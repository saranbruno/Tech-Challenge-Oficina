import argparse
import contextlib
import importlib.util
import io
import json
from pathlib import Path
import tempfile
import unittest
from unittest.mock import patch


spec = importlib.util.spec_from_file_location(
    "k8s_local", Path(__file__).resolve().parents[2] / "scripts/k8s-local.py"
)
deploy = importlib.util.module_from_spec(spec)
spec.loader.exec_module(deploy)


class LocalDeploySafetyTest(unittest.TestCase):
    def invoke(self, temporary, action="destroy", confirm=None, convert=False):
        with tempfile.TemporaryDirectory() as directory:
            state = Path(directory)
            (state / "settings.json").write_text(json.dumps({"temporary": temporary}))
            args = argparse.Namespace(action=action, temporary=convert,
                                      confirm_destroy=confirm)
            with patch.object(deploy.shutil, "which", return_value="/bin/tool"), \
                    patch.object(deploy, "run") as command, \
                    contextlib.redirect_stderr(io.StringIO()), \
                    contextlib.redirect_stdout(io.StringIO()):
                try:
                    deploy.execute(args, argparse.ArgumentParser(), "oficina-local-test", state)
                except SystemExit as error:
                    return error.code, command.call_args_list
                return 0, command.call_args_list

    def test_persistent_destroy_requires_exact_cluster_confirmation(self):
        for confirm in [None, "test", "oficina-local-other"]:
            code, calls = self.invoke(False, confirm=confirm)
            self.assertEqual(2, code)
            self.assertEqual([], calls)

    def test_persistent_environment_cannot_be_converted_to_temporary(self):
        code, calls = self.invoke(False, convert=True)
        self.assertEqual(2, code)
        self.assertEqual([], calls)

    def test_temporary_destroy_uses_only_its_own_terraform_directory(self):
        code, calls = self.invoke(True)
        self.assertEqual(0, code)
        self.assertEqual(1, len(calls))
        self.assertIn("/infra/environments/local", calls[0].args[1])
        self.assertEqual("destroy", calls[0].args[2])

    def test_command_failure_is_not_ignored(self):
        with self.assertRaises(deploy.subprocess.CalledProcessError):
            deploy.run("python3", "-c", "raise SystemExit(7)")

    def test_top_level_kind_ignores_a_nested_scale_target_kind(self):
        manifest = """apiVersion: autoscaling/v2
kind: HorizontalPodAutoscaler
spec:
  scaleTargetRef:
    kind: Deployment
"""
        self.assertEqual("HorizontalPodAutoscaler", deploy.top_level_kind(manifest))

    def test_other_running_control_planes_excludes_the_target(self):
        with patch.object(deploy, "run", return_value="oficina-local-test-control-plane\nother-control-plane\n"):
            self.assertEqual(["other-control-plane"], deploy.other_running_control_planes("oficina-local-test"))

    def test_running_control_plane_stops_up_before_creating_state(self):
        with tempfile.TemporaryDirectory() as directory:
            state = Path(directory) / "new"
            args = argparse.Namespace(action="up", temporary=True, confirm_destroy=None)
            with patch.object(deploy.shutil, "which", return_value="/bin/tool"), \
                    patch.object(deploy, "other_running_control_planes", return_value=["other-control-plane"]), \
                    contextlib.redirect_stderr(io.StringIO()), \
                    contextlib.redirect_stdout(io.StringIO()):
                with self.assertRaises(SystemExit) as error:
                    deploy.execute(args, argparse.ArgumentParser(), "oficina-local-test", state)
            self.assertEqual(2, error.exception.code)
            self.assertFalse(state.exists())


if __name__ == "__main__":
    unittest.main()
