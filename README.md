# 🧉 Capivarinha Curitibaninha

**Um jogo educativo e divertido para aprender gírias, curitibaneices e expressões típicas de Curitiba!**

---

## 📋 Sobre o Projeto

**Capivarinha Curitibaninha** é uma aplicação web interativa desenvolvida para a disciplina **DS122 (Desenvolvimento Seguro)** que combina:

- 🎮 **Gameplay** de digitação em tempo real
- 🏆 **Sistema de ranking** com ligas competitivas
- 🔐 **Segurança em primeiro lugar** com proteção CSRF, Session Fixation prevention e validação de entrada
- 📱 **Design responsivo** com tema visual baseado em Curitiba
- 🗄️ **Banco de dados** com suporte a MySQL e fallback SQLite

### Tema

O jogo trabalha com **gírias e expressões curitibanas**, oferecendo:

- **Palavras-chave regionais**: Pinhão, Bah, Piá, Raiz, Xis, Cauim
- **Nomes locais**: Parque Barigui, Rua XV, Calçadão
- **Personagem mascote**: Uma Capivara animada que reage em tempo real

---

## 🚀 Começar

### Requisitos

- **PHP** 8.0+ (Testado em PHP 8.0.30)
- **MySQL** 8.0+ (opcional, usa SQLite como fallback)
- **Navegador moderno** (Chrome, Firefox, Safari, Edge)

### Instalação Rápida (Desenvolvimento)

#### Opção 1: Com MySQL

```bash
# Clone o repositório
git clone https://github.com/laramaoly/Capivarinha_Curitibaninha.git
cd Capivarinha_Curitibaninha

# Execute o script de setup
bash fix_env.sh

# Inicie o servidor
/opt/php/8.0.30/bin/php -S 0.0.0.0:8000
```

**Credenciais padrão do MySQL:**
- Host: `127.0.0.1`
- Usuário: `admin`
- Senha: `admin`
- Banco: `capityper`

#### Opção 2: Com SQLite (Recomendado para desenvolvimento)

```bash
# Clone o repositório
git clone https://github.com/laramaoly/Capivarinha_Curitibaninha.git
cd Capivarinha_Curitibaninha

# Inicialize o banco SQLite
/opt/php/8.0.30/bin/php setup_sqlite.php

# Inicie o servidor
/opt/php/8.0.30/bin/php -S 0.0.0.0:8000
```

### Acessar

Abra no navegador:

```
http://127.0.0.1:8000
```

---

## 🎮 Como Jogar

### Objetivo

Digitar corretamente as palavras curitibanas que aparecem na tela em tempo limitado.

### Regras Básicas

1. **Começar**: Clique em "Iniciar Jogo" na tela inicial
2. **Digitar**: O campo de entrada mostrará feedback em tempo real:
   - 🟢 **Verde**: Primeira letra(s) correta(s)
   - 🔴 **Vermelho**: Erro na digitação
3. **Acertar**: Ao digitar a palavra completa corretamente, passa para a próxima
4. **Vidas**: Você tem 3 vidas. A cada erro, perde uma
5. **Tempo**: 100 segundos por rodada (indicado pela barra de progresso)

### Pontuação

- ✅ Acerto: +10 pontos
- ❌ Erro: -5 pontos
- ⏱️ Bônus de tempo: +2 pontos por segundo restante

### Ligas

Crie uma liga para competir com amigos:

1. Vá para **"Minhas Ligas"**
2. Clique em **"Criar Nova Liga"**
3. Defina um nome e uma senha (para amigos entrarem)
4. Compartilhe a senha com seus amigos
5. Compete no ranking exclusivo da liga

---

## 🔐 Segurança (Implementações)

### Proteção contra Vulnerabilidades

#### 1. **Prevenção de SQL Injection**
- ✅ Uso de **Prepared Statements** em todas as queries
- ✅ PDO com `ATTR_EMULATE_PREPARES = false`

```php
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->execute([$email]); // Email não é interpretado como SQL
```

#### 2. **Proteção CSRF (Cross-Site Request Forgery)**
- ✅ Tokens únicos por sessão em `includes/csrf.php`
- ✅ Validação obrigatória em todos os formulários POST
- ✅ Token regenerado a cada requisição

```html
<form method="POST">
    <?php echo csrfInput(); ?>
    <!-- Restante do formulário -->
</form>
```

