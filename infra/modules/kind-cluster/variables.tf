variable "cluster_name" {
  type        = string
  description = "Nome do cluster Kind."
}

variable "node_image" {
  type        = string
  description = "Imagem kindest/node usada pelo cluster."
}

variable "kind_binary" {
  type        = string
  description = "Caminho ou nome do binario kind."
  default     = "kind"
}

variable "kubeconfig_path" {
  type        = string
  description = "Caminho do kubeconfig usado pelo provider Kubernetes."
  default     = "~/.kube/config"
}

variable "wait_timeout" {
  type        = string
  description = "Tempo maximo aguardado pelo Kind para o control-plane."
  default     = "120s"
}
