output "namespace" {
  value = kubernetes_namespace_v1.oficina.metadata[0].name
}

output "secret_name" {
  value = kubernetes_secret_v1.postgres.metadata[0].name
}

output "service_name" {
  value = kubernetes_service_v1.postgres.metadata[0].name
}

output "stateful_set_name" {
  value = kubernetes_stateful_set_v1.postgres.metadata[0].name
}

output "pvc_name" {
  value = "data-${kubernetes_stateful_set_v1.postgres.metadata[0].name}-0"
}
