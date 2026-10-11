variable "cluster_name" {
  type        = string
  description = "Nome do cluster Kind."
}

variable "api_server_address" {
  type        = string
  description = "Endereco local usado pelo servidor da API Kubernetes."
  default     = "127.0.0.1"
}

variable "api_server_port" {
  type        = number
  description = "Porta local usada pelo servidor da API Kubernetes; zero solicita uma porta aleatoria."
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
