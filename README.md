# Mini Payment Dashboard

Прототип дашборда учёта оплат, проектов и закрывающих актов для digital-агентства.

## Стек

- **Backend:** Laravel 12 (REST API, бизнес-логика, SQLite)
- **Frontend:** Vue 3 + TypeScript + Pinia + Vite
- **Деплой:** один Docker-контейнер (Laravel + собранный SPA)

## Быстрый старт (Docker)

```bash
docker compose up --build
```

Откройте http://localhost:8080

## Локальная разработка

### Backend

```bash
cd backend
cp .env.example .env
# Убедитесь: DB_CONNECTION=sqlite, DASHBOARD_REFERENCE_DATE=2026-08-14
touch database/database.sqlite
docker run --rm -v "%cd%:/app" -w /app php:8.4-cli-alpine sh -c "apk add sqlite-dev && docker-php-ext-install pdo pdo_sqlite && php artisan key:generate && php artisan migrate:fresh --seed && php artisan serve --host=0.0.0.0 --port=8000"
```

### Frontend

```bash
cd frontend
npm install
npm run dev
```

Vite проксирует `/api` на `http://localhost:8000`.

### Сборка SPA в Laravel

```bash
cd frontend
npm run build
```

Статика попадает в `backend/public/spa/`. Laravel отдаёт `index.html` для всех не-API маршрутов.

## API

Базовый префикс: `/api/v1`

| Метод | Endpoint | Описание |
|-------|----------|----------|
| GET | `/dashboard/summary` | KPI-сводка |
| GET | `/filters` | Списки для фильтров |
| GET | `/projects` | Агрегаты по проектам |
| GET | `/payments` | Список оплат |
| PATCH | `/payments/{id}/act` | Обновить статус акта |
| PATCH | `/payments/bulk-act` | Массовое обновление |

Фильтры: `search`, `project_id`, `legal_entity_id`, `service_stage`, `date_from`, `date_to`, `act_status`, `is_sent`, `is_signed`.

## Тесты

```bash
docker run --rm -v ./backend:/app -w /app php:8.4-cli-alpine sh -c "apk add sqlite-dev && docker-php-ext-install pdo pdo_sqlite && php artisan test"
```

## Данные

Seed из `docs/project_payments_acts_dashboard(1).html` → `backend/database/data/payments.json` (24 оплаты, 19 юрлиц).

Подробнее: [ARCHITECTURE.md](ARCHITECTURE.md)
