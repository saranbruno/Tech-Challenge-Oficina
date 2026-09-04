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

module "kind_cluster" {
  source = "../../modules/kind-cluster"

  api_server_port = var.api_server_port
  cluster_name    = var.cluster_name
  node_image      = var.node_image
  kind_binary     = var.kind_binary
  kubeconfig_path = var.kubeconfig_path
  wait_timeout    = var.wait_timeout
}

module "postgresql" {
  source = "../../modules/postgresql"

  database_name   = var.database_name
  kubeconfig_path = var.kubeconfig_path
  password        = var.database_password
  username        = var.database_username

  depends_on = [module.kind_cluster]
}

output "cluster_name" {
  value = module.kind_cluster.cluster_name
}

output "kubectl_context" {
  value = module.kind_cluster.kubectl_context
}

output "kubeconfig_path" {
  value = module.kind_cluster.kubeconfig_path
}

output "postgres_service_name" {
  value = module.postgresql.service_name
}

output "postgres_namespace" {
  value = module.postgresql.namespace
}

output "postgres_pvc_name" {
  value = module.postgresql.pvc_name
}
