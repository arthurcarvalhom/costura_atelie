# 🏗️ Arquitetura do Sistema - Costura Ateliê

Documentação da arquitetura técnica e fluxos de dados do sistema.

---

## 📊 Arquitetura Geral

```
┌─────────────────────────────────────────────────────────────┐
│                    CAMADA APRESENTAÇÃO                      │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  PÚBLICO              │           ADMIN DASHBOARD             │
│  ┌─────────────────┐ │  ┌──────────────────────────────────┐ │
│  │ Catálogo        │ │  │ Dashboard Principal              │ │
│  │ (public/index)  │ │  │ - Estatísticas                   │ │
│  │                 │ │  │ - Alertas                        │ │
│  │ - Grid Produtos │ │  │ - Últimos Pedidos                │ │
│  │ - Search/Filter │ │  ├──────────────────────────────────┤ │
│  │ - Product Modal │ │  │ Módulos Funcionais               │ │
│  │ - Contato       │ │  ├──────────────────────────────────┤ │
│  └─────────────────┘ │  │ 📦 Produtos (CRUD)               │ │
│                      │  │    - Fotos                       │ │
│                      │  │    - Categorias                  │ │
│                      │  │    - Lucro                       │ │
│                      │  │                                  │ │
│                      │  │ 🏢 Estoque (Inventário)          │ │
│                      │  │    - Tecido, Renda, etc          │ │
│                      │  │    - Entrada/Saída               │ │
│                      │  │    - Alertas estoque baixo       │ │
│                      │  │                                  │ │
│                      │  │ 🛒 Pedidos (Vendas)              │ │
│                      │  │    - Clientes                    │ │
│                      │  │    - Itens                       │ │
│                      │  │    - Status                      │ │
│                      │  │                                  │ │
│                      │  │ 📅 Produção (Agenda)             │ │
│                      │  │    - Tarefas por data            │ │
│                      │  │    - Prioridades                 │ │
│                      │  │                                  │ │
│                      │  │ 💰 Financeiro (Relatórios)       │ │
│                      │  │    - Vendas                      │ │
│                      │  │    - Custos                      │ │
│                      │  │    - Lucro                       │ │
│                      │  └──────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
          ▲                                    ▲
          │                                    │
          └────────────────────┬───────────────┘
                               │ HTTP/HTTPS
                               │ JavaScript/AJAX
                               ▼
        ┌──────────────────────────────────────────────┐
        │     CAMADA LÓGICA (Backend PHP)              │
        ├──────────────────────────────────────────────┤
        │                                              │
        │  config/database.php                         │
        │  - Configuração de banco                     │
        │  - Constantes globais                        │
        │                                              │
        │  includes/functions.php                      │
        │  - 50+ Funções de negócio                    │
        │  - Autenticação                              │
        │  - CRUD operações                            │
        │  - Cálculos financeiros                      │
        │  - Validação                                 │
        │                                              │
        │  admin/api/*                                 │
        │  - Endpoints JSON                            │
        │  - Integração AJAX                           │
        │                                              │
        │  includes/* (Templates)                      │
        │  - header.php                                │
        │  - footer.php                                │
        │  - navbar.php                                │
        │                                              │
        └──────────────────────────────────────────────┘
                      ▲              ▲
                      │              │ Prepared Statements
                      │              │ SQL Injection Protection
                      │              ▼
        ┌──────────────────────────────────────────────┐
        │       CAMADA DADOS (MySQL Database)          │
        ├──────────────────────────────────────────────┤
        │                                              │
        │  Tabelas Core (11)                           │
        │  ├─ usuarios                                 │
        │  ├─ categorias                               │
        │  ├─ produtos                                 │
        │  ├─ produto_materiais                        │
        │  ├─ materias_primas                          │
        │  ├─ movimentacao_estoque                     │
        │  ├─ clientes                                 │
        │  ├─ pedidos                                  │
        │  ├─ pedido_itens                             │
        │  ├─ agenda_producao                          │
        │  └─ relatorio_financeiro                     │
        │                                              │
        │  Views (1)                                   │
        │  └─ view_custo_pedidos                       │
        │     (Cálculos de lucro)                      │
        │                                              │
        │  Índices & Relacionamentos                   │
        │  - Foreign Keys                              │
        │  - Índices em campo-chave                    │
        │  - Constraints de integridade                │
        │                                              │
        └──────────────────────────────────────────────┘
```

