variable "database_name" {
  type        = string
  description = "Nome do banco PostgreSQL."
}

variable "image" {
  type        = string
  description = "Imagem do PostgreSQL."
  default     = "postgres:18.4-alpine"
}

variable "kubeconfig_path" {
  type        = string
  description = "Caminho do kubeconfig do cluster."
}

variable "labels" {
  type        = map(string)
  description = "Labels aplicados ao Service e StatefulSet."
  default = {
    "app.kubernetes.io/name"      = "postgres"
    "app.kubernetes.io/component" = "database"
  }
}

variable "namespace" {
  type        = string
  description = "Namespace do PostgreSQL."
  default     = "oficina"
}

variable "password" {
  type        = string
  description = "Senha do usuario PostgreSQL."
  sensitive   = true
}

variable "secret_name" {
  type        = string
  description = "Nome do Secret com as credenciais PostgreSQL."
  default     = "postgres-secrets"
}

variable "service_name" {
  type        = string
  description = "Nome do Service e StatefulSet PostgreSQL."
  default     = "postgres"
}

variable "storage_class_name" {
  type        = string
  description = "StorageClass do volume persistente."
  default     = "standard"
}

variable "storage_size" {
  type        = string
  description = "Tamanho solicitado para o PVC."
  default     = "1Gi"
}

variable "username" {
  type        = string
  description = "Usuario PostgreSQL."
}
