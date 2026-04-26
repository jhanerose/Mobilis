# Module Map

## Core Runtime Modules
- `app/bootstrap.php`: loads env, starts session, wires core includes
- `app/config.php`: environment-driven config resolution
- `app/db.php`: PDO connection + `dbConnected()`
- `app/auth.php`: login, session helpers, RBAC guard, password/profile updates
- `app/repository.php`: repository include aggregator

## Repository Modules
- `common.php`: shared fallback/date helpers
- `dashboard.php`: KPI metrics, vehicle status, upcoming bookings
- `customers.php`: customer listing/profile/create/update
- `bookings.php`: booking create/update/actions/cancel
- `vehicles.php`: vehicle CRUD/listing/maintenance options
- `payments.php`: invoice retrieval and payment updates
- `support.php`: contact/reset queue operations and admin response actions
- `tracking.php`: simulated live tracking and role-scoped map payloads
- `analytics.php`: reporting aggregates and recommendations (legacy, superseded by Python service)

## Python Service Modules
- `python-service/app/main.py`: FastAPI application entry point
- `python-service/app/config.py`: Configuration management
- `python-service/app/services/php_api_client.py`: HTTP client for PHP APIs
- `python-service/app/services/analytics_engine.py`: Data processing and analytics logic
- `python-service/app/services/export_generator.py`: Export generation (CSV, Excel, PDF)
- `python-service/app/api/dashboard.py`: Dashboard metrics endpoints
- `python-service/app/api/analytics.py`: Analytics endpoints
- `python-service/app/api/exports.py`: Export endpoints
- `python-service/app/models/analytics.py`: Pydantic models for analytics data
- `python-service/app/models/exports.py`: Pydantic models for export requests

## UI/Pages by Audience
- Public auth/support pages: login/register/forgot-password/contact-admin
- Customer pages: dashboard/bookings/vehicles/tracking/payments
- Staff pages: dashboard/bookings/customers/vehicles/maintenance/tracking/payments/reports
- Admin pages: settings/support inbox

## Shared Frontend
- `public/assets/styles.css`: global styling
- `public/assets/app.js`: insight refresh, modal system, form confirmations, profile panel utilities

## Data Layer Files
- `mobilis_sql.sql`: canonical schema + seed baseline
- `database/migrations/*.sql`: incremental schema transformations