---

## 🔄 Fluxos Principais

### 1️⃣ Fluxo de Autenticação

```
┌──────────────┐
│ User Acessa  │
│ login.php    │
└──────────────┘
       │
       ▼
┌──────────────────────┐
│ Submit Formulário    │
│ (email + senha)      │
└──────────────────────┘
       │
       ▼
┌────────────────────────────────────┐
│ fazer_login()                      │
│ 1. Sanitize email                  │
│ 2. Query usuário no banco          │
│ 3. password_verify(senha)          │
└────────────────────────────────────┘
       │
       ├─ Não encontrado / Senha errada
       │       ▼
       │  ❌ Erro: "Credenciais inválidas"
       │
       └─ Senha correta
               ▼
       ┌──────────────────────┐
       │ Criar Session        │
       │ $_SESSION['usuario'] │
       └──────────────────────┘
               ▼
       ┌──────────────────────┐
       │ Redirect para        │
       │ admin/index.php      │
       │ (Dashboard)          │
       └──────────────────────┘
```

### 2️⃣ Fluxo de Produto (CRUD)

```
CRIAR PRODUTO
┌──────────────┐
│ Nome         │
│ Descrição    │  Save
│ Categoria    │────────┐
│ Preço        │        │
│ Custo        │        │
│ Foto         │        │
└──────────────┘        │
                        ▼
            ┌──────────────────────┐
            │ Upload imagem        │
            │ - Validar tipo       │
            │ - Validar tamanho    │
            │ - Salvar em pasta    │
            └──────────────────────┘
                        │
                        ▼
            ┌──────────────────────────┐
            │ INSERT INTO produtos    │
            │ - Hash URL foto         │
            │ - Salvar em banco       │
            │ - Retornar ID produto  │
            └──────────────────────────┘
                        │
                        ▼
            ┌──────────────────────────┐
            │ INSERT INTO             │
            │ produto_materiais       │
            │ - Ligar produto ↔       │
            │   matérias-primas       │
            │ - Quantidade necessária │
            └──────────────────────────┘
                        │
                        ▼
            ┌──────────────────────┐
            │ ✅ Produto Criado    │
            │ Redirect para Lista  │
            └──────────────────────┘

EDITAR PRODUTO
┌────────────────┐
│ Carregar dados │ UPDATE SET
│ do banco       │─────────────┐
│ em formulário  │             │
└────────────────┘             ▼
                   ┌─────────────────────┐
                   │ UPDATE produtos     │
                   │ SET nome, desc, etc │
                   └─────────────────────┘
                               │
                        ✅ Atualizado

DELETAR PRODUTO
┌────────────────┐
│ Produto ID     │
└────────────────┘
        │
        ▼ (Soft delete)
┌────────────────────┐
│ UPDATE produtos    │
│ SET ativo = 0      │
│ (Não remove dados) │
└────────────────────┘
        │
        ▼
   ✅ Deletado
```

### 3️⃣ Fluxo de Pedido

