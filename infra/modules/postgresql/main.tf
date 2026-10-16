terraform {
  required_version = ">= 1.5.0"

  required_providers {
    kubernetes = {
      source  = "hashicorp/kubernetes"
      version = "~> 2.38"
    }
  }
}

resource "kubernetes_namespace_v1" "oficina" {
  metadata {
    name = var.namespace
    labels = {
      "app.kubernetes.io/name"    = "tech-challenge-oficina"
      "app.kubernetes.io/part-of" = "tech-challenge-oficina"
    }
  }
}

resource "kubernetes_secret_v1" "postgres" {
  metadata {
    name      = var.secret_name
    namespace = kubernetes_namespace_v1.oficina.metadata[0].name
  }

  data = {
    POSTGRES_DB       = var.database_name
    POSTGRES_PASSWORD = var.password
    POSTGRES_USER     = var.username
  }

  type = "Opaque"
}

resource "kubernetes_service_v1" "postgres" {
  metadata {
    name      = var.service_name
    namespace = kubernetes_namespace_v1.oficina.metadata[0].name
    labels    = var.labels
  }

  spec {
    cluster_ip = "None"

    selector = var.labels

    port {
      name        = "postgresql"
      port        = 5432
      target_port = 5432
      protocol    = "TCP"
    }
  }
}

resource "kubernetes_stateful_set_v1" "postgres" {
  metadata {
    name      = var.service_name
    namespace = kubernetes_namespace_v1.oficina.metadata[0].name
    labels    = var.labels
  }

  spec {
    service_name = kubernetes_service_v1.postgres.metadata[0].name
    replicas     = 1

    selector {
      match_labels = var.labels
    }

    template {
      metadata {
        labels = var.labels
      }

      spec {
        container {
          name              = "postgres"
          image             = var.image
          image_pull_policy = "IfNotPresent"

          port {
            name           = "postgresql"
            container_port = 5432
            protocol       = "TCP"
          }

          env_from {
            secret_ref {
              name = kubernetes_secret_v1.postgres.metadata[0].name
            }
          }

          startup_probe {
            exec {
              command = ["sh", "-c", "pg_isready -U \"$POSTGRES_USER\" -d \"$POSTGRES_DB\""]
            }
            period_seconds    = 5
            timeout_seconds   = 3
            failure_threshold = 24
          }

          readiness_probe {
            exec {
              command = ["sh", "-c", "pg_isready -U \"$POSTGRES_USER\" -d \"$POSTGRES_DB\""]
            }
            period_seconds    = 5
            timeout_seconds   = 3
            failure_threshold = 3
          }

          liveness_probe {
            exec {
              command = ["sh", "-c", "pg_isready -U \"$POSTGRES_USER\" -d \"$POSTGRES_DB\""]
            }
            period_seconds    = 10
            timeout_seconds   = 3
            failure_threshold = 3
          }

          resources {
            requests = {
              cpu    = "100m"
              memory = "256Mi"
            }
            limits = {
              cpu    = "500m"
              memory = "1Gi"
            }
          }

          volume_mount {
            name       = "data"
            mount_path = "/var/lib/postgresql"
          }
        }
      }
    }

    volume_claim_template {
      metadata {
        name = "data"
      }

      spec {
        access_modes       = ["ReadWriteOnce"]
        storage_class_name = var.storage_class_name

        resources {
          requests = {
            storage = var.storage_size
          }
        }
      }
    }
  }
}
