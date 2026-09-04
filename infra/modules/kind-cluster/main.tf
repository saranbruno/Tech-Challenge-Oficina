terraform {
  required_version = ">= 1.5.0"

  required_providers {
    kubernetes = {
      source  = "hashicorp/kubernetes"
      version = "~> 2.38"
    }
  }
}

resource "terraform_data" "kind_cluster" {
  triggers_replace = {
    api_server_address = var.api_server_address
    api_server_port    = var.api_server_port
    cluster_name       = var.cluster_name
    kind_binary        = var.kind_binary
    node_image         = var.node_image
  }

  provisioner "local-exec" {
    interpreter = ["/bin/bash", "-c"]
    command     = <<-EOT
      set -e
      kind_config="$(mktemp)"
      trap 'rm -f "$kind_config"' EXIT
      cat >"$kind_config" <<EOF
      apiVersion: kind.x-k8s.io/v1alpha4
      kind: Cluster
      networking:
        apiServerAddress: ${var.api_server_address}
        apiServerPort: ${var.api_server_port}
      EOF
      "${var.kind_binary}" create cluster --name "${var.cluster_name}" --image "${var.node_image}" --config "$kind_config" --kubeconfig "${pathexpand(var.kubeconfig_path)}" --wait "${var.wait_timeout}"
    EOT
  }

  provisioner "local-exec" {
    when    = destroy
    command = "${self.triggers_replace.kind_binary} delete cluster --name ${self.triggers_replace.cluster_name}"
  }
}
