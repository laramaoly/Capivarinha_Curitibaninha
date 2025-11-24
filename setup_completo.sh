#!/bin/bash

echo "🧹 Limpiando configuraciones previas y empezando de cero..."

# 1. Instalar MySQL Server y PHP 8.3 (la versión compatible con Ubuntu actual)
sudo apt-get update -qq
sudo apt-get install -y mysql-server php8.3 php8.3-mysql php8.3-common php8.3-mbstring php8.3-xml

# 2. Asegurar que usamos PHP 8.3 por defecto (ignorar el PHP 8.0 roto de Codespaces)
sudo update-alternatives --set php /usr/bin/php8.3

# 3. Iniciar MySQL
echo "🚀 Iniciando base de datos..."
sudo service mysql start

# 4. Configurar usuario 'admin' en MySQL (para que coincida con tu config/database.php)
# Esto arregla el error "Access denied" sin tocar tu código PHP
echo "🔧 Configurando usuario SQL..."
sudo mysql -e "CREATE USER IF NOT EXISTS 'admin'@'localhost' IDENTIFIED BY 'admin';"
sudo mysql -e "GRANT ALL PRIVILEGES ON *.* TO 'admin'@'localhost' WITH GRANT OPTION;"
sudo mysql -e "FLUSH PRIVILEGES;"

# 5. Ejecutar tu script de base de datos usando PHP 8.3 explícitamente
echo "📂 Creando tablas..."
php8.3 setup_database.php

echo "✅ INSTALACIÓN COMPLETADA."
echo "🌍 Iniciando servidor... Abre el navegador cuando aparezca el mensaje."

# 6. Iniciar servidor Web
php8.3 -S 0.0.0.0:8000