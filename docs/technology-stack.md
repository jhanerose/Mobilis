# Technology Stack

## Overview
Mobilis is a hybrid web application with PHP backend, MySQL persistence, and a Python microservice for analytics and data export capabilities.

## Runtime and Languages
- Backend language (primary): PHP 8.x
- Backend language (analytics/exports): Python 3.11+
- Frontend languages: HTML5, CSS3, JavaScript (vanilla)
- Database: MySQL 8.x (InnoDB)

## Backend Stack (PHP)
- PHP built-in server for local development (`php -S`)
- Native PHP sessions for authentication state
- PDO for MySQL access with prepared statements
- Function-based repository layer in `app/repositories/`
- Lightweight custom view/layout system in `app/view.php` + `app/views/layouts/`

## Backend Stack (Python)
- FastAPI framework for analytics and export microservice
- httpx for async HTTP client (calls PHP APIs)
- pandas for data processing and analytics
- openpyxl/xlsxwriter for Excel (.xlsx) export generation
- reportlab for PDF export generation
- Pydantic for data validation and settings management
- Uvicorn ASGI server for production deployment

## Frontend Stack
- Server-rendered PHP pages under `public/`
- Shared stylesheet: `public/assets/styles.css`
- Shared interactive logic: `public/assets/app.js`
- Chart rendering in reports pages via client-side script (Chart.js)
- Optional Python service integration for enhanced analytics and exports

## Database Stack
- Single schema SQL bootstrap: `mobilis_sql.sql`
- Additional migration scripts: `database/migrations/`
- Core engine: InnoDB with foreign keys and indexes
- Views used for reporting/support summary:
  - `vw_active_rentals`
  - `vw_monthly_revenue`
  - `vw_support_inbox_summary`

## Environment and Configuration

### PHP Configuration
Configuration is loaded in this order:
1. Process environment variables
2. Optional project `.env` file (loaded by `loadProjectEnv()`)
3. App defaults in `app/config.php`

Primary variables:
- `MOBILIS_DB_HOST`, `MOBILIS_DB_PORT`, `MOBILIS_DB_NAME`, `MOBILIS_DB_USER`, `MOBILIS_DB_PASS`
- Railway-compatible fallbacks: `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER`, `MYSQLPASSWORD`
- `PYTHON_SERVICE_URL`: URL of Python analytics service (default: `http://localhost:8001`)

### Python Service Configuration
Configuration is loaded from:
1. Process environment variables
2. Optional `python-service/.env` file (loaded by python-dotenv)

Primary variables:
- `PHP_API_BASE_URL`: URL of PHP backend APIs (default: `http://localhost:8000`)
- `PYTHON_SERVICE_PORT`: Port for Python service (default: `8001`)
- `PYTHON_SERVICE_HOST`: Host for Python service (default: `0.0.0.0`)
- `CORS_ORIGINS`: Comma-separated list of allowed CORS origins (default: `http://localhost:8000`)

## Security/Access Model
- Session-based auth in `app/auth.php`
- Roles: `admin`, `staff`, `customer`
- Route gating via `requireAuth([...])`
- Password hashing with bcrypt (`password_hash`, `password_verify`)

## Deployment Surface
- Local (PHP): PHP built-in server (`php -S localhost:8000 -t public`)
- Local (Python): Uvicorn server (`python -m app.main` or `uvicorn app.main:app`)
- Containerized: `Dockerfile` (PHP) and `python-service/Dockerfile` (Python)
- Cloud target: Railway (PHP service with optional Python service)

## Notes for Developers
- The codebase is mostly function-oriented (not class-based MVC).
- Many repositories include fallback demo arrays when DB is unavailable.
- Dynamic schema guards exist for some columns (for example payment method and support response columns), enabling forward compatibility when older schemas are present.
- The Python service is optional - the PHP frontend will fall back to PHP functions if the service is unavailable.
- Python service consumes data from existing PHP APIs rather than connecting directly to the database.
- See [python-integration.md](python-integration.md) for detailed Python service documentation.
