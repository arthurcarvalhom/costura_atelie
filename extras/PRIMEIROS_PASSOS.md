# 🚀 Primeiros Passos - Costura Ateliê

Guia passo a passo para começar a usar o sistema.

## 📋 Checklist de Instalação

### 1️⃣ Verificação do Sistema
- [ ] PHP 7.4+ instalado
- [ ] MySQL 5.7+ instalado
- [ ] Apache com mod_rewrite ativado
- [ ] XAMPP rodando

**Para verificar:**
1. Acesse `http://localhost/costura/verificacao.php`
2. Todos os itens devem estar em ✅ verde
3. Se houver erros ❌, resolva antes de continuar

### 2️⃣ Configuração do Banco de Dados

**Passo 1: Criar Banco de Dados**
```bash
# Via phpMyAdmin (http://localhost/phpmyadmin)
1. Clique em "Nova" no painel esquerdo
2. Digite: costura_atelier
3. Clique em "Criar"
```

**Passo 2: Importar Schema**
```bash
1. Abra phpMyAdmin
2. Selecione o banco "costura_atelier"
3. Clique em "Importar"
4. Escolha arquivo: sql/schema.sql
5. Clique em "Executar"
```

**Passo 3: Importar Dados de Teste (Opcional)**
```bash
1. Selecione banco "costura_atelier" novamente
2. Clique em "Importar"
3. Escolha arquivo: sql/seed.sql
4. Clique em "Executar"
```

### 3️⃣ Criar Primeiro Usuário Admin

1. Acesse: `http://localhost/costura/criar_usuario.php`
2. Preencha os dados:
   - **Nome**: Seu nome
   - **Email**: seu@email.com
   - **Senha**: Mínimo 8 caracteres
3. Clique em "Criar Usuário"
4. Sistema mostrará confirmação ✅

### 4️⃣ Fazer Login

1. Acesse: `http://localhost/costura/admin/login.php`
2. Use credenciais criadas no passo anterior
3. Clique em "Entrar"
4. Você verá o Dashboard 🎉

---

## 📝 Roteiro de Uso Recomendado

### Primeiro Dia - Configuração Inicial (30 min)

**Estoque (10 min)**
1. Vá em **Estoque** no painel
2. Clique em **Adicionar Matéria-Prima**
3. Adicione seus materiais:
   - Tecido (algodão, tricô, etc.)
   - Renda (várias cores/tamanhos)
   - Enchimento (poliéster)
   - Linha (cores principais)

**Produtos (15 min)**
1. Vá em **Produtos**
2. Clique em **Novo Produto**
3. Adicione 3-5 produtos principais:
   - Nome: Ex. "Ninho Redutor Rosa"
   - Descrição
   - Categoria
   - Foto (clique em "Escolher arquivo")
   - Preço de venda
   - Custo de produção
   - Materiais utilizados

**Dashboard (5 min)**
1. Volte ao **Dashboard**
2. Veja as estatísticas atualizadas
3. Confirme que tudo está mostrando corretamente

### Segundo Dia - Operacional (1 hora)

**Criar Primeiro Pedido (20 min)**
1. Vá em **Pedidos**
2. Clique em **Novo Pedido**
3. Preencha:
   - Cliente (crie novo)
   - Selecione produtos
   - Data de entrega
4. Clique em **Criar Pedido**

**Agenda de Produção (15 min)**
1. Vá em **Produção**
2. Você verá tarefas do pedido criado
3. Clique para editar status
4. Marque como "Em Produção"

**Gestão Financeira (15 min)**
1. Vá em **Financeiro**
2. Veja resumo de vendas
3. Analise lucro dos pedidos
4. Use filtros de data para relatórios

**Catálogo Público (10 min)**
1. Acesse: `http://localhost/costura/public/`
2. Veja como clientes veem seus produtos
3. Teste busca e filtros
4. Clique em um produto para ver detalhes

---

## 🔐 Dados de Teste (Se Importou seed.sql)

### Usuários Pré-criados
| Email | Senha | Tipo |
|-------|-------|------|
| admin@costura.com | admin123 | Admin |

### Produtos de Teste
- 6 produtos em categorias diferentes
- Fotos placeholder incluídas
- Preços variados

### Pedidos de Teste
- 4 pedidos em diferentes estágios
- Status: Pendente, Em Produção, Pronto, Entregue

### Estoque de Teste
- 8 matérias-primas com quantidades
- Várias com alertas de estoque baixo

---

## 🎯 Próximos Passos Recomendados

### Customização (1-2 horas)
- [ ] Editar contato WhatsApp em `public/index.php` (linha 210)
- [ ] Editar cores em `assets/css/style.css` (linhas 30-40)
- [ ] Adicionar sua logo em `includes/navbar.php`
- [ ] Personalizar descrição no catálogo público

