terraform {
  required_version = ">= 1.5.0"

  required_providers {
    kubernetes = {
      source  = "hashicorp/kubernetes"
      version = "~> 2.38"
    }
  }
}

locals {
  name      = "metrics-server"
  namespace = "kube-system"
  labels    = { "k8s-app" = local.name }
}

resource "kubernetes_service_account_v1" "metrics_server" {
  metadata {
    name      = local.name
    namespace = local.namespace
    labels    = local.labels
  }
}

resource "kubernetes_cluster_role_v1" "reader" {
  metadata {
    name = "system:aggregated-metrics-reader"
    labels = merge(local.labels, {
      "rbac.authorization.k8s.io/aggregate-to-admin" = "true"
      "rbac.authorization.k8s.io/aggregate-to-edit"  = "true"
      "rbac.authorization.k8s.io/aggregate-to-view"  = "true"
    })
  }
  rule {
    api_groups = ["metrics.k8s.io"]
    resources  = ["pods", "nodes"]
    verbs      = ["get", "list", "watch"]
  }
}

resource "kubernetes_cluster_role_v1" "metrics_server" {
  metadata {
    name   = "system:metrics-server"
    labels = local.labels
  }
  rule {
    api_groups = [""]
    resources  = ["nodes/metrics"]
    verbs      = ["get"]
  }
  rule {
    api_groups = [""]
    resources  = ["pods", "nodes"]
    verbs      = ["get", "list", "watch"]
  }
}

resource "kubernetes_role_binding_v1" "auth_reader" {
  metadata {
    name      = "metrics-server-auth-reader"
    namespace = local.namespace
    labels    = local.labels
  }
  role_ref {
    api_group = "rbac.authorization.k8s.io"
    kind      = "Role"
    name      = "extension-apiserver-authentication-reader"
  }
  subject {
    kind      = "ServiceAccount"
    name      = kubernetes_service_account_v1.metrics_server.metadata[0].name
    namespace = local.namespace
  }
}

resource "kubernetes_cluster_role_binding_v1" "auth_delegator" {
  metadata {
    name   = "metrics-server:system:auth-delegator"
    labels = local.labels
  }
  role_ref {
    api_group = "rbac.authorization.k8s.io"
    kind      = "ClusterRole"
    name      = "system:auth-delegator"
  }
  subject {
    kind      = "ServiceAccount"
    name      = kubernetes_service_account_v1.metrics_server.metadata[0].name
    namespace = local.namespace
  }
}

resource "kubernetes_cluster_role_binding_v1" "metrics_server" {
  metadata {
    name   = "system:metrics-server"
    labels = local.labels
  }
  role_ref {
    api_group = "rbac.authorization.k8s.io"
    kind      = "ClusterRole"
    name      = kubernetes_cluster_role_v1.metrics_server.metadata[0].name
  }
  subject {
    kind      = "ServiceAccount"
    name      = kubernetes_service_account_v1.metrics_server.metadata[0].name
    namespace = local.namespace
  }
}

resource "kubernetes_deployment_v1" "metrics_server" {
  metadata {
    name      = local.name
    namespace = local.namespace
    labels    = local.labels
  }
  spec {
    replicas = 1
    selector {
      match_labels = local.labels
    }
    strategy {
      rolling_update {
        max_unavailable = "0"
      }
    }
    template {
      metadata {
        labels = local.labels
      }
      spec {
        service_account_name = kubernetes_service_account_v1.metrics_server.metadata[0].name
        priority_class_name  = "system-cluster-critical"
        node_selector        = { "kubernetes.io/os" = "linux" }

        container {
          name              = local.name
          image             = "registry.k8s.io/metrics-server/metrics-server:v0.9.0"
          image_pull_policy = "IfNotPresent"
          args = concat([
            "--cert-dir=/tmp",
            "--secure-port=10250",
            "--kubelet-preferred-address-types=InternalIP,ExternalIP,Hostname",
            "--kubelet-use-node-status-port",
            "--metric-resolution=15s"
          ], var.kubelet_insecure_tls ? ["--kubelet-insecure-tls"] : [])

          port {
            name           = "https"
            container_port = 10250
          }
          liveness_probe {
            http_get {
              path   = "/livez"
              port   = "https"
              scheme = "HTTPS"
            }
            period_seconds    = 10
            failure_threshold = 3
          }
          readiness_probe {
            http_get {
              path   = "/readyz"
              port   = "https"
              scheme = "HTTPS"
            }
            initial_delay_seconds = 20
            period_seconds        = 10
            failure_threshold     = 3
          }
          resources {
            requests = { cpu = "100m", memory = "200Mi" }
            limits   = { cpu = "500m", memory = "512Mi" }
          }
          security_context {
            allow_privilege_escalation = false
            read_only_root_filesystem  = true
            run_as_non_root            = true
            run_as_user                = 1000
            capabilities {
              drop = ["ALL"]
            }
            seccomp_profile {
              type = "RuntimeDefault"
            }
          }
          volume_mount {
            mount_path = "/tmp"
            name       = "tmp-dir"
          }
        }
        volume {
          name = "tmp-dir"
          empty_dir {}
        }
      }
    }
  }
  timeouts {
    create = "5m"
    update = "5m"
  }
  depends_on = [
    kubernetes_role_binding_v1.auth_reader,
    kubernetes_cluster_role_binding_v1.auth_delegator,
    kubernetes_cluster_role_binding_v1.metrics_server
  ]
}

resource "kubernetes_service_v1" "metrics_server" {
  metadata {
    name      = local.name
    namespace = local.namespace
    labels    = local.labels
  }
  spec {
    selector = local.labels
    port {
      name         = "https"
      app_protocol = "https"
      port         = 443
      target_port  = "https"
    }
  }
}

resource "kubernetes_api_service_v1" "metrics" {
  metadata {
    name   = "v1beta1.metrics.k8s.io"
    labels = local.labels
  }
  spec {
    group                    = "metrics.k8s.io"
    group_priority_minimum   = 100
    version                  = "v1beta1"
    version_priority         = 100
    insecure_skip_tls_verify = true
    service {
      name      = kubernetes_service_v1.metrics_server.metadata[0].name
      namespace = local.namespace
    }
  }
}