```
┌─────────────────────────────────────┐
│ Cliente acessa Catálogo Público     │
│ (public/index.php)                  │
└─────────────────────────────────────┘
        │
        ├─ Busca por produto
        │       ▼
        │  ┌──────────────────────┐
        │  │ Query produtos       │
        │  │ WHERE nome LIKE %q%  │
        │  └──────────────────────┘
        │
        └─ Clica em produto
                ▼
        ┌──────────────────────┐
        │ Modal se abre        │
        │ - Foto grande        │
        │ - Descrição          │
        │ - Preço              │
        │ - WhatsApp/Email     │
        └──────────────────────┘
                ▼
        ┌──────────────────────┐
        │ Clica "Contatar no   │
        │ WhatsApp"            │
        └──────────────────────┘
                ▼
        ┌──────────────────────────────┐
        │ Abre WhatsApp com texto      │
        │ "Olá, tenho interesse no     │
        │  [PRODUTO]..."               │
        └──────────────────────────────┘

---ADMIN REGISTRA PEDIDO---
        ▼
┌──────────────────────────┐
│ Admin vai em Pedidos     │
│ Novo Pedido              │
└──────────────────────────┘
        │
        ▼
┌──────────────────────────┐
│ 1. Seleciona Cliente     │
│    (ou cria novo)        │
│ 2. Seleciona Produtos    │
│ 3. Define data entrega   │
│ 4. Clica "Criar"         │
└──────────────────────────┘
        │
        ▼
┌────────────────────────────────────┐
│ Sistema cria registro              │
│                                    │
│ INSERT INTO pedidos:               │
│ - cliente_id                       │
│ - data_pedido (NOW)                │
│ - data_entrega_prevista            │
│ - status = "Pendente"              │
│ - valor_total (calc)               │
└────────────────────────────────────┘
        │
        ▼
┌────────────────────────────────────┐
│ INSERT INTO pedido_itens:          │
│ - pedido_id                        │
│ - produto_id                       │
│ - quantidade                       │
│ - preco_unitario                   │
│ - subtotal (GENERATED)             │
└────────────────────────────────────┘
        │
        ▼
┌────────────────────────────────────┐
│ INSERT INTO agenda_producao:       │
│ - Cria tarefas de produção         │
│   (corte, costura, embalagem)      │
│ - Status = "Pendente"              │
│ - data_prevista = antes da entrega │
└────────────────────────────────────┘
        │
        ▼
┌──────────────────────┐
│ ✅ Pedido Criado!    │
│ Mostra no Dashboard  │
└──────────────────────┘
        │
        ▼ (Quando pronto)
┌──────────────────────┐
│ Admin marca como     │
│ "Pronto" em Pedidos  │
└──────────────────────┘
        │
        ▼
┌──────────────────────┐
│ UPDATE pedidos       │
│ SET status="Pronto"  │
│ data_conclusao=NOW   │
└──────────────────────┘
        │
        ▼
┌──────────────────────┐
│ ✅ Pronto para envio │
└──────────────────────┘
```

### 4️⃣ Fluxo de Estoque

```
┌─────────────────────────────────────┐
│ Admin vai em Estoque                │
└─────────────────────────────────────┘
        │
        ├─ VER ESTOQUE
        │   ├─ Lista todas matérias-primas
        │   ├─ Mostra quantidade atual
        │   ├─ Status (OK/Atenção/Baixo)
        │   └─ Alertas se < quantidade_mínima
        │
        └─ REGISTRAR MOVIMENTO
                ▼
        ┌──────────────────────────┐
        │ Seleciona Material:      │
        │ - Tecido Vermelho        │
        │ - Renda Branca           │
        │ - Enchimento Poliéster   │
        │ - Linha preta            │
        └──────────────────────────┘
                │
                ▼
        ┌──────────────────────────┐
        │ Escolhe tipo:            │
        │ ⊕ ENTRADA (Compra)       │
        │ ⊖ SAÍDA (Uso)            │
        └──────────────────────────┘
                │
                ▼
        ┌──────────────────────────┐
        │ Digita quantidade        │
        │ e descrição              │
        └──────────────────────────┘
                │
                ▼
        ┌────────────────────────────────┐
        │ Sistema registra:              │
        │                                │
        │ INSERT INTO                    │
        │ movimentacao_estoque:          │
        │ - materia_prima_id             │
        │ - tipo (entrada/saída)         │
        │ - quantidade                   │
        │ - descricao                    │
        │ - data_movimentacao (NOW)      │
        └────────────────────────────────┘
                │
                ▼
        ┌────────────────────────────────┐
        │ UPDATE materias_primas:        │
        │                                │
        │ IF tipo = 'entrada':           │
        │   quantidade_estoque += qtd    │
        │ IF tipo = 'saída':             │
        │   quantidade_estoque -= qtd    │
        └────────────────────────────────┘
                │
                ▼
        ┌────────────────────────────────┐
        │ Verifica alertas:              │
        │ IF quantidade < quantidade_min │
        │   MARCAR COMO "ESTOQUE BAIXO"  │
        │   MOSTRAR ⚠️ ALERTA            │
        └────────────────────────────────┘
                │
                ▼
        ┌──────────────────────┐
        │ ✅ Movimento         │
        │ Registrado!          │
        │ Estoque Atualizado   │
        └──────────────────────┘
```

