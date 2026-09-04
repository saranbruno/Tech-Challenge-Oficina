variable "cluster_name" {
  type    = string
  default = "tech-challenge-terraform-ci"
}

variable "node_image" {
  type    = string
  default = "kindest/node:v1.35.0"
}

variable "api_server_port" {
  type    = number
  default = 0
}

variable "kind_binary" {
  type    = string
  default = "kind"
}

variable "kubeconfig_path" {
  type    = string
  default = "~/.kube/tech-challenge-terraform-ci.config"
}

variable "wait_timeout" {
  type    = string
  default = "120s"
}
