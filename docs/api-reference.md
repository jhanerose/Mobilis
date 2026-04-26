# API and Endpoint Reference

## PHP JSON APIs

### GET /api/dashboard.php
- Auth: required (`admin|staff|customer`)
- Response:
  - `ok`: boolean
  - `source`: `mysql` or `fallback`
  - `payload`:
    - `generated_at`
    - `metrics` (fleet, rentals, revenue, utilization)
    - `vehicles` list
    - `bookings` list
    - `maintenance` list
  - `insights`: analytics insights output

### GET /api/tracking.php
- Auth: required (`admin|staff|customer`)
- Response:
  - `ok`, `source`, `role`
  - `generated_at`, `step_seconds`
  - `center` (`lat`, `lng`)
  - `vehicles[]`: `vehicle_id`, `name`, `plate`, `status`, `lat`, `lng`, `updated_at`

## Python Analytics Service APIs

**Base URL**: `http://localhost:8001` (configurable via `PYTHON_SERVICE_URL`)

**Note**: The Python service is optional. If unavailable, the PHP frontend falls back to PHP functions.

### Health Check
- `GET /health`
- Auth: none
- Response: `{"status": "healthy"}`

### Dashboard Metrics
- `GET /api/dashboard/metrics`
- Auth: none (relies on PHP auth)
- Response:
  - `total_fleet`: integer
  - `active_rentals`: integer
  - `revenue_today`: float
  - `utilization_rate`: integer

### Analytics Endpoints

#### Revenue Trends
- `GET /api/analytics/revenue-trends?period=month`
- Parameters:
  - `period`: `week`, `month`, or `year` (default: `month`)
- Response: Array of objects with `date` and `total` fields

#### Booking Trends
- `GET /api/analytics/booking-trends?period=month`
- Parameters:
  - `period`: `week`, `month`, or `year` (default: `month`)
- Response: Array of objects with `date`, `count`, and `revenue` fields

#### Top Customers
- `GET /api/analytics/top-customers?limit=10`
- Parameters:
  - `limit`: integer (default: 10)
- Response: Array of objects with `name`, `email`, `bookings`, and `total_revenue` fields

#### Vehicle Performance
- `GET /api/analytics/vehicle-performance`
- Response: Array of objects with `name`, `type`, `rentals`, and `revenue` fields

#### Payment Status Breakdown
- `GET /api/analytics/payment-status-breakdown`
- Response: Array of objects with `status`, `count`, and `total` fields

#### Booking Status Breakdown
- `GET /api/analytics/booking-status-breakdown`
- Response: Array of objects with `status`, `count`, and `total` fields

#### Average Revenue per Booking
- `GET /api/analytics/average-revenue-per-booking`
- Response: Object with `average_revenue` field

#### Revenue by Vehicle Type
- `GET /api/analytics/revenue-by-vehicle-type`
- Response: Array of objects with `type`, `total_revenue`, and `rentals` fields

#### Maintenance Alerts
- `GET /api/analytics/maintenance-alerts`
- Response: Array of objects with `vehicle`, `mileage_km`, `days_since_service`, and `recent_work` fields

#### Analytics Summary
- `GET /api/analytics/summary`
- Response: Comprehensive object with:
  - `fleet_health`: fleet metrics and utilization
  - `booking_behavior`: rental patterns and averages
  - `financial_snapshot`: revenue data
  - `maintenance_alerts`: array of maintenance alerts
  - `recommendations`: array of operational recommendations
  - `generated_at`: timestamp

### Export Endpoints

#### Bookings Export
- `POST /api/exports/bookings?format=xlsx&search=&from_date=&to_date=&status=`
- Parameters:
  - `format`: `csv`, `xlsx`, or `pdf` (default: `csv`)
  - `search`: search query string
  - `from_date`: filter from date (YYYY-MM-DD)
  - `to_date`: filter to date (YYYY-MM-DD)
  - `status`: filter by booking status
- Response: File download (binary stream)

#### Customers Export
- `POST /api/exports/customers?format=xlsx`
- Parameters:
  - `format`: `csv`, `xlsx`, or `pdf` (default: `csv`)
- Response: File download (binary stream)

#### Vehicles Export
- `POST /api/exports/vehicles?format=xlsx&status=&category=&search=`
- Parameters:
  - `format`: `csv`, `xlsx`, or `pdf` (default: `csv`)
  - `status`: filter by vehicle status
  - `category`: filter by vehicle category
  - `search`: search query string
- Response: File download (binary stream)

## API Documentation (Python)

When the Python service is running, interactive API documentation is available:
- Swagger UI: http://localhost:8001/docs
- ReDoc: http://localhost:8001/redoc

## Public Auth/Support Pages
- `GET/POST /login.php`
- `GET/POST /register.php`
- `GET/POST /forgot-password.php`
- `GET/POST /contact-admin.php`
- `GET /logout.php`

## Admin Pages
- `GET/POST /Admin/support-requests.php`
  - Handles contact responses (`respond_contact`)
  - Handles password reset completion/rejection (`reset_password`, `reject_request`)
- `GET /Admin/settings.php`

## Staff Pages
- Fleet operations: vehicles, maintenance, tracking
- Customer operations: customers, bookings, payments
- Reporting: `/Staff/reports.php`

## Customer Pages
- `/Customer/dashboard.php`
- `/Customer/bookings.php`, `/Customer/booking-create.php`, `/Customer/booking-view.php`
- `/Customer/payments.php`
- `/Customer/vehicles.php`
- `/Customer/tracking.php`

## Common Response/Status Patterns
- Write actions often return redirect + notice.
- Repository write functions return structures like:
  - `{ ok: true, ... }`
  - `{ ok: false, error: "..." }`