### 5️⃣ Fluxo Financeiro

```
┌──────────────────────────┐
│ Admin em Financeiro      │
│ Define período:          │
│ - Data início            │
│ - Data final (ou mês)    │
└──────────────────────────┘
        │
        ▼
┌──────────────────────────────────┐
│ Query JOIN com view_custo_pedidos:
│                                  │
│ SELECT p.*, v.custo_material,    │
│        (p.valor_total -          │
│         v.custo_material) lucro  │
│ FROM pedidos p                   │
│ JOIN view_custo_pedidos v        │
│ WHERE data_pedido BETWEEN ...    │
└──────────────────────────────────┘
        │
        ▼
┌──────────────────────────────────┐
│ Cálculos Automáticos:           │
│                                 │
│ Total Vendas = SUM(valor_total) │
│ Total Custos = SUM(custo)       │
│ Total Lucro = (Vendas - Custos) │
│ Margem % = (Lucro/Vendas) × 100 │
│ Ticket Médio = Vendas / Count   │
│ Lucro Médio = Lucro / Count     │
└──────────────────────────────────┘
        │
        ▼
┌──────────────────────────┐
│ Exibir Dashboard:        │
│                          │
│ 💵 4 Cards de resumo     │
│ 📊 Tabela detalhada      │
│ 💡 Dicas de melhoria     │
│ 📈 Gráfico (CSS)         │
└──────────────────────────┘
```

---

## 🗄️ Diagrama de Banco de Dados

```
┌─────────────────────────────────────────────────────────────┐
│                    CAMADA DE DADOS                          │
└─────────────────────────────────────────────────────────────┘

usuarios                    
├─ id (PK)                 
├─ nome                    
├─ email (UNIQUE)          
├─ senha (BCRYPT)          
├─ data_criacao            
└─ ativo                   

categorias                  
├─ id (PK)                 
├─ nome                    
└─ descricao              

produtos ◄─────── categorias
├─ id (PK)                 FK
├─ nome                    ├─ categoria_id
├─ descricao       ┌─────┘
├─ categoria_id ───┘
├─ preco_venda              
├─ custo_producao           
├─ material                
├─ foto_url                
├─ ativo                   
└─ data_criacao            

clientes                   
├─ id (PK)                 
├─ nome                    
├─ email                   
├─ telefone                
└─ data_criacao            

pedidos ◄────── clientes    
├─ id (PK)         FK      
├─ cliente_id ─────── id   
├─ data_pedido             
├─ data_entrega_prevista   
├─ data_entrega_real       
├─ status (ENUM)           
├─ descricao_customizacao  
├─ valor_total             
└─ observacoes             

pedido_itens ◄──── pedidos ──────┐
├─ id (PK)         FK            │
├─ pedido_id───────── id         │
├─ produto_id ──────────────┐    │
├─ quantidade        (FK)   │    │
├─ preco_unitario    └─────►produos
├─ subtotal (GENERATED)     │    │
└─                          │    │

agenda_producao ◄──── pedidos
├─ id (PK)         FK
├─ pedido_id ─────────── id
├─ tipo_tarefa (ENUM)
├─ data_prevista
├─ data_conclusao
├─ status (ENUM)
├─ responsavel
└─ notas

materias_primas
├─ id (PK)
├─ nome
├─ tipo (ENUM: Tecido, Renda, Enchimento, Linha)
├─ unidade (ENUM: metros, quilos, unidades)
├─ preco_unitario
├─ quantidade_estoque
├─ quantidade_minima
├─ data_criacao
└─ data_atualizacao

movimentacao_estoque ◄──── materias_primas
├─ id (PK)         FK
├─ materia_prima_id ──── id
├─ tipo (ENUM: entrada, saída)
├─ quantidade
├─ data_movimentacao
└─ descricao

produto_materiais ◄──── produtos & materias_primas
├─ id (PK)         FK              FK
├─ produto_id ────────── id   ┌────── id
├─ materia_prima_id ────────┘
└─ quantidade_necessaria

relatorio_financeiro ◄──── pedidos
├─ id (PK)         FK
├─ pedido_id ─────────── id
├─ tipo (ENUM: venda, custo, ajuste)
├─ descricao
├─ valor
└─ data_transacao

VIEW: view_custo_pedidos ◄──── Joins pedidos com materiais
├─ pedido_id
├─ valor_total
├─ custo_material (calculado)
└─ lucro (calculado)
```

