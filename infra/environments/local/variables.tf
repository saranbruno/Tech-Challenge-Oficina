variable "cluster_name" {
  type    = string
  default = "tech-challenge-terraform-local"
}

variable "node_image" {
  type    = string
  default = "kindest/node:v1.37.0"
}

variable "kind_binary" {
  type    = string
  default = "/tmp/kind"
}

variable "kubeconfig_path" {
  type    = string
  default = "~/.kube/config"
}

variable "wait_timeout" {
  type    = string
  default = "120s"
}
