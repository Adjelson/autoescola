# AutoEscola Financeiro — MVP v1.0

Sistema SaaS de gestão financeira para escolas de condução.  
**PHP MVC · MySQL · Bootstrap 5 · JavaScript vanilla**

---

## Instalação rápida (XAMPP / localhost)

### 1. Copiar ficheiros
```
C:\xampp\htdocs\autoescola\
```

### 2. Criar base de dados
Abrir **phpMyAdmin** e executar `database.sql` (File → Import).

Ou via terminal:
```bash
mysql -u root -p < database.sql
```

### 3. Configurar ligação à BD
Editar `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           // a sua password MySQL
define('DB_NAME', 'autoescola_financeiro');
```

### 4. Configurar URL base
Editar `config/app.php`:
```php
define('APP_URL', 'http://localhost/autoescola');
```

### 5. Aceder
```
http://localhost/autoescola/
```

---

## Credenciais padrão

| Role | Email | Password |
|---|---|---|
| SuperAdmin | `superadmin@autoescola.pt` | `password` |

> ⚠️ **Altere a password imediatamente** após o primeiro login:  
> Menu utilizador (canto superior direito) → **Meu Perfil** → Alterar Password

Para criar a sua escola clique em **"Registar escola"** na landing page.

---

## Estrutura de pastas

```
autoescola/
├── config/
│   ├── app.php              # Configuração geral + helpers globais
│   └── database.php         # Ligação PDO singleton
│
├── controllers/
│   ├── AuthController.php       # Login, registo, logout
│   ├── DashboardController.php  # Dashboard (suporta superadmin global)
│   ├── AlunosController.php     # CRUD alunos
│   ├── ReceitasController.php   # Registar/listar/eliminar receitas
│   ├── DespesasController.php   # CRUD despesas
│   ├── UtilizadoresController.php
│   ├── PerfilController.php     # Alterar password
│   ├── RelatorioController.php  # Relatório mensal imprimível
│   ├── ExportController.php     # Exportar CSV (alunos/receitas/despesas)
│   └── EscolasController.php    # Gestão de escolas (superadmin)
│
├── middleware/
│   └── auth.php             # requireLogin, requireRole, requireEscolaContext
│
├── models/
│   ├── User.php
│   ├── Escola.php
│   ├── Aluno.php
│   ├── Receita.php
│   └── Despesa.php
│
├── views/
│   ├── layouts/             # header, footer, flash alerts
│   ├── auth/                # login, registo
│   ├── dashboard/           # dashboard principal
│   ├── alunos/              # listagem + formulário
│   ├── receitas/            # listagem + formulário
│   ├── despesas/            # listagem + formulário
│   ├── utilizadores/        # listagem + formulário
│   ├── perfil/              # perfil + alterar password
│   ├── relatorio/           # relatório mensal
│   ├── escolas/             # gestão de escolas (superadmin)
│   └── landing.php          # página pública
│
├── public/
│   ├── css/app.css          # Estilos completos (inclui print/mobile)
│   └── js/app.js            # JavaScript (alerts, confirm, sidebar, cálculo dívida)
│
├── index.php                # Router principal (front controller)
├── database.sql             # Schema + dados iniciais
├── .htaccess                # Apache: proteger pastas sensíveis
├── web.config               # IIS / Dominios.pt
└── README.md
```

---

## Funcionalidades por role

| Funcionalidade | funcionario | admin_escola | superadmin |
|---|:---:|:---:|:---:|
| Dashboard | ✅ | ✅ | ✅ (global) |
| Listar alunos | ✅ | ✅ | ✅* |
| Criar/editar/eliminar alunos | ❌ | ✅ | ✅* |
| Registar receitas | ✅ | ✅ | ✅* |
| Eliminar receitas | ❌ | ✅ | ✅* |
| CRUD despesas | ✅/❌ | ✅ | ✅* |
| Relatório mensal | ❌ | ✅ | ✅* |
| Exportar CSV | ✅ | ✅ | ✅* |
| Gerir utilizadores | ❌ | ✅ | ✅* |
| Alterar password | ✅ | ✅ | ✅ |
| Gestão de escolas | ❌ | ❌ | ✅ |
| Aceder como admin (impersonate) | ❌ | ❌ | ✅ |

*após impersonar uma escola

---

## Segurança implementada

- ✅ Passwords com `password_hash()` — bcrypt, cost 12
- ✅ Prepared statements PDO em **todas** as queries
- ✅ Token CSRF em todos os formulários POST
- ✅ Multi-tenant: `escola_id` verificado em cada operação
- ✅ `session_regenerate_id()` após login
- ✅ `htmlspecialchars()` em todos os outputs (`e()` helper)
- ✅ Headers de segurança (`.htaccess` / `web.config`)
- ✅ Proteção de acesso direto às pastas `config/`, `models/`, `controllers/`, `middleware/`
- ✅ Superadmin não pode aceder a dados de escola sem impersonar
- ✅ Funcionário não pode eliminar dados nem gerir utilizadores

---

## Deploy em produção (IIS / Dominios.pt)

1. Upload via FTP de todos os ficheiros
2. Importar `database.sql` no phpMyAdmin do hosting
3. Editar `config/database.php` com as credenciais do hosting
4. Editar `config/app.php`:
   ```php
   define('APP_URL', 'https://seudominio.pt');
   ```
5. Confirmar que `web.config` está na raiz

---

## Dados de demonstração

O ficheiro `database.sql` contém um bloco comentado com dados de teste.  
Para ativar, descomente o bloco `/* ... */` no final do ficheiro antes de importar.

---

## Requisitos mínimos

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Extensão PDO + PDO_MySQL activa
- Apache com `mod_rewrite` **ou** IIS com URL Rewrite
