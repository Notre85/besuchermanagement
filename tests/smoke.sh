#!/usr/bin/env bash
set -euo pipefail

project_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$project_dir"

while IFS= read -r -d '' file; do
    php -l "$file" >/dev/null
done < <(find . -path './vendor' -prune -o -name '*.php' -print0)

bash -n setup.sh
composer validate --no-check-publish >/dev/null
composer audit --format=plain >/dev/null
bash tests/http_security.sh

if rg -n 'system\(|shell_exec|passthru|method="GET"[^>]*action=.*(check|delete|update)' --glob '!vendor/**' --glob '!logs/**' --glob '!tests/**' .; then
    echo 'Unsichere Shell-Ausführung oder GET-Schreibaktion gefunden.' >&2
    exit 1
fi

echo 'Smoke-Test erfolgreich.'
