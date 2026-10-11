variable "cluster_name" {
  type    = string
  default = "tech-challenge-terraform-local"
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
  default = "/tmp/kind"
}

variable "kubeconfig_path" {
  type    = string
  default = "~/.kube/tech-challenge-terraform-local.config"
}

variable "wait_timeout" {
  type    = string
  default = "120s"
}

variable "database_name" {
  type    = string
  default = "tech_challenge_oficina"
}

variable "database_username" {
  type    = string
  default = "oficina"
}

variable "database_password" {
  type      = string
  default   = "local-only-change-me"
  sensitive = true
}
