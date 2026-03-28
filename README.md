# Sistema Escola — README técnico

## Visão geral
Esta aplicação é um sistema web em **PHP + MySQL** para apoio administrativo escolar e financeiro. Pelo código analisado, o sistema cobre estas áreas:

- autenticação de utilizadores
- dashboard administrativo
- gestão de utilizadores
- gestão de alunos
- receitas e despesas
- transações financeiras
- salários e dívidas
- pedidos de exame
- pedidos de licença
- geração de PDFs

A aplicação está organizada de forma simples, com páginas PHP em `views/`, conexão PDO em `config/db.php`, includes reutilizáveis em `includes/` e um endpoint AJAX em `ajax/`.

---

## Stack identificada

- **Backend:** PHP puro
- **Base de dados:** MySQL / MariaDB
- **Acesso à BD:** PDO
- **Frontend:** Bootstrap, Font Awesome, Chart.js
- **PDF:** mPDF e trechos de código preparados para Dompdf
- **Sessão/Auth:** `$_SESSION`

---

## Estrutura do projeto

```text
escola/
├── ajax/
│   └── busca_aluno.php
├── assets/
│   ├── css/
│   ├── img/
│   ├── js/
│   └── logo.png
├── config/
│   └── db.php
├── controllers/
│   └── auth.php
├── includes/
│   ├── auth_check.php
│   ├── footer.php
│   └── header.php
├── views/
│   ├── login.php
│   ├── dashboard.php
│   ├── users.php
│   ├── alunos.php
│   ├── receitas.php
│   ├── despesas.php
│   ├── transacoes.php
│   ├── salarios.php
│   ├── pedido_exame.php
│   ├── pedido_licenca.php
│   ├── pdf_*.php
│   └── ...
├── composer.json
├── composer.lock
├── data.sql
├── index.php
└── logout.php
```

---

## Requisitos

- PHP 8.1+ recomendado
- MySQL 8+ ou MariaDB 10.4+
- Extensões PHP comuns:
  - `pdo`
  - `pdo_mysql`
  - `mbstring`
  - `gd`
  - `xml`
  - `curl`
- Composer
- Servidor Apache ou Nginx com PHP-FPM

---

## Instalação local

### 1. Copiar o projeto
Coloque os ficheiros dentro da pasta pública do seu servidor local.

Exemplo:

```bash
/var/www/html/escola
```

### 2. Criar a base de dados
No estado atual do projeto, o ficheiro `data.sql` está **incompleto** para a aplicação inteira. Mesmo assim, pode importar o que existe:

```bash
mysql -u root -p < data.sql
```

### 3. Ajustar credenciais da base de dados
Edite `config/db.php`:

```php
$host = 'localhost';
$db   = 'gestao_financeira';
$user = 'root';
$pass = '';
```

### 4. Instalar dependências PHP
Na raiz do projeto:

```bash
composer install
```

### 5. Garantir permissões
Se usar Linux:

```bash
chmod -R 755 .
```

### 6. Abrir no navegador
Aceda a:

```text
http://localhost/escola/
```

---

## Fluxo de acesso

- `index.php` redireciona para:
  - `views/dashboard.php` se existir sessão
  - `views/login.php` se não existir sessão
- o login usa a tabela `users`
- utilizadores inativos não entram
- após login, o redirecionamento vai para `receitas.php`

---

## Módulos encontrados

### 1. Autenticação
- login com email e senha
- CAPTCHA após múltiplas tentativas
- bloqueio progressivo por tentativas falhadas
- logout via `logout.php`

### 2. Dashboard
- totais de receitas e despesas
- lucro
- total de utilizadores
- alunos em curso/finalizados
- gráficos por mês e por dia

### 3. Utilizadores
- criar
- editar
- ativar/inativar
- acesso restrito para admin

### 4. Alunos
- cadastro
- edição
- eliminação
- filtro por nome, categoria e status

### 5. Financeiro
- receitas
- despesas
- transações
- salários
- dívidas

### 6. Documentos e pedidos
- pedido de exame
- edição/eliminação de pedidos
- pedido de licença
- geração de PDFs

---

## Problemas e erros encontrados

Abaixo estão os principais problemas reais observados no código.

### Críticos

#### 1. `data.sql` não representa a aplicação completa
O ficheiro cria apenas:

- `transacoes`
- `users`

Mas o sistema usa também estas tabelas:

- `alunos`
- `receitas`
- `despesas`
- `salarios`
- `dividas`
- `pedidos_exame`
- `pedidos_licenca`
- `faturas_salarios`

Sem essas tabelas, várias páginas vão falhar imediatamente.

#### 2. Ordem incorreta de criação das tabelas em `data.sql`
`transacoes` é criada antes de `users`, mas tem chave estrangeira para `users(id)`.

Isso pode falhar na importação dependendo do motor/configuração.

#### 3. Incompatibilidade entre schema e código na coluna `users.tipo`
No `data.sql`, `users.tipo` é:

```sql
ENUM('admin', 'comum')
```

Mas no sistema, o código trabalha com estes valores:

- `admin`
- `secretario`
- `prof_pratica`
- `prof_teorica`

