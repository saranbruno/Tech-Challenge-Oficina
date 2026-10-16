set -Eeuo pipefail

target_url="${LOAD_TEST_URL:-http://127.0.0.1:8081/up}"
duration_seconds="${LOAD_TEST_DURATION_SECONDS:-60}"
concurrency="${LOAD_TEST_CONCURRENCY:-8}"
request_timeout="${LOAD_TEST_REQUEST_TIMEOUT_SECONDS:-10}"
output_file="${LOAD_TEST_OUTPUT:-load-test-$(date -u +%Y%m%dT%H%M%SZ).csv}"

[[ "$duration_seconds" =~ ^[1-9][0-9]*$ ]]
[[ "$concurrency" =~ ^[1-9][0-9]*$ ]]
[[ "$request_timeout" =~ ^[1-9][0-9]*$ ]]

output_dir="$(dirname "$output_file")"
mkdir -p "$output_dir"
printf 'timestamp,worker,status,elapsed_ms\n' > "$output_file"

deadline=$((SECONDS + duration_seconds))
worker() {
    local worker_id="$1"
    local status elapsed start timestamp
    while (( SECONDS < deadline )); do
        start="$(date +%s%N)"
        status="$(curl --silent --show-error --output /dev/null --write-out '%{http_code}' --max-time "$request_timeout" "$target_url" 2>/dev/null || printf '000')"
        elapsed=$(( ($(date +%s%N) - start) / 1000000 ))
        timestamp="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
        printf '%s,%s,%s,%s\n' "$timestamp" "$worker_id" "$status" "$elapsed" >> "$output_file"
    done
}

pids=()
for worker_id in $(seq 1 "$concurrency"); do
    worker "$worker_id" &
    pids+=("$!")
done

exit_code=0
for pid in "${pids[@]}"; do
    wait "$pid" || exit_code=1
done

summary="$(awk -F, 'NR > 1 { total++; if ($3 ~ /^2[0-9][0-9]$/) success++; else failed++ } END { printf "requests=%d successes=%d failures=%d error_rate=%.4f", total, success, failed, (total ? failed / total : 1) }' "$output_file")"
printf '%s\n' "$summary"
printf 'target=%s duration_seconds=%s concurrency=%s output=%s\n' "$target_url" "$duration_seconds" "$concurrency" "$output_file"

(( exit_code == 0 ))
