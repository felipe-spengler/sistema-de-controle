# 💼 Sistema de Gestão de Vendas de Sistemas

Sistema completo para administração de vendas de software com painel administrativo e painel de vendedores.

![Status](https://img.shields.io/badge/Status-Em%20Desenvolvimento-yellow)
![PHP](https://img.shields.io/badge/PHP-8.0+-blue)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange)

---

## 🎯 Funcionalidades

### 👨‍💼 Painel Administrativo
- ✅ Dashboard com visão geral completa
- ✅ Gerenciar vendedores
- ✅ Visualizar todos os clientes
- ✅ Visualizar todas as faturas
- ✅ Aprovar saques
- ✅ Relatórios financeiros

### 👨‍💻 Painel do Vendedor
- ✅ Dashboard personalizado
- ✅ Gerenciar clientes próprios
- ✅ Criar e acompanhar faturas
- ✅ Solicitar saques
- ✅ Visualizar assinaturas ativas
- ✅ Extrato financeiro
- ✅ Gerenciar dados bancários

### 💰 Integração Financeira
- ✅ Integração com **Asaas** (Pagamentos e Transferências)
- ✅ Geração automática de boletos
- ✅ PIX
- ✅ Saques automáticos para conta bancária
- ✅ Webhooks para atualização automática

---

## 🚀 Instalação (Docker)
> **Recomendado para Produção e Desenvolvimento**

### Pré-requisitos
- Docker e Docker Compose instalados

### Passo a Passo

1. **Clone o repositório**
2. **Configure as variáveis de ambiente**
   - Copie o `.env.example` para `.env`
   - Preencha as credenciais do Banco e Asaas

3. **Inicie os Containers**
```bash
docker-compose up -d --build
```

4. **Acesse o Sistema**
   - **Sistema Principal:** `http://localhost:3051`
   - **Login Admin:** `admin@sistema.com` / `admin123`
   - **WhatsApp (Waha):** `http://localhost:3050`

---

## 🤖 Automação e Cobrança

O sistema possui um **agendador interno** que roda automaticamente todos os dias às 09:00.

### 🔄 fluxo de Verificação
1. **Verificação de Conexão:** Checa se o WhatsApp (Waha) está conectado.
2. **Busca de Faturas:**
   - Vencendo em 5 dias (Aviso prévio)
   - Vencendo Hoje (Cobrança)
   - Atrasadas > 1 dia e < 30 dias (Cobrança diária)
3. **Envio de Mensagem:** Se conectado, envia a mensagem personalizada via WhatsApp.
4. **Log Detalhado:** Todos os envios (sucesso ou falha) são gravados em `logs_cobrancas` e visíveis no admin.

### 📱 Mensagens Personalizáveis
Você pode editar os templates das mensagens diretamente no painel em **Configurações**.
Variáveis disponíveis: `{cliente}`, `{valor}`, `{vencimento}`, `{link_pagamento}`.


---

## 📁 Estrutura do Projeto

```
projeto-assis/
├── assets/
│   ├── css/
│   │   ├── variables.css    # Variáveis CSS (cores, espaçamentos)
│   │   └── style.css         # Estilos principais
│   ├── js/
│   └── img/
├── config/
│   └── db.php                # Configuração do banco de dados
├── includes/
│   ├── auth.php              # Autenticação e proteção de rotas
│   ├── asaas.php             # Classe de integração Asaas
│   └── sidebar.php           # Menu lateral
├── pages/
│   ├── dashboard.php         # Dashboard principal
│   ├── clients.php           # Gestão de clientes
│   ├── invoices.php          # Gestão de faturas
│   ├── sellers.php           # Gestão de vendedores (admin)
│   ├── withdrawals.php       # Saques
│   ├── subscriptions.php     # Assinaturas ativas
│   ├── statement.php         # Extrato financeiro
│   └── my_account.php        # Configurações da conta
├── index.php                 # Página de login
├── logout.php                # Logout
├── setup.php                 # Instalação inicial
├── update_database.php       # Atualização de tabelas
├── README.md                 # Este arquivo
├── PLANO_FINALIZACAO.md      # Plano de desenvolvimento
└── INTEGRACAO_ASAAS.md       # Documentação Asaas
```

---

## 🗄️ Banco de Dados

### Tabelas Principais

#### `users`
Armazena usuários (admin e vendedores)
- Dados pessoais
- Dados bancários
- Taxa de comissão

#### `clients`
Clientes cadastrados pelos vendedores
- Informações da empresa
- Software contratado
- Plano e mensalidade

#### `invoices`
Faturas geradas
- Vinculada a cliente
- Status de pagamento
- Integração com Asaas

#### `withdrawals`
Solicitações de saque
- Vinculada a vendedor
- Status de aprovação
- ID da transferência Asaas

#### `logs_cobrancas`
Histórico de automação
- Registro de envios de mensagens
- Status (enviado, erro, não_conectado)
- Data/Hora da verificação


---

## 🎨 Design

O sistema utiliza um design **premium e moderno** com:
- ✨ Paleta de cores profissional (azul royal + cinza)
- 🎯 Interface limpa e intuitiva
- 📱 Layout responsivo
- 🌈 Badges coloridos para status
- 💫 Animações suaves

### Cores Principais
- **Primary:** `#2563eb` (Azul Royal)
- **Success:** `#10b981` (Verde)
- **Warning:** `#f59e0b` (Laranja)
- **Danger:** `#ef4444` (Vermelho)

---

## 🔐 Segurança

- ✅ Senhas criptografadas com `password_hash()`
- ✅ Proteção contra SQL Injection (PDO Prepared Statements)
- ✅ Validação de sessão em todas as páginas
- ✅ Controle de permissões (Admin vs Vendedor)
- ✅ Sanitização de inputs

---

## 🔌 Integração Asaas

### Configuração

1. Obtenha sua chave de API no [Asaas](https://www.asaas.com/)
2. Edite `includes/asaas.php`:
   ```php
   $ASAAS_API_KEY = 'SUA_CHAVE_AQUI';
   $ASAAS_ENV = 'production'; // ou 'sandbox' para testes
   ```

### Funcionalidades
- 📄 Criar cobranças (Boleto/PIX)
- 💸 Processar saques
- 👤 Gerenciar clientes
- 💰 Consultar saldo

**Documentação completa:** `INTEGRACAO_ASAAS.md`

---

## 📊 Funcionalidades por Página

### Dashboard
- Total de clientes
- Assinaturas ativas
- Faturas pendentes
- Receita mensal
- Gráfico de vendas (em desenvolvimento)

### Clientes
- Listar clientes
- Adicionar novo cliente
- Editar cliente
- Excluir cliente
- Filtrar por status

### Faturas
- Listar faturas
- Criar nova fatura
- Baixar fatura (marcar como paga)
- Integração com Asaas (em desenvolvimento)

### Saques
- Visualizar saldo disponível
- Solicitar saque
- Histórico de saques
- Integração com Asaas para transferências

### Assinaturas
- Visualizar assinaturas ativas
- Receita mensal recorrente
- Próximas renovações

### Extrato
- Histórico de transações
- Filtros por tipo e período
- Resumo financeiro

### Minha Conta
- Editar dados pessoais
- Configurar dados bancários
- Alterar senha
- Informações da conta

---

## 🛠️ Próximas Implementações

### Sprint Atual
- [ ] Gráficos no dashboard (Chart.js)
- [ ] Sistema de comissões
- [ ] Geração de PDF de faturas
- [ ] Envio de e-mails
- [ ] Relatórios avançados

### Futuro
- [ ] Notificações em tempo real
- [ ] App mobile
- [ ] Multi-idioma
- [ ] Tema escuro

---

## 🐛 Troubleshooting

### Erro de conexão com banco de dados
```
Solução: Verifique se o MySQL está rodando no XAMPP
```

### Página em branco
```
Solução: Ative display_errors no php.ini
```

### Erro 404
```
Solução: Verifique se o projeto está em C:\xampp\htdocs\projeto-assis
```

---

## 📝 Licença

Este projeto é proprietário e confidencial.

---

## 👨‍💻 Suporte

Para dúvidas ou problemas:
- 📧 Email: suporte@sistema.com
- 📱 WhatsApp: (00) 00000-0000

---

## 🎯 Status do Projeto

**Versão Atual:** 1.0 (Beta)  
**Última Atualização:** Dezembro 2025  
**Progresso:** 80% Concluído

### ✅ Concluído
- Sistema de autenticação
- CRUD completo de clientes
- Gestão de faturas
- Sistema de saques
- Integração Asaas
- Design premium

### 🚧 Em Desenvolvimento
- Gráficos e relatórios
- Sistema de comissões
- Webhooks Asaas
- Geração de PDF

---

**Desenvolvido com ❤️ para gestão eficiente de vendas de sistemas**
