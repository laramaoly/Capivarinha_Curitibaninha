#!/bin/bash

echo "🕵️  Iniciando configuração do ambiente Codespaces..."

# 1. Atualizar repositórios e instalar driver MySQL genérico (alinha com a versão ativa do PHP)
sudo apt-get update
sudo apt-get install -y php-mysql

# 2. Iniciar o serviço do Banco de Dados (Obrigatório no Codespaces)
echo "🚀 Iniciando serviço MySQL..."
sudo service mysql start

# 3. Identificar o php.ini ativo
PHP_INI=$(php --ini | grep "Loaded Configuration File" | cut -d: -f2 | xargs)
echo "📂 Arquivo de configuração PHP encontrado: $PHP_INI"

# 4. Verificar e ativar extensão pdo_mysql corretamente
if grep -q "^extension=pdo_mysql" "$PHP_INI"; then
    echo "⚠️  A extensão pdo_mysql já está ativada."
else
    echo "🔧 Ativando extensão pdo_mysql..."
    echo "extension=pdo_mysql" | sudo tee -a "$PHP_INI" > /dev/null
fi

# 5. Verificar e ativar extensão mysqli (caso não esteja)
if grep -q "^extension=mysqli" "$PHP_INI"; then
    echo "⚠️  A extensão mysqli já está ativada."
else
    echo "🔧 Ativando extensão mysqli..."
    echo "extension=mysqli" | sudo tee -a "$PHP_INI" > /dev/null
fi

# 6. Verificação final
echo "🔍 Verificando módulos carregados:"
php -m | grep -E 'pdo_mysql|mysqli'

echo "✅ Ambiente pronto! Execute agora: php setup_database.php"
