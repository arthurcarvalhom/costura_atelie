# 🚀 Guia Rápido de Instalação - Costura Ateliê

## Passo 1: Preparação Inicial

1. **Certifique-se que MySQL está rodando**
   - XAMPP: Start Apache e MySQL

2. **Acesse phpMyAdmin**
   - URL: `http://localhost/phpmyadmin`

## Passo 2: Criar Banco de Dados

### Opção A: Automática (Recomendado)

1. Abra phpMyAdmin
2. Clique em "novo"
3. Digite: `costura_atelier`
4. Clique em "Criar"
5. Selecione o banco criado
6. Clique em "Importar"
7. Escolha: `costura/sql/schema.sql`
8. Clique em "Executar"
9. Repita os passos 6-8 para o arquivo `costura/sql/seed.sql`

### Opção B: Manual (SQL)

```sql
-- Copie TODO o conteúdo de schema.sql
-- Cole no phpMyAdmin → Abas SQL
-- Clique em "Executar"

-- Depois repita com seed.sql
```

## Passo 3: Verificar Configurações

Abra: `costura/config/database.php`

```php
// Verifique se está assim:
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'costura_atelier');
```

Se sua senha MySQL é diferente, altere a linha `DB_PASS`

## Passo 4: Acessar o Sistema

### Painel de Administração
```
http://localhost/costura/admin
```

**Login:**
- Email: `admin@costura.com`
- Senha: `admin123`

### Catálogo Público
```
http://localhost/costura
```

## Passo 5: Começar a Usar

### Na primeira vez:

1. **Faça login** no painel
2. **Adicione matérias-primas** em Estoque
3. **Cadastre alguns produtos** (com fotos se desejar)
4. **Crie pedidos** de teste
5. **Visualize a agenda** de produção
6. **Confira o financeiro**

## ⚠️ Possíveis Problemas

### "Erro ao conectar ao banco"
```
❌ Solução: Verifique credentials em config/database.php
```

### "Pasta uploads não tem permissão"
```
❌ Solução: 
- Windows: Clique direito na pasta → Propriedades → Segurança → Editar
- Dê acesso total para Todos
```

### "Páginas mostram conteúdo em branco"
```
❌ Solução: Verifique error_log do PHP
- Procure em: C:\xampp\logs\php_error.log
```

### "Login não funciona"
```
❌ Solução: Verifique se há usuário no banco
- phpMyAdmin → costura_atelier → usuarios
- Deve haver pelo menos 1 usuário
```

## 📝 Customizações Rápidas

### Alterar Logo/Título
`includes/navbar.php` - Linha 10
```php
<h1 class="navbar-title">Seu Título Aqui</h1>
```

### Alterar Cores
`assets/css/style.css` - Linha 12
```css
--cor-primaria: #FF6B9D;  /* Mude para sua cor */
```

### Adicionar Contato WhatsApp
`public/index.php` - Busque por "wa.me"
```html
https://wa.me/SEU_NUMERO_AQUI
```

## 🔄 Fazer Backup

### Banco de Dados
```
phpMyAdmin → costura_atelier → Exportar
→ Selecione tudo → Ir
```

### Arquivo de Projeto
```
Copiar pasta: C:\xampp\htdocs\costura
```

## 📞 Precisa de Ajuda?

Verifique:
1. Se PHP está na versão 7.4+
2. Se MySQL/MariaDB está rodando
3. Se os uploads têm permissão 777
4. Se não há typos nas credenciais

---

**Pronto! Sistema instalado com sucesso! 🎉**
