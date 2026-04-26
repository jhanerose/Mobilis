# Python Analytics & Export Service Integration

This document describes the Python microservice integration for analytics and data export functionality in the Mobilis system.

## Overview

The Python microservice provides enhanced analytics capabilities and multi-format data export (CSV, Excel, PDF) for the Mobilis vehicle rental system. It runs as a separate FastAPI service that consumes data from the existing PHP backend APIs.

## Architecture

```
PHP Frontend (reports.php, export files)
    ↓ (HTTP requests with fallback)
Python Microservice (FastAPI on port 8001)
    ↓ (HTTP requests)
PHP Backend APIs (dashboard.php, tracking.php)
    ↓
MySQL Database
```

## Service Location

- **Directory**: `python-service/`
- **Default Port**: 8001
- **Framework**: FastAPI
- **Language**: Python 3.11+

## Installation

### Prerequisites

- Python 3.11 or higher
- pip package manager
- PHP backend running (http://localhost:8000)

### Setup Steps

1. Navigate to the python-service directory:
```bash
cd python-service
```

2. Install dependencies:
```bash
pip install -r requirements.txt
```

3. Configure environment variables (optional):
```bash
cp .env.example .env
```

Edit `.env` file:
```
PHP_API_BASE_URL=http://localhost:8000
PYTHON_SERVICE_PORT=8001
PYTHON_SERVICE_HOST=0.0.0.0
CORS_ORIGINS=http://localhost:8000
```

4. Run the service:
```bash
python -m app.main
```

The service will be available at http://localhost:8001

### Docker Deployment

Build and run with Docker:
```bash
docker build -t mobilis-analytics .
docker run -p 8001:8001 mobilis-analytics
```

## API Endpoints

### Health Check
- `GET /health` - Service health status

### Dashboard Metrics
- `GET /api/dashboard/metrics` - Get dashboard KPIs (total fleet, active rentals, revenue today, utilization rate)

### Analytics Endpoints

#### Revenue Trends
- `GET /api/analytics/revenue-trends?period=month`
- Parameters:
  - `period`: `week`, `month`, or `year` (default: `month`)
- Returns: Array of revenue data by date

#### Booking Trends
- `GET /api/analytics/booking-trends?period=month`
- Parameters:
  - `period`: `week`, `month`, or `year` (default: `month`)
- Returns: Array of booking count and revenue by date

#### Top Customers
- `GET /api/analytics/top-customers?limit=10`
- Parameters:
  - `limit`: Number of customers to return (default: 10)
- Returns: Array of top customers by revenue

#### Vehicle Performance
- `GET /api/analytics/vehicle-performance`
- Returns: Array of vehicle performance metrics (rentals, revenue by vehicle)

#### Payment Status Breakdown
- `GET /api/analytics/payment-status-breakdown`
- Returns: Payment status distribution (paid, unpaid, partial)

#### Booking Status Breakdown
- `GET /api/analytics/booking-status-breakdown`
- Returns: Booking status distribution (pending, active, completed, cancelled)

#### Average Revenue per Booking
- `GET /api/analytics/average-revenue-per-booking`
- Returns: Average revenue amount per booking

#### Revenue by Vehicle Type
- `GET /api/analytics/revenue-by-vehicle-type`
- Returns: Revenue breakdown by vehicle category

#### Maintenance Alerts
- `GET /api/analytics/maintenance-alerts`
- Returns: Vehicles needing maintenance based on mileage and service history

#### Analytics Summary
- `GET /api/analytics/summary`
- Returns: Comprehensive summary including fleet health, booking behavior, financial snapshot, and recommendations

### Export Endpoints

#### Bookings Export
- `POST /api/exports/bookings?format=xlsx&search=&from_date=&to_date=&status=`
- Parameters:
  - `format`: `csv`, `xlsx`, or `pdf` (default: `csv`)
  - `search`: Search query string
  - `from_date`: Filter from date (YYYY-MM-DD)
  - `to_date`: Filter to date (YYYY-MM-DD)
  - `status`: Filter by booking status
- Returns: File download

#### Customers Export
- `POST /api/exports/customers?format=xlsx`
- Parameters:
  - `format`: `csv`, `xlsx`, or `pdf` (default: `csv`)
- Returns: File download

#### Vehicles Export
- `POST /api/exports/vehicles?format=xlsx&status=&category=&search=`
- Parameters:
  - `format`: `csv`, `xlsx`, or `pdf` (default: `csv`)
  - `status`: Filter by vehicle status
  - `category`: Filter by vehicle category
  - `search`: Search query string
- Returns: File download

## PHP Frontend Integration

The PHP frontend automatically detects if the Python service is available and falls back to PHP functions if not.

### Configuration

All settings are hardcoded for simplicity. Edit the following files as needed:

**Python service (`python-service/app/config.py`)**:
```python
PHP_API_BASE_URL = "http://localhost"  # XAMPP default
PHP_PROJECT_PATH = "Mobilis-System"    # Your project folder
PYTHON_API_KEY = "mobilis-api-key-2024"  # Must match PHP
```

**PHP backend (`app/auth.php`)**:
```php
$validApiKey = 'mobilis-api-key-2024';  // Must match Python
```

The API key is hardcoded in both files as `mobilis-api-key-2024` by default. No environment variables are needed.

### Updated Files

The following PHP files have been updated to use the Python service:

1. **`public/Staff/reports.php`** - Analytics data fetching
   - Calls Python analytics endpoints for reports
   - Falls back to PHP functions if service unavailable

2. **`public/Staff/bookings-export.php`** - Bookings export
   - Supports CSV, Excel, and PDF formats via Python
   - Falls back to CSV generation via PHP

3. **`public/Staff/customers-export.php`** - Customers export
   - Supports CSV, Excel, and PDF formats via Python
   - Falls back to CSV generation via Python

4. **`public/Staff/vehicles-export.php`** - Vehicles export
   - Supports CSV, Excel, and PDF formats via Python
   - Falls back to CSV generation via PHP

### Usage in PHP

The PHP files use a helper function to fetch data from Python:

```php
$pythonServiceUrl = getenv('PYTHON_SERVICE_URL') ?: 'http://localhost:8001';
$pythonAvailable = @file_get_contents($pythonServiceUrl . '/health') !== false;

if ($pythonAvailable) {
    $data = fetchFromPython('/api/analytics/summary');
} else {
    // Fallback to PHP functions
    $data = getAnalyticsSummary();
}
```

## API Documentation

When the Python service is running, automatic API documentation is available:

- **Swagger UI**: http://localhost:8001/docs
- **ReDoc**: http://localhost:8001/redoc

## Data Flow

### Analytics Flow

1. User accesses reports page in PHP frontend
2. PHP checks if Python service is available
3. If available, PHP calls Python analytics endpoints
4. Python calls PHP backend APIs (dashboard.php, tracking.php)
5. Python processes data with pandas
6. Python returns JSON response to PHP
7. PHP renders charts and tables

### Export Flow

1. User clicks export button with format selection
2. PHP checks if Python service is available
3. If available, PHP proxies request to Python export endpoint
4. Python fetches data from PHP backend
5. Python generates export file (CSV/Excel/PDF)
6. Python streams file back through PHP to user
7. User downloads file

## Libraries Used

- **FastAPI**: Modern async web framework
- **httpx**: Async HTTP client for calling PHP APIs
- **pandas**: Data processing and analysis
- **openpyxl**: Excel (.xlsx) file generation
- **xlsxwriter**: Alternative Excel writer
- **reportlab**: PDF generation
- **Pydantic**: Data validation and settings management
- **python-dotenv**: Environment variable management

## Troubleshooting

### Service Not Starting

Check if port 8001 is already in use:
```bash
# Linux/Mac
lsof -i :8001

# Windows
netstat -ano | findstr :8001
```

### PHP Can't Connect to Python

1. Verify Python service is running: `curl http://localhost:8001/health`
2. Check firewall settings
3. Verify `PYTHON_SERVICE_URL` environment variable is set correctly
4. Check CORS configuration in `app/config.py`

### Export Not Working

1. Verify all Python dependencies are installed
2. Check Python service logs for errors
3. Ensure PHP backend APIs are accessible from Python service
4. Verify file permissions for temporary directories

### Analytics Data Empty

1. Check that PHP backend APIs are returning data
2. Verify database connection in PHP backend
3. Check Python service logs for API call errors
4. Test PHP backend APIs directly: `curl http://localhost:8000/api/dashboard.php`

## Benefits

1. **Enhanced Exports**: Professional Excel and PDF exports with styling
2. **Better Data Processing**: pandas for complex analytics
3. **Separation of Concerns**: Analytics logic decoupled from PHP
4. **Scalability**: Python microservice can scale independently
5. **Modern Stack**: FastAPI provides automatic API documentation
6. **Future Flexibility**: Easy to add ML models, advanced analytics

## Future Enhancements

- Add machine learning models for demand forecasting
- Implement real-time analytics with WebSocket
- Add scheduled report generation
- Create custom report builder
- Add data visualization endpoints (generate charts server-side)
- Implement caching for improved performance
- Add authentication/authorization for Python service
