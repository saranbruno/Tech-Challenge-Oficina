set -Eeuo pipefail

namespace="${HPA_NAMESPACE:-oficina}"
hpa_name="${HPA_NAME:-oficina-api}"
deployment_name="${HPA_DEPLOYMENT:-oficina-api}"
sample_seconds="${HPA_SAMPLE_SECONDS:-15}"
sample_count="${HPA_SAMPLE_COUNT:-20}"
cooldown_count="${HPA_COOLDOWN_COUNT:-20}"
output_dir="${HPA_OUTPUT_DIR:-hpa-evidence-$(date -u +%Y%m%dT%H%M%SZ)}"

[[ "$sample_seconds" =~ ^[1-9][0-9]*$ ]]
[[ "$sample_count" =~ ^[1-9][0-9]*$ ]]
[[ "$cooldown_count" =~ ^[1-9][0-9]*$ ]]

mkdir -p "$output_dir"
kubectl -n "$namespace" get hpa "$hpa_name" -o yaml > "$output_dir/hpa-before.yaml"
kubectl -n "$namespace" get deployment "$deployment_name" -o wide > "$output_dir/deployment-before.txt"
kubectl -n "$namespace" get pods -o wide > "$output_dir/pods-before.txt"
kubectl top pods -n "$namespace" --containers > "$output_dir/metrics-before.txt"

sample() {
    local phase="$1"
    local index="$2"
    local stamp="$(date -u +%Y%m%dT%H%M%SZ)"
    kubectl -n "$namespace" get hpa "$hpa_name" -o wide > "$output_dir/${phase}-${index}-${stamp}-hpa.txt"
    kubectl -n "$namespace" get deployment "$deployment_name" -o wide > "$output_dir/${phase}-${index}-${stamp}-deployment.txt"
    kubectl -n "$namespace" get pods -o wide > "$output_dir/${phase}-${index}-${stamp}-pods.txt"
    kubectl top pods -n "$namespace" --containers > "$output_dir/${phase}-${index}-${stamp}-metrics.txt" || true
}

load_pid=""
if [[ -n "${LOAD_COMMAND:-}" ]]; then
    bash -c "$LOAD_COMMAND" > "$output_dir/load.log" 2>&1 &
    load_pid="$!"
fi

for index in $(seq 1 "$sample_count"); do
    sample load "$index"
    sleep "$sample_seconds"
done

if [[ -n "$load_pid" ]]; then
    wait "$load_pid" || true
fi

for index in $(seq 1 "$cooldown_count"); do
    sample cooldown "$index"
    sleep "$sample_seconds"
done

kubectl -n "$namespace" get events --sort-by=.lastTimestamp > "$output_dir/events.txt"
kubectl -n "$namespace" describe hpa "$hpa_name" > "$output_dir/hpa-describe.txt"
kubectl -n "$namespace" get deployment "$deployment_name" -o yaml > "$output_dir/deployment-after.yaml"
kubectl -n "$namespace" get pods -o wide > "$output_dir/pods-after.txt"
kubectl top pods -n "$namespace" --containers > "$output_dir/metrics-after.txt" || true
printf 'evidence_dir=%s\n' "$output_dir"