---

## 🔐 Segurança em Camadas

```
┌─────────────────────────────────────┐
│ CAMADA 1: ENTRADA (Cliente)         │
├─────────────────────────────────────┤
│ • Validação HTML5 (front-end)       │
│ • Tipo de entrada esperado          │
│ • Tamanho máximo de arquivo         │
└─────────────────────────────────────┘
             ▼
┌─────────────────────────────────────┐
│ CAMADA 2: SANITIZAÇÃO (PHP)         │
├─────────────────────────────────────┤
│ • limpar_entrada() função           │
│ • trim() + strip_tags()             │
│ • htmlspecialchars()                │
│ • Validação de tipo                 │
└─────────────────────────────────────┘
             ▼
┌─────────────────────────────────────┐
│ CAMADA 3: AUTENTICAÇÃO              │
├─────────────────────────────────────┤
│ • verificar_autenticacao()          │
│ • Session verificada                │
│ • User ID em $_SESSION              │
└─────────────────────────────────────┘
             ▼
┌─────────────────────────────────────┐
│ CAMADA 4: BANCO DE DADOS            │
├─────────────────────────────────────┤
│ • Prepared Statements               │
│ • Parametrização (?)                │
│ • Tipo binding (i, s, d, b)         │
│ • SQL Injection Protection          │
└─────────────────────────────────────┘
             ▼
┌─────────────────────────────────────┐
│ CAMADA 5: ARMAZENAMENTO SEGURO      │
├─────────────────────────────────────┤
│ • Senhas com bcrypt                 │
│ • password_hash() + salt            │
│ • password_verify()                 │
│ • Soft deletes (ativo flag)         │
└─────────────────────────────────────┘
```

---

## 📁 Estrutura de Diretórios

