#!/usr/bin/env bash
set -euo pipefail

echo "🧉 Inicializando Capivarinha Curitibaninha..."

USER_NAME="$(whoami)"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Tudo em uma única sequência
sudo mysql -e "DROP DATABASE IF EXISTS capityper; CREATE DATABASE capityper CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE USER IF NOT EXISTS 'admin'@'%' IDENTIFIED BY 'admin'; GRANT ALL PRIVILEGES ON capityper.* TO 'admin'@'%' WITH GRANT OPTION; FLUSH PRIVILEGES;" && \
sudo mysql capityper < "$SCRIPT_DIR/sql/database_setup.sql" && \
php8.3 "$SCRIPT_DIR/update_db_hints.php" > /dev/null 2>&1 && \
sudo usermod -aG mysql "$USER_NAME" 2>/dev/null || true && \
cat > "$SCRIPT_DIR/.env" <<EOF && \
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=capityper
DB_USER=admin
DB_PASS=admin
EOF
echo "✅ Pronto! Execute para iniciar o servidor:"
echo "   php8.3 -S 0.0.0.0:8000"
echo "   Acesse: http://127.0.0.1:8000"

exit 0
