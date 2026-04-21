#!/bin/bash

# Script de Instalação do Costura Ateliê
# Copie este arquivo para a pasta costura e execute: bash install.sh

echo "======================================"
echo "🧵 Costura Ateliê - Auto Instalador"
echo "======================================"
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Verificar PHP
echo "Verificando PHP..."
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n 1 | awk '{print $2}')
    echo -e "${GREEN}✓${NC} PHP $PHP_VERSION encontrado"
else
    echo -e "${RED}✗${NC} PHP não encontrado"
    exit 1
fi

# Criar permissões de upload
echo ""
echo "Criando permissões de upload..."
if [ -d "assets/uploads/produtos" ]; then
    chmod 777 assets/uploads/produtos
    echo -e "${GREEN}✓${NC} Permissões definidas"
else
    mkdir -p assets/uploads/produtos
    chmod 777 assets/uploads/produtos
    echo -e "${GREEN}✓${NC} Pasta criada com permissões"
fi

# Criar .env
echo ""
echo "Configurando variáveis de ambiente..."
if [ ! -f ".env" ]; then
    cp .env.example .env
    echo -e "${GREEN}✓${NC} Arquivo .env criado (edite com suas credenciais)"
else
    echo -e "${YELLOW}!${NC} Arquivo .env já existe"
fi

echo ""
echo "======================================"
echo "✅ Instalação Preparada!"
echo "======================================"
echo ""
echo "Próximos passos:"
echo "1. Abra o phpMyAdmin: http://localhost/phpmyadmin"
echo "2. Crie banco: costura_atelier"
echo "3. Importe: sql/schema.sql"
echo "4. Importe: sql/seed.sql"
echo "5. Edite: config/database.php (se necessário)"
echo ""
echo "Ou acesse:"
echo "• Verificação: http://localhost/costura/verificacao.php"
echo "• Criar Usuário: http://localhost/costura/criar_usuario.php"
echo "• Admin: http://localhost/costura/admin"
echo ""