#### 3. **Prevenção de Session Fixation**
- ✅ `session_regenerate_id(true)` após login bem-sucedido
- ✅ `session.cookie_httponly = 1` (JavaScript não acessa cookies)
- ✅ `session.cookie_samesite = 'Strict'` (Mesmo site apenas)

#### 4. **Hash de Senhas**
- ✅ `password_hash()` com algoritmo `PASSWORD_DEFAULT` (bcrypt)
- ✅ `password_verify()` para validação

#### 5. **Tratamento de Erros Seguro**
- ✅ Detalhes técnicos **logados em arquivo**, não exibidos ao usuário
- ✅ Mensagens genéricas ao usuário ("Erro de conexão, tente novamente")
- ✅ Log centralizado em `logs/db_errors.log`

#### 6. **Validação de Entrada**
- ✅ `htmlspecialchars()` em todos os outputs dinâmicos
- ✅ Trimming e sanitização de inputs
- ✅ Validação de emails com filtros PHP

#### 7. **Controle de Acesso**
- ✅ Middleware simples no `index.php` que redireciona não-autenticados
- ✅ Verificação de `$_SESSION['user_id']` antes de operações sensíveis

---

## 📁 Estrutura do Projeto

```
Capivarinha_Curitibaninha/
├── index.php                      # Router principal
├── .env                          # Variáveis de ambiente (dev)
├── .gitignore                    # Arquivos ignorados no git
│
├── config/
│   └── database.php             # Configuração de banco (MySQL + SQLite fallback)
│
├── controllers/
│   ├── AuthController.php       # Login, Registro, Logout
│   ├── LeagueController.php     # Criação e gerenciamento de ligas
│   └── RankingController.php    # Rankings global e por liga
│
├── views/
│   ├── login.php               # Tela de login
│   ├── register.php            # Tela de registro
│   ├── game.php                # Tela principal do jogo
│   ├── ranking.php             # Tela de rankings
│   └── dashboard.php           # Dashboard com ligas
│
├── includes/
│   ├── header.php              # Cabeçalho HTML comum
│   ├── footer.php              # Rodapé HTML comum
│   ├── navbar.php              # Barra de navegação
│   └── csrf.php                # ⭐ Proteção CSRF
│
├── api/
│   ├── get_palavras.php        # API: Busca palavras do jogo
│   └── save_score.php          # API: Salva pontuação do usuário
│
├── assets/
│   ├── css/
│   │   ├── style.css           # Estilos globais
│   │   └── game.css            # Estilos específicos do jogo
│   ├── js/
│   │   ├── main.js             # Lógica geral
│   │   └── game.js             # Lógica do jogo (typing)
│   └── img/
│       ├── char-capivara-*.png  # Mascote (animações)
│       ├── background-*.png     # Fundos temáticos
│       └── icons/               # Ícones diversos
│
├── sql/
│   └── database_setup.sql      # Schema inicial do banco
│
├── data/
│   └── capityper.db            # Banco SQLite (gerado automaticamente)
│
├── logs/
│   └── db_errors.log           # Log de erros do banco
│
└── README.md                    # Este arquivo

```

---

## 📊 Banco de Dados

### Tabelas Principais

#### `usuarios`
| Campo | Tipo | Descrição |
| --- | --- | --- |
| `id` | INT PK | ID único do usuário |
| `nome` | VARCHAR | Nome ou apelido |
| `email` | VARCHAR UNIQUE | Email para login |
| `senha` | VARCHAR | Hash bcrypt da senha |
| `data_cadastro` | DATETIME | Data de registro |

#### `palavras`
| Campo | Tipo | Descrição |
| --- | --- | --- |
| `id` | INT PK | ID único |
| `palavra` | VARCHAR UNIQUE | Gíria ou expressão |
| `dica` | TEXT | Dica/descrição |
| `categoria` | VARCHAR | Categoria (ex: "curitiba") |

#### `scores`
| Campo | Tipo | Descrição |
| --- | --- | --- |
| `id` | INT PK | ID único |
| `usuario_id` | INT FK | ID do jogador |
| `liga_id` | INT FK | ID da liga (nulo = global) |
| `palavras_acertadas` | INT | Quantidade de acertos |
| `palavras_erradas` | INT | Quantidade de erros |
| `tempo_gasto` | INT | Segundos utilizados |
| `data_jogo` | DATETIME | Quando a rodada foi jogada |

