#!/usr/bin/env bash
set -euo pipefail

echo "🧉 Inicializando Capivarinha Curitibaninha..."

USER_NAME="$(whoami)"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Aguardar MySQL ficar pronto
echo "Aguardando MySQL..."

# Verificar se MySQL/MariaDB está instalado
if ! command -v mysql >/dev/null 2>&1; then
  echo "⚠️  MySQL não está instalado. Configure manualmente ou use Docker."
  echo "Continuando com criação de arquivo .env..."
else
  # Se o serviço MySQL não estiver ativo, tenta iniciar (útil em containers/resets)
  if ! sudo mysqld_safe --daemonize >/dev/null 2>&1; then
    echo "⚠️  Não foi possível iniciar MySQL, continuando..."
  fi
  sleep 2

  for i in {1..30}; do
    if sudo mysql -e "SELECT 1" >/dev/null 2>&1; then
      echo "✅ MySQL pronto!"
      
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
      
      break
    fi
    if [ $i -eq 30 ]; then
      echo "❌ MySQL não respondeu após 30 segundos"
      exit 1
    fi
    sleep 1
  done
fi

# Criar arquivo .env
cat > "$SCRIPT_DIR/.env" <<EOF
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=capityper
DB_USER=admin
DB_PASS=admin
EOF

# Pequena pausa para o usuário apreciar o progresso (defina NO_PAUSE=1 para pular)
if [ "${NO_PAUSE:-0}" != "1" ]; then
  echo "Aguardando 2 segundos para que você veja o progresso..."
  sleep 2
fi

echo "✅ Pronto! Execute para iniciar o servidor:"
echo "   php -S 0.0.0.0:8000"
echo "   Acesse: http://127.0.0.1:8000"

# Verificação final: tenta instanciar RankingController para detectar problemas de PDO/config
PHP_BIN="php8.3"
if ! command -v "$PHP_BIN" >/dev/null 2>&1; then
  PHP_BIN="php"
fi

echo "Executando checagem rápida do controller de ranking com ${PHP_BIN}..."
# Executa o check em PHP; captura saída e não deixa o script falhar (para debugging)
CHECK_OUT=$(
  "$PHP_BIN" -r 'try { require "controllers/RankingController.php"; new RankingController(); echo "RankingController: OK\n"; } catch (Throwable $e) { echo "RankingController: ERR: ".addslashes($e->getMessage())."\n"; exit(1); }' 2>&1
) || true

echo "$CHECK_OUT"

exit 0
