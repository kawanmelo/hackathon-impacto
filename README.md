# AurellIA API

Backend Laravel que abastece a plataforma AurellIA com jornadas gamificadas, quizzes personalizados, marketplace solidário e relatórios com IA para professores e gestores.

## ✨ Principais Recursos

- **Quizzes dinâmicos** – professores montam quizzes selecionando perguntas de cada disciplina; alunos respondem e recebem moedas.
- **Marketplace solidário** – catálogo de recompensas gerido pela API, com compras validadas pelo saldo real do estudante.
- **Relatórios inteligentes** – endpoints integrados ao OpenAI Service geram análises por turma ou grupo.
- **Integração multi‐perfil** – rotas para estudantes, professores, gestores e marketplace mantendo os mesmos dados usados no frontend AurellIA.

## 🧱 Stack

- PHP 8.2 + Laravel 12
- PostgreSQL/MySQL (configure via `.env`)
- Vite + npm/yarn para assets (quando necessário)
- Docker/Docker Compose opcionais para desenvolvimento rápido

## ✅ Pré‑requisitos

- PHP 8.2+
- Composer 2.x
- Node.js 18+ e npm (ou yarn) – apenas se for compilar assets com Vite
- Banco de dados compatível (MySQL, MariaDB ou PostgreSQL)

## 🚀 Setup Rápido

```bash
# 1. Instale dependências PHP
composer install

# 2. Copie o .env e configure DB, FRONTEND_URL e chaves da OpenAI
cp .env.example .env
php artisan key:generate

# 3. Rode as migrações (adicione --seed se tiver seeders prontos)
php artisan migrate

# 4. Instale dependências JS (apenas se precisar do build do Vite)
npm install
npm run build   # ou npm run dev para hot reload

# 5. Suba o servidor
php artisan serve
```

### Usando Docker

```bash
docker compose up --build
```

O arquivo `docker-compose.yml` já sobe PHP, banco e nginx conforme configurado.

## 🔧 Scripts Úteis

| Comando | Descrição |
| --- | --- |
| `composer setup` | Instala dependências, gera `.env`, roda `migrate` e build do Vite |
| `composer dev` | Executa servidor Laravel, fila, logs e Vite em paralelo |
| `composer test` | Limpa cache e roda `php artisan test` |
| `npm run dev` | Servidor Vite com HMR |
| `npm run build` | Build de assets para produção |

## 🌐 Variáveis de Ambiente

| Variável | Descrição |
| --- | --- |
| `APP_URL` | URL pública da API |
| `FRONTEND_URL` | Origem liberada no CORS (ex.: `http://localhost:3000`) |
| `DB_*` | Configurações do banco |
| `OPENAI_API_KEY`/`services.openai.*` | Chaves usadas pelo `OpenAIService` |

## 📡 Endpoints Principais (prefixo `/api/v1`)

| Método / Rota | Descrição |
| --- | --- |
| `GET /disciplines` | Lista disciplinas disponíveis |
| `GET /quizzes` | Lista quizzes (filtro `discipline_id` opcional) |
| `GET /quizzes/{quiz}` | Detalha quiz com perguntas e alternativas |
| `POST /quizzes` | Cria quiz (payload com `discipline_id`, `title`, `questions[]`) |
| `POST /quizzes/submit` | Submissão de respostas do aluno |
| `GET /quizzes/{quiz}/results/{student}` | Resultados de um aluno em um quiz |
| `GET /quizzes/discipline/{discipline}/questions` | Perguntas disponíveis para montar quizzes |
| `POST /quizzes/{group}/generate-group-report` | Estatísticas agregadas da turma |
| `POST /groups/{group}` | Relatório narrativo semanal via IA |
| `GET /products` / `POST /products/buy` | Catálogo e compra do marketplace |
| `GET /students/{student}` | Perfil e saldo de moedas do estudante |

Todas as rotas estão registradas em `routes/api.php` e herdam os cabeçalhos de CORS do middleware `App\Http\Middleware\CorsMiddleware`.

## 📂 Estrutura de Pastas

- `app/` – controllers, services (OpenAI, QuizService), models e middleware
- `database/migrations` – schema para quizzes, perguntas, produtos, métricas etc.
- `routes/api.php` – todas as rotas REST expostas para o frontend AurellIA
- `config/prompts.php` – templates usados pelos relatórios com IA

## 🧪 Testes

```bash
php artisan test
# ou
composer test
```

Use `php artisan test --filter=NomeDoTeste` para executar casos específicos.

## 🤝 Contribuição

1. Faça um fork e crie sua branch feature (`git checkout -b feature/nova-funcionalidade`)
2. Garanta que testes e lint passam (`composer test`)
3. Abra um PR descrevendo claramente a mudança

## 📄 Licença

Projeto licenciado sob **MIT**. Veja o arquivo `LICENSE` ou o cabeçalho padrão do Laravel para mais detalhes.