Resultado: criação/edição de utilizadores pode quebrar ou gravar valores inválidos.

#### 4. Dependências PDF inconsistentes
`composer.json` instala apenas:

```json
"mpdf/mpdf": "8.2"
```

Mas há ficheiros a usar **Dompdf**:

- `views/gerar_tabela_exame.php`
- `views/relatorio_transacoes.php`

Sem instalar `dompdf/dompdf`, essas páginas vão falhar.

#### 5. Pasta `vendor/` não foi incluída no zip
Os ficheiros PDF fazem:

```php
require '../vendor/autoload.php';
```

Mas o zip analisado não traz a pasta `vendor/`. Sem `composer install`, os PDFs não funcionam.

#### 6. Eliminação sem autenticação em `views/eliminar_pedido.php`
Esse ficheiro faz delete direto com `$_GET['id']`, mas **não inclui** `auth_check.php`.

Isso é uma falha de segurança séria.

---

### Altos

#### 7. Operações destrutivas por `GET`
Várias páginas usam links `?delete=` ou `?excluir=` para apagar/inativar dados.

Exemplos:
- alunos
- despesas
- receitas
- utilizadores
- pedidos

O ideal é usar `POST` com proteção CSRF.

#### 8. Ausência de proteção CSRF
Os formulários e ações sensíveis não têm token CSRF.

Isso deixa o sistema vulnerável a requisições forjadas.

#### 9. O projeto não está realmente em MVC
Apesar de existir pasta `controllers/`, a regra de negócio está quase toda dentro de `views/*.php`.

Consequências:
- manutenção difícil
- código duplicado
- mistura de HTML com SQL e validação
- pouca testabilidade

#### 10. `controllers/auth.php` está vazio
A pasta sugere arquitetura mais organizada, mas o ficheiro de autenticação não tem implementação.

#### 11. Referência a ficheiro inexistente
`includes/header.php` e `views/login.php` carregam:

```html
../assets/css/bootstrap-icons.css
```

Esse ficheiro **não existe** no zip.

Os ícones `bi bi-*` podem não aparecer.

#### 12. URL fixa errada no relatório de transações
Em `views/relatorio_transacoes.php` existe:

```html
<img src="http://localhost/nova/assets/logo.png" alt="">
```

Isso quebra fora desse ambiente específico.

---

### Médios

#### 13. Sessão com tempo definido, mas sem expiração efetiva
Em `views/login.php` existe:

```php
define('TEMPO_SESSAO', 15 * 60);
```

Mas não encontrei lógica consistente a destruir a sessão ao expirar esse tempo.

#### 14. Senha inicial do admin está apenas como hash
O seed cria um admin com hash pronto, mas o README original não informa a senha real.

Para implantação, é melhor criar um script claro de bootstrap ou reset de senha.

#### 15. Redirecionamento pós-login inconsistente
`index.php` manda utilizadores autenticados para `dashboard.php`, mas `login.php` redireciona para `receitas.php`.

Convém padronizar.

#### 16. Duplicação de scripts JS
O footer carrega `bootstrap.bundle.min.js` e também `bootstrap.js`.

Isso pode causar redundância e comportamentos inesperados.

#### 17. `dashboard.php` inclui Chart.js e o footer também inclui JS globais
A página já carrega Chart.js diretamente e o footer carrega outros scripts sempre. Isso não é fatal, mas aumenta acoplamento.

#### 18. Permissões incompletas em alguns módulos
Há páginas com proteção admin explícita e outras apenas com login genérico. Convém definir claramente quem pode:

- criar utilizadores
- apagar dados
- gerar PDFs
- editar pedidos
- lançar receitas/despesas

---

## Melhorias recomendadas

### Prioridade 1
- corrigir o schema completo da base de dados
- alinhar `users.tipo` com os perfis usados no código
- proteger todas as rotas de eliminação/edição
- trocar ações destrutivas para `POST`
- adicionar CSRF
- instalar e padronizar a biblioteca de PDF

### Prioridade 2
- separar regras de negócio das views
- criar controllers reais
- centralizar validações
- criar funções auxiliares reutilizáveis

### Prioridade 3
- criar `.env` para credenciais
- padronizar mensagens de erro
- melhorar logs
- criar migrações SQL por módulo
- adicionar README de instalação real

---

## Estado atual do projeto

### O que parece funcional
- autenticação básica
- estrutura visual geral
- CRUDs simples em várias páginas
- geração de alguns PDFs via mPDF

### O que precisa de revisão antes de produção
- base de dados
- segurança
- permissões
- padronização da arquitetura
- dependências PDF
- ficheiros estáticos ausentes

---

## Recomendação prática
Antes de publicar ou continuar o desenvolvimento, o ideal é fazer nesta ordem:

1. montar um `schema.sql` completo
2. corrigir perfis de utilizador
3. rever autenticação e autorização
4. corrigir rotas de eliminação
5. padronizar geração de PDF
6. reorganizar o projeto para MVC real

---

## Observação final
O sistema tem boa base funcional para um projeto administrativo escolar, mas ainda está com sinais de crescimento rápido sem consolidação estrutural. A principal urgência é **fechar falhas de segurança** e **alinhar a base de dados com o código**.
