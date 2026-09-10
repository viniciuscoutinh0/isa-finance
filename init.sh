#!/bin/bash
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
COMPOSE_FILE="$PROJECT_DIR/docker-compose.yaml"
NGINX_FILE="$PROJECT_DIR/.docker/nginx/default.conf"
MCP_FILE="$PROJECT_DIR/.mcp.json"
RULES_DIR="$PROJECT_DIR/.ai/rules"

OLD_APP_SERVICE=""
if [ -f "$COMPOSE_FILE" ]; then
    OLD_APP_SERVICE="$(grep -m1 -oE 'container_name: +[a-z0-9_]+_app' "$COMPOSE_FILE" | awk '{print $2}' || true)"
fi

NAME="${1:-}"

if [ -z "$NAME" ]; then
    read -rp "Nome do projeto (prefixo dos servicos): " NAME
fi

NAME="$(echo "$NAME" | tr '[:upper:] -' '[:lower:]__' | tr -cd 'a-z0-9_')"

if [ -z "$NAME" ]; then
    echo "Erro: nome invalido. Use letras, numeros, - ou _." >&2
    exit 1
fi

APP_SERVICE="${NAME}_app"
WEB_SERVICE="${NAME}_web"

mkdir -p "$(dirname "$NGINX_FILE")"

cat > "$COMPOSE_FILE" <<EOF
services:
  ${APP_SERVICE}:
    container_name: ${APP_SERVICE}
    build:
      context: "./.docker"
      dockerfile: Dockerfile
    ports:
      - "5173:5173"
    volumes:
      - "./.:/var/www/html"

  ${WEB_SERVICE}:
    container_name: ${WEB_SERVICE}
    image: nginx:1.31-alpine
    ports:
      - "80:80"
    volumes:
      - "./.docker/nginx/default.conf:/etc/nginx/conf.d/default.conf"
      - "./.:/var/www/html"
    depends_on:
      - "${APP_SERVICE}"
EOF

cat > "$NGINX_FILE" <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name localhost;
    root /var/www/html/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php\$ {
        fastcgi_pass ${APP_SERVICE}:9000;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

if [ -f "$MCP_FILE" ]; then
    TMP_MCP="$(mktemp)"
    awk -v svc="$APP_SERVICE" '
        prev_is_flag && $0 ~ /^[[:space:]]*"[^"]*",?$/ {
            match($0, /^[[:space:]]*/)
            indent = substr($0, 1, RLENGTH)
            comma = ($0 ~ /,$/) ? "," : ""
            print indent "\"" svc "\"" comma
            prev_is_flag = 0
            replaced = 1
            next
        }
        { prev_is_flag = ($0 ~ /^[[:space:]]*"-T",[[:space:]]*$/) ? 1 : 0; print }
        END { exit replaced ? 0 : 1 }
    ' "$MCP_FILE" > "$TMP_MCP" && MCP_REPLACED=1 || MCP_REPLACED=0

    if [ "$MCP_REPLACED" = "1" ]; then
        mv "$TMP_MCP" "$MCP_FILE"
        echo "  .mcp.json: container atualizado"
    else
        rm -f "$TMP_MCP"
        echo "Aviso: .mcp.json existe mas nao tem argumento apos \"-T\"; ajuste manual." >&2
    fi
else
    cat > "$MCP_FILE" <<EOF
{
  "mcpServers": {
    "laravel-boost": {
      "type": "stdio",
      "command": "docker",
      "args": [
        "compose",
        "exec",
        "-T",
        "${APP_SERVICE}",
        "php",
        "artisan",
        "boost:mcp"
      ]
    }
  }
}
EOF
    echo "  .mcp.json: criado"
fi

if [ -d "$RULES_DIR" ]; then
    if [ -n "$OLD_APP_SERVICE" ] && [ "$OLD_APP_SERVICE" != "$APP_SERVICE" ]; then
        RULES_TOUCHED=0

        while IFS= read -r RULE_FILE; do
            sed -i "s/\b${OLD_APP_SERVICE}\b/${APP_SERVICE}/g" "$RULE_FILE"
            RULES_TOUCHED=$((RULES_TOUCHED + 1))
        done < <(grep -rlF "$OLD_APP_SERVICE" "$RULES_DIR" || true)

        echo "  .ai/rules: ${RULES_TOUCHED} arquivo(s) atualizados (${OLD_APP_SERVICE} -> ${APP_SERVICE})"
    fi

    if grep -rqE '\b[a-z0-9_]+_app\b' "$RULES_DIR" && ! grep -rqF "$APP_SERVICE" "$RULES_DIR"; then
        echo "Aviso: .ai/rules cita um container que nao e ${APP_SERVICE}; ajuste manual." >&2
    fi
fi

echo "OK:"
echo "  app: ${APP_SERVICE}"
echo "  web: ${WEB_SERVICE}"
echo "  fastcgi_pass: ${APP_SERVICE}:9000"