```
costura/
├── admin/                          # Painel administrativo
│   ├── index.php                   # Dashboard
│   ├── login.php                   # Autenticação
│   ├── logout.php                  # Logout
│   ├── produtos.php                # Gestão de produtos
│   ├── estoque.php                 # Controle de estoque
│   ├── pedidos.php                 # Gestão de pedidos
│   ├── producao.php                # Agenda de produção
│   ├── financeiro.php              # Relatórios financeiros
│   └── api/                        # Endpoints JSON
│       ├── produtos.php            # API de produtos
│       ├── estoque.php             # API de estoque
│       └── pedidos.php             # API de pedidos
│
├── public/                         # Catálogo público
│   └── index.php                   # Catálogo de produtos
│
├── includes/                       # Componentes reutilizáveis
│   ├── header.php                  # HTML <head>
│   ├── footer.php                  # Rodapé
│   ├── navbar.php                  # Barra de navegação
│   └── functions.php               # Funções de negócio
│
├── config/                         # Configuração
│   └── database.php                # Conexão BD + constantes
│
├── assets/                         # Recursos estáticos
│   ├── css/
│   │   └── style.css               # Estilos
│   ├── js/
│   │   └── script.js               # Interatividade
│   ├── uploads/
│   │   ├── produtos/               # Fotos de produtos
│   │   └── .gitkeep                # Manter diretório
│   └── img/                        # Imagens estáticas
│
├── sql/                            # Scripts SQL
│   ├── schema.sql                  # Estrutura BD
│   └── seed.sql                    # Dados de teste
│
├── .htaccess                       # Rewrite rules
├── .env.example                    # Variáveis ambiente
├── .gitignore                      # Git ignore
├── index.php                       # Redirect público
├── inicio.html                     # Página de atalhos
├── verificacao.php                 # Health check
├── criar_usuario.php               # Criar primeiro usuário
│
└── docs/                           # Documentação
    ├── README.md                   # Documentação completa
    ├── INSTALACAO_RAPIDA.md        # Guia rápido
    ├── PRIMEIROS_PASSOS.md         # Primeiros passos
    ├── API_DOCS.md                 # API
    ├── CHANGELOG.md                # Histórico
    ├── SUMARIO.md                  # Sumário
    └── ARQUITETURA.md              # Este arquivo
```

---

## 🔄 Ciclo de Vida de um Pedido

```
1. CRIAÇÃO
   └─ Status: "Pendente"
   └─ Data criação: NOW()
   └─ Cria itens do pedido
   └─ Cria tarefas de produção

2. INÍCIO DA PRODUÇÃO
   └─ Status: "Em Produção"
   └─ Data início: NOW()
   └─ Tarefas marcadas como "Em Andamento"

3. PRODUÇÃO EM ANDAMENTO
   └─ Status: "Em Produção"
   └─ Admin atualiza Progress das tarefas
   └─ Movimentos de estoque registrados

4. CONCLUSÃO
   └─ Status: "Pronto"
   └─ Data conclusão: NOW()
   └─ Tarefas marcadas como "Concluídas"
   └─ Cálculo de lucro final

5. ENTREGA
   └─ Status: "Entregue"
   └─ Data entrega real: NOW()
   └─ Pedido finalizado
   └─ Disponível em relatórios
```

---

## 📈 Performance & Otimizações

### Índices do Banco
```sql
-- Melhor performance em queries
CREATE INDEX idx_produto_categoria ON produtos(categoria_id);
CREATE INDEX idx_estoque_tipo ON materias_primas(tipo);
CREATE INDEX idx_pedido_status ON pedidos(status);
CREATE INDEX idx_pedido_cliente ON pedidos(cliente_id);
CREATE INDEX idx_movimentacao_data ON movimentacao_estoque(data_movimentacao);
```

### Cache
- CSS/JS minificados
- Imagens otimizadas (max 5MB)
- Queries preparadas (reivindicam)

### Conexão
- Persistent connection (via XAMPP)
- Charset UTF-8 em todas as operações
- Connection pooling automático

---

## 🚀 Roadmap Futuro

### v1.1 (Próximo)
- [ ] Sistema multi-usuário com roles
- [ ] Esquecimento de senha com email
- [ ] 2FA (Two-Factor Authentication)
- [ ] Backups automáticos

### v1.2
- [ ] Integração WhatsApp Business API
- [ ] Geração de PDF de pedidos
- [ ] Relatórios mais avançados com gráficos
- [ ] Email automático ao cliente

### v2.0
- [ ] Aplicativo mobile (React Native)
- [ ] Integração com plataforma de vendas
- [ ] Múltiplos ateliês/franchises
- [ ] Sistema de avaliação de clientes

---

**Documentação da Arquitetura**  
Data: 11 de abril de 2026  
Versão: 1.0.0  
Criado para: Costura Ateliê
