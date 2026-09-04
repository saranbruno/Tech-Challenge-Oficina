output "cluster_name" {
  value       = var.cluster_name
  description = "Nome do cluster Kind provisionado."
}

output "kubectl_context" {
  value       = "kind-${var.cluster_name}"
  description = "Contexto kubectl do cluster provisionado."
}

output "kubeconfig_path" {
  value       = pathexpand(var.kubeconfig_path)
  description = "Caminho do kubeconfig usado pelo cluster."
}
