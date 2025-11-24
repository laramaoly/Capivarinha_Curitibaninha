#!/usr/bin/env bash
set -euo pipefail

echo "🧉 Inicializando Capivarinha Curitibaninha..."

USER_NAME="$(whoami)"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Aguardar MySQL ficar pronto
echo "Aguardando MySQL..."
for i in {1..30}; do
  if sudo mysql -e "SELECT 1" >/dev/null 2>&1; then
    echo "✅ MySQL pronto!"
    break
  fi
  if [ $i -eq 30 ]; then
    echo "❌ MySQL não respondeu após 30 segundos"
    exit 1
  fi
  sleep 1
done

# Configurar banco de dados
echo "Configurando banco de dados..."
sudo mysql -e "DROP DATABASE IF EXISTS capityper; CREATE DATABASE capityper CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE USER IF NOT EXISTS 'admin'@'%' IDENTIFIED BY 'admin'; GRANT ALL PRIVILEGES ON capityper.* TO 'admin'@'%' WITH GRANT OPTION; FLUSH PRIVILEGES;"

# Importar schema
sudo mysql -u admin -padmin capityper < "$SCRIPT_DIR/sql/database_setup.sql"

# Atualizar dicas no banco (garante que a coluna exista e que as dicas estejam preenchidas)
if command -v php8.3 >/dev/null 2>&1; then
  php8.3 "$SCRIPT_DIR/update_db_hints.php" > /dev/null 2>&1 || true
else
  php "$SCRIPT_DIR/update_db_hints.php" > /dev/null 2>&1 || true
fi

# Criar arquivo .env
cat > "$SCRIPT_DIR/.env" <<EOF
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=capityper
DB_USER=admin
DB_PASS=admin
EOF

echo "✅ Pronto! Execute para iniciar o servidor:"
echo "   php -S 0.0.0.0:8000"
echo "   Acesse: http://127.0.0.1:8000"

exit 0
