#!/usr/bin/env python3
"""Run endpoint-probe.php against every plugin-owned AJAX action, REST route and ability.

Dev/CI only (not shipped). Usage:

  python3 .github/security/probe.py --wp "wp --path=/path/to/site" \
      --script /path/to/plugin/.github/security/endpoint-probe.php

  # wp-env (CI): the script path is the one *inside* the container
  python3 .github/security/probe.py --wp "npx wp-env run cli wp" \
      --script wp-content/plugins/<slug>/.github/security/endpoint-probe.php

Exits 1 if any endpoint answers a subscriber/anonymous request successfully
or attempts a database write. Writes are blocked by the probe, so the site is
never modified.
"""
import argparse
import json
import shlex
import subprocess
import sys

NONCE_OVERRIDE = "function wp_verify_nonce( $n = '', $a = -1 ) { return 1; }"


def wp(base, args, check=True):
    cmd = shlex.split(base) + args
    proc = subprocess.run(cmd, capture_output=True, text=True)
    if check and proc.returncode != 0:
        sys.exit(f"command failed: {' '.join(cmd)}\n{proc.stdout}\n{proc.stderr}")
    return proc.stdout


def last_json(text, marker=None):
    for line in reversed(text.splitlines()):
        line = line.strip()
        if marker:
            if line.startswith(marker):
                return json.loads(line[len(marker):].strip())
        elif line.startswith("[") or line.startswith("{"):
            return json.loads(line)
    return None


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--wp", default="wp", help="WP-CLI command prefix")
    ap.add_argument("--script", required=True, help="endpoint-probe.php path as WP-CLI sees it")
    ap.add_argument("--only", default="", help="only probe targets containing this text")
    opts = ap.parse_args()

    admin = wp(opts.wp, ["user", "list", "--role=administrator", "--field=ID", "--number=1"]).split()[-1]
    sub = wp(opts.wp, ["user", "get", "nhrprobe", "--field=ID"], check=False).strip().split()
    if not sub or not sub[-1].isdigit():
        wp(opts.wp, ["user", "create", "nhrprobe", "nhrprobe@example.invalid", "--role=subscriber", "--porcelain"])
        sub = wp(opts.wp, ["user", "get", "nhrprobe", "--field=ID"]).strip().split()
    sub_id = sub[-1]

    # Plugins commonly register AJAX handlers only when DOING_AJAX is set.
    targets = last_json(wp(opts.wp, [f"--user={admin}", "--exec=define( 'DOING_AJAX', true );",
                                     "eval-file", opts.script, "list"])) or []
    if opts.only:
        targets = [t for t in targets if opts.only in t["target"]]
    count = {kind: sum(1 for t in targets if t["type"] == kind) for kind in ("ajax", "rest", "ability")}
    print(f"Probing {count['ajax']} AJAX actions, {count['rest']} REST endpoints and {count['ability']} abilities "
          "as subscriber/anonymous (nonces forced valid)")

    failures = 0
    runs = 0
    for t in targets:
        roles = ["subscriber"]
        if t["type"] == "rest" or t["nopriv"]:
            roles.append("anonymous")
        for role in roles:
            exec_code = NONCE_OVERRIDE + (" define( 'DOING_AJAX', true );" if t["type"] == "ajax" else "")
            out = wp(opts.wp, [f"--user={admin}", f"--exec={exec_code}", "eval-file", opts.script,
                               "run", t["type"], t["target"], t["method"], role, sub_id], check=False)
            res = last_json(out, "NHRPROBE_RESULT")
            runs += 1
            if res is None:
                failures += 1
                print(f"ERROR  {t['type']:7} {t['method']:6} {t['target']} [{role}] probe crashed:\n{out[-400:]}")
                continue
            if res["fail"]:
                failures += 1
                why = "responded successfully" if res["leak"] else "attempted DB writes"
                print(f"FAIL   {t['type']:7} {t['method']:6} {t['target']} [{role}] {why}: {res['output'][:120]}")
                for w in res["writes"][:3]:
                    print(f"         write: {w}")
            else:
                print(f"ok     {t['type']:7} {t['method']:6} {t['target']} [{role}]")

    wp(opts.wp, ["user", "delete", sub_id, "--yes"], check=False)
    print(f"\n{runs} checks, {failures} failure(s)")
    sys.exit(1 if failures or not targets else 0)


if __name__ == "__main__":
    main()
