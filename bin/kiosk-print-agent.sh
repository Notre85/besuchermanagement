#!/usr/bin/env bash
set -euo pipefail

: "${PRINT_AGENT_URL:?PRINT_AGENT_URL fehlt}"
: "${PRINT_AGENT_TOKEN:?PRINT_AGENT_TOKEN fehlt}"
: "${PRINT_AGENT_DEVICE_ID:?PRINT_AGENT_DEVICE_ID fehlt}"
: "${CUPS_DESTINATION:?CUPS_DESTINATION fehlt}"

while true; do
    response=$(curl --fail --silent --show-error --connect-timeout 3 --max-time 20 \
        -H "X-Print-Agent-Token: ${PRINT_AGENT_TOKEN}" \
        "${PRINT_AGENT_URL%/}/print_agent.php?action=next&device_id=${PRINT_AGENT_DEVICE_ID}") || response='{"job":null}'
    job_id=$(printf '%s' "$response" | jq -r '.job.id // empty')
    if [[ -n "$job_id" ]]; then
        tmp_file=$(mktemp)
        if printf '%s' "$response" | jq -r '.job.content_base64' | base64 --decode > "$tmp_file" && lp -d "$CUPS_DESTINATION" "$tmp_file" >/dev/null 2>&1; then
            status=printed
            error=''
        else
            status=failed
            error='CUPS-Druck fehlgeschlagen'
        fi
        curl --fail --silent --show-error --max-time 10 -X POST \
            -H "X-Print-Agent-Token: ${PRINT_AGENT_TOKEN}" -H 'Content-Type: application/json' \
            --data "{\"job_id\":${job_id},\"status\":\"${status}\",\"error\":\"${error}\"}" \
            "${PRINT_AGENT_URL%/}/print_agent.php?action=status&device_id=${PRINT_AGENT_DEVICE_ID}" >/dev/null || true
        rm -f "$tmp_file"
    else
        sleep 2
    fi
done
