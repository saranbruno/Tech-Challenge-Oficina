terraform {
  required_version = ">= 1.5.0"

  required_providers {
    kubernetes = {
      source  = "hashicorp/kubernetes"
      version = "~> 2.38"
    }
  }
}

provider "kubernetes" {
  config_path = pathexpand(var.kubeconfig_path)
}

resource "terraform_data" "kind_cluster" {
  triggers_replace = {
    cluster_name = var.cluster_name
    kind_binary  = var.kind_binary
    node_image   = var.node_image
  }

  provisioner "local-exec" {
    command = "${var.kind_binary} create cluster --name ${var.cluster_name} --image ${var.node_image} --wait ${var.wait_timeout}"
  }

  provisioner "local-exec" {
    when    = destroy
    command = "${self.triggers_replace.kind_binary} delete cluster --name ${self.triggers_replace.cluster_name}"
  }
}
