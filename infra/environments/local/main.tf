module "kind_cluster" {
  source = "../../modules/kind-cluster"

  api_server_port = var.api_server_port
  cluster_name    = var.cluster_name
  node_image      = var.node_image
  kind_binary     = var.kind_binary
  kubeconfig_path = var.kubeconfig_path
  wait_timeout    = var.wait_timeout
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
