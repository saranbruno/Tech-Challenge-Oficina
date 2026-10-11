variable "kubelet_insecure_tls" {
  type        = bool
  default     = false
  description = "Aceitar certificados autoassinados do kubelet somente nos clusters Kind."
}