#### `ligas` e `liga_membros`
- Ligas podem ser criadas para competições internas
- Cada liga tem uma password para novos membros

---

## 🛠️ Configuração de Ambiente

### Variáveis de Ambiente (`.env`)

Crie um arquivo `.env` na raiz do projeto:

```bash
# MySQL (se usar)
DB_HOST=127.0.0.1
DB_NAME=capityper
DB_USER=admin
DB_PASS=admin

# Banco de dados em produção
# DB_HOST=db.exemplo.com
# DB_USER=usuario_seguro
# DB_PASS=senha_complexa_aleatoria
```

**Nota:** O `.env` está no `.gitignore` para segurança (senhas não vão para o repositório).

---

## 🐛 Troubleshooting

### "Erro ao acessar o banco de dados"

**Verificar:**
1. MySQL está rodando? `sudo service mysql status`
2. Banco foi criado? Executar `bash fix_env.sh`
3. Credenciais corretas em `.env`?
4. Permissões em `data/` e `logs/`? `chmod 755 data logs`

### "Token inválido (CSRF)"

**Motivos comuns:**
- Sessão expirou (logout automático após 24h)
- Formulário foi enviado fora da seção (atualize a página)
- Cookies desabilitados no navegador

**Solução:** Limpe cookies, atualize a página e tente novamente.

### PHP Fatal Error: "could not find driver"

**Solução:**
1. Instale o driver MySQL: `sudo apt-get install php-mysql`
2. Reinicie o servidor PHP
3. Verificar: `php -m | grep pdo`

---

## 🧪 Testes & Validação

### Teste de Segurança CSRF

```bash
# 1. Acesse a página de login
curl http://127.0.0.1:8000/index.php?page=login

# 2. Tente um POST sem o token (deve falhar com 403)
curl -X POST http://127.0.0.1:8000/index.php?page=login \
  -d "email=test@test.com&senha=123456"

# Esperado: "Ação não autorizada (Token inválido)"
```

### Teste de SQL Injection

```bash
# Tente um email com SQL injection (deve retornar "Email ou senha incorretos")
curl -X POST http://127.0.0.1:8000/index.php?page=login \
  -d "email=admin' OR '1'='1&senha=qualquer"

# Esperado: "Email ou senha incorretos" (não expõe erro SQL)
```

---

## 👥 Créditos & Atribuições

### Desenvolvimento
- **Maoly Lara Serrano** - Autora principal
- Disciplina: **DS122 - Desenvolvimento Seguro**
- Professor: **Alex Kutzke**
- Instituição: **UFPR - SEPT**

### Tecnologias

| Tecnologia | Uso | Versão |
| --- | --- | --- |
| **PHP** | Back-end | 8.0.30+ |
| **MySQL** | Banco (opcional) | 8.0+ |
| **SQLite** | Banco (fallback) | 3.x |
| **HTML5** | Front-end | - |
| **CSS3** | Styling | - |
| **JavaScript** | Interatividade | ES6+ |
| **PDO** | Database abstraction | Built-in |

### Bibliotecas & Recursos

- **Ícones**: Font Awesome (CDN)
- **Imagens do Mascote**: Criadas com IA (Generative design)
- **Fontes**: Google Fonts (Poppins, Roboto)
- **Paleta de Cores**: Inspirada em Curitiba 🌲

### Palavras & Gírias

As palavras do banco de dados foram coletadas de:
- Comunidades locais curitibanas
- Literatura e artigos sobre cultura local
- Contribuições da comunidade

---

## 📝 Licença

Este projeto é fornecido para fins educacionais e acadêmicos.

---

## 🤝 Contribuições

Quer ajudar? Envie um Pull Request com:

- ✅ Novas palavras/gírias curitibanas
- ✅ Correções de bugs
- ✅ Melhorias de UX/UI
- ✅ Otimizações de segurança

---

## 📧 Contato & Suporte

Para dúvidas ou sugestões:

- 📍 **GitHub**: [laramaoly/Capivarinha_Curitibaninha](https://github.com/laramaoly/Capivarinha_Curitibaninha)
- 💬 **Issues**: [Abra uma issue](https://github.com/laramaoly/Capivarinha_Curitibaninha/issues)

---

**Boa sorte no jogo! 🧉🎮**

*"Bah, piá! Que legal jogar com a galera curitibana!"*
Feito com 💚, 🧉 e código.