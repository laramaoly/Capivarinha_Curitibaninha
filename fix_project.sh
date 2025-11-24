#!/bin/bash

echo "🕵️  Iniciando diagnóstico profundo..."

# 1. Identificar qual php.ini está sendo usado pelo CLI
PHP_INI=$(php --ini | grep "Loaded Configuration File" | cut -d: -f2 | xargs)
echo "📂 Arquivo de configuração PHP encontrado: $PHP_INI"

# 2. Verificar se a extensão está ativada nesse arquivo
if grep -q "extension=pdo_mysql" "$PHP_INI"; then
    echo "⚠️  A extensão pdo_mysql já parece estar ativada no php.ini."
else
    echo "🔧 Ativando extensão pdo_mysql no php.ini..."
    # Adiciona a linha se não existir
    echo "extension=pdo_mysql" | sudo tee -a "$PHP_INI" > /dev/null
    echo "extension=mysqli" | sudo tee -a "$PHP_INI" > /dev/null
fi

# 3. Reinstalar o pacote php8.0-mysql (Garantia)
echo "📦 Reinstalando driver MySQL para PHP 8.0..."
sudo apt-get update -qq
sudo apt-get install -y php8.0-mysql

# 4. Verificar módulos carregados
echo "🔍 Verificando módulos carregados:"
php -m | grep -E 'pdo_mysql|mysqli'

echo "✅ Correção concluída. Tente rodar o servidor agora."