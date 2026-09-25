#!/usr/bin/env bash
set -euo pipefail

project_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
port=18123
log_file="$(mktemp)"
server_pid=""

cleanup() {
    if [[ -n "$server_pid" ]]; then
        kill "$server_pid" 2>/dev/null || true
        wait "$server_pid" 2>/dev/null || true
    fi
}
trap cleanup EXIT

php -S "127.0.0.1:${port}" -t "$project_dir" "$project_dir/router.php" >"$log_file" 2>&1 &
server_pid=$!
server_ready=0

for _ in {1..30}; do
    if curl -fsS "http://127.0.0.1:${port}/kiosk.php" >/dev/null 2>&1; then
        server_ready=1
        break
    fi
    sleep 0.1
done
test "$server_ready" = "1"

status_code() {
    curl -sS -o /dev/null -w '%{http_code}' "$1"
}

test "$(status_code "http://127.0.0.1:${port}/kiosk.php")" = "200"
test "$(status_code "http://127.0.0.1:${port}/index.php")" = "200"
test "$(status_code "http://127.0.0.1:${port}/visitor_management.php")" = "302"
test "$(status_code "http://127.0.0.1:${port}/backup.php")" = "405"
test "$(status_code "http://127.0.0.1:${port}/setup.sh")" = "404"
test "$(status_code "http://127.0.0.1:${port}/.well-known/security.txt")" = "200"
test "$(status_code "http://127.0.0.1:${port}/assets/bootstrap/bootstrap.min.css")" = "200"

headers="$(curl -sS -D - -o /dev/null "http://127.0.0.1:${port}/index.php")"
grep -qi '^cache-control: no-store' <<<"$headers"
grep -qi '^x-content-type-options: nosniff' <<<"$headers"
test "$(curl -sS -o /dev/null -w '%{http_code}' -X POST -d 'action=checkin' "http://127.0.0.1:${port}/kiosk.php")" = "403"

echo 'HTTP-Sicherheitstests erfolgreich.'