### Operacional
- [ ] Criar categorias específicas de produto
- [ ] Registrar todos os seus materiais no estoque
- [ ] Listar todos os produtos do ateliê
- [ ] Começar a registrar pedidos de clientes

### Avançado (Opcional)
- [ ] Integrar WhatsApp Business API (ver `API_DOCS.md`)
- [ ] Configurar email de notificações
- [ ] Backup automático do banco de dados
- [ ] Configurar DNS e domínio próprio

---

## ❓ Perguntas Frequentes

### P: Como alterar a senha?
**R:** Atualmente não há função de "Esqueci senha". Para alterar:
1. Acesse phpMyAdmin
2. Vá para tabela `usuarios`
3. Edite o usuário
4. Clique em "Editar"
5. Deixe a senha em branco e salve
6. Sistema pedirá para criar nova senha no próximo login

### P: Como fazer backup?
**R:** Por phpMyAdmin:
1. Selecione banco `costura_atelier`
2. Abra aba "Exportar"
3. Clique em "Ir"
4. Arquivo SQL será baixado

### P: Posso ter múltiplos usuários?
**R:** Sim! Vá em `admin/` e procure "Usuários" (será adicionado em v1.1). Por enquanto, use `criar_usuario.php` novamente.

### P: Como adicionar categorias de produtos?
**R:** Edite `config/database.php` e procure pela função `obter_categorias()`, ou insira diretamente no phpMyAdmin na tabela `categorias`.

### P: A foto não salvou, o que fazer?
**R:** Verifique:
1. Arquivo é imagem (PNG, JPG, GIF)?
2. Tamanho é menor que 5MB?
3. Pasta `assets/uploads/` existe e tem permissão de escrita?
4. Veja erros em `php://stderr` ou arquivo de log do servidor

### P: Como funciona o cálculo de lucro?
**R:** Lucro = Preço de Venda - Custo de Produção
- Custo = Quantidade de cada material × Preço unitário
- Calculado automaticamente quando você cria um pedido

### P: Posso usar em produção (internet)?
**R:** Sim! Mas primeiro:
1. Mude senha padrão (se usou seed.sql)
2. Configure HTTPS/SSL
3. Implemente rate limiting
4. Faça backups regulares
5. Considere adicionar 2FA

---

## 🚨 Troubleshooting

### Erro: "Conexão com banco de dados falhou"
```
Solução:
1. Confirme MySQL está rodando
2. Verifique dados em config/database.php
3. Teste com phpMyAdmin
4. Reinicie XAMPP
```

### Erro: "Acesso negado ao fazer upload"
```
Solução:
1. No terminal (como admin):
   - Windows: icacls "C:\xampp\htdocs\costura\assets\uploads" /grant:r Everyone:F
   - Linux: chmod -R 777 assets/uploads
2. Recarregue página
```

### Erro: "Módulo rewrite não ativado"
```
Solução:
1. Abra C:\xampp\apache\conf\httpd.conf
2. Procure: #LoadModule rewrite_module modules/mod_rewrite.so
3. Remova o # do início
4. Reinicie Apache
```

### Erro em "Verificação de Sistema"
```
Solução:
1. Clique em cada erro para ver detalhes
2. Siga instruções específicas mostradas
3. Se erro persistir, abra issue com screenshot
```

---

## 📚 Recursos Adicionais

- **📖 README.md** - Documentação completa
- **📖 INSTALACAO_RAPIDA.md** - Guia de instalação
- **📖 API_DOCS.md** - Documentação da API
- **📖 CHANGELOG.md** - Histórico de versões
- **🌐 http://localhost/costura/inicio.html** - Página de atalhos

---

## ✅ Checklist Final de Configuração

- [ ] Banco de dados criado e importado
- [ ] Verificação do sistema passou ✅
- [ ] Primeiro usuário criado
- [ ] Consegui fazer login
- [ ] Dashboard mostra dados
- [ ] Consegui adicionar um produto
- [ ] Consegui adicionar matéria-prima
- [ ] Catálogo público está visível
- [ ] Contatos ajustados (WhatsApp, email)
- [ ] Sistema pronto para uso! 🎉

---

## 🎉 Parabéns!

Seu sistema está configurado e pronto para usar. 

**Próximo passo:** Comece a registrar seus produtos e pedidos no painel!

---

**Dúvidas?** Consulte:
- `README.md` - Documentação técnica
- `API_DOCS.md` - Detalhes de API
- Página de verificação - `verificacao.php`

**Data:** 11 de abril de 2026  
**Versão:** 1.0.0
