# 📡 API Documentation - Costura Ateliê

## Visão Geral

A API do sistema Costura Ateliê fornece endpoints JSON para operações com produtos, estoque e pedidos.

**Base URL:** `http://localhost/costura/admin/api/`

**Autenticação:** Requer sessão autenticada (via login web)

**Content-Type:** `application/json`

---

## 📦 Produtos API

### **GET /produtos.php?acao=listar**

Retorna lista de todos os produtos.

**Parâmetros:**
```
?acao=listar
?categoria=ID (opcional)
```

**Response:**
```json
{
  "sucesso": true,
  "dados": [
    {
      "id": 1,
      "nome": "Ninho Redutor Clássico",
      "descricao": "...",
      "categoria_id": 1,
      "categoria_nome": "Ninhos Redutores",
      "preco_venda": 150.00,
      "custo_producao": 60.00,
      "material": "...",
      "foto_url": "prod_123.jpg",
      "ativo": 1
    }
  ]
}
```

---

### **GET /produtos.php?acao=obter&id=1**

Retorna detalhes de um produto específico.

**Parâmetros:**
```
?acao=obter
&id=ID (obrigatório)
```

**Response:**
```json
{
  "sucesso": true,
  "dados": {
    "id": 1,
    "nome": "Ninho Redutor Clássico",
    ...
  }
}
```

---

### **POST /produtos.php?acao=deletar**

Deleta um produto (marca como inativo).

**Body:**
```json
{
  "id": 1
}
```

**Response:**
```json
{
  "sucesso": true,
  "mensagem": "Produto deletado com sucesso"
}
```

---

## 🏭 Estoque API

### **GET /estoque.php?acao=listar**

Retorna lista de matérias-primas.

**Response:**
```json
{
  "sucesso": true,
  "dados": [
    {
      "id": 1,
      "nome": "Tecido Algodão 100%",
      "tipo": "Tecido",
      "unidade": "metro",
      "preco_unitario": 25.00,
      "quantidade_estoque": 50,
      "quantidade_minima": 10
    }
  ]
}
```

---

### **GET /estoque.php?acao=alertas**

Retorna itens com estoque baixo.

**Response:**
```json
{
  "sucesso": true,
  "dados": [
    {
      "id": 1,
      "nome": "Tecido Algodão",
      "quantidade_estoque": 5,
      "quantidade_minima": 10
    }
  ]
}
```

---

### **POST /estoque.php?acao=registrar**

Registra movimento de estoque.

**Body:**
```json
{
  "materia_prima_id": 1,
  "tipo": "entrada",
  "quantidade": 10,
  "descricao": "Compra fornecedor A"
}
```

**Response:**
```json
{
  "sucesso": true,
  "mensagem": "Movimentação registrada com sucesso"
}
```

---

## 🛒 Pedidos API

### **GET /pedidos.php?acao=listar**

Retorna lista de pedidos.

**Parâmetros:**
```
?acao=listar
?status=pendente (opcional: pendente, em_producao, pronto, entregue)
```

**Response:**
```json
{
  "sucesso": true,
  "dados": [
    {
      "id": 1,
      "cliente_id": 1,
      "cliente_nome": "Maria Silva",
      "data_pedido": "2026-04-11 10:30:00",
      "data_entrega_prevista": "2026-04-20",
      "status": "em_producao",
      "valor_total": 180.00,
      "quantidade_itens": 1
    }
  ]
}
```

---

### **GET /pedidos.php?acao=obter&id=1**

Retorna detalhes de um pedido.

**Response:**
```json
{
  "sucesso": true,
  "dados": {
    "id": 1,
    "cliente_nome": "Maria Silva",
    "email": "maria@email.com",
    "telefone": "(11) 98765-4321",
    "data_pedido": "2026-04-11 10:30:00",
    "data_entrega_prevista": "2026-04-20",
    "status": "em_producao",
    "valor_total": 180.00
  }
}
```

---

### **POST /pedidos.php?acao=atualizar_status**

Atualiza status do pedido.

**Body:**
```json
{
  "pedido_id": 1,
  "status": "pronto"
}
```

**Valores válidos para status:**
- `pendente`
- `em_producao`
- `pronto`
- `entregue`
- `cancelado`

**Response:**
```json
{
  "sucesso": true,
  "mensagem": "Status atualizado com sucesso"
}
```

---

### **GET /pedidos.php?acao=lucro&id=1**

Retorna análise de lucro do pedido.

**Response:**
```json
{
  "sucesso": true,
  "dados": {
    "valor_venda": 180.00,
    "custo": 75.00,
    "lucro": 105.00,
    "percentual_lucro": 58.33
  }
}
```

---

## 🔄 Uso com JavaScript

### **Exemplo 1: Listar Produtos**

```javascript
fetch('admin/api/produtos.php?acao=listar')
  .then(res => res.json())
  .then(data => {
    if (data.sucesso) {
      console.log(data.dados);
    }
  });
```

### **Exemplo 2: Atualizar Status de Pedido**

```javascript
fetch('admin/api/pedidos.php?acao=atualizar_status', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    pedido_id: 1,
    status: 'pronto'
  })
})
.then(res => res.json())
.then(data => {
  if (data.sucesso) {
    console.log('Status atualizado!');
  }
});
```

### **Exemplo 3: Registrar Movimentação**

```javascript
const dados = {
  materia_prima_id: 1,
  tipo: 'entrada',
  quantidade: 10,
  descricao: 'Compra fornecedor'
};

fetch('admin/api/estoque.php?acao=registrar', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded'
  },
  body: new URLSearchParams(dados)
})
.then(res => res.json())
.then(data => console.log(data));
```

---

## 🔐 Segurança

- ✅ Todas as APIs requerem autenticação
- ✅ Entrada validada e sanitizada
- ✅ Respostas em JSON
- ✅ Proteção contra SQL Injection

---

## 📊 Códigos de Resposta

| Status | Significado |
|--------|-------------|
| `sucesso: true` | Operação bem-sucedida |
| `sucesso: false` | Erro na operação |

---

## 🐛 Troubleshooting

### "Sucesso false sem mensagem"
```
Verifique:
1. Se está autenticado (cookie de sessão)
2. Se os parâmetros estão corretos
3. Se o método HTTP é correto (GET/POST)
```

### "Erro 404"
```
Verifique:
1. Se o arquivo de API existe
2. Se o caminho está correto
3. Se não há typos na URL
```

### "Erro de permissão"
```
Provavelmente não autenticado.
Faça login primeiro em /admin/login.php
```

---

## 🚀 Próximas Integrações

- [ ] REST API completa
- [ ] OAuth 2.0
- [ ] GraphQL
- [ ] Webhooks
- [ ] Rate Limiting

---

**Versão da API:** 1.0.0  
**Última Atualização:** 11/04/2026
