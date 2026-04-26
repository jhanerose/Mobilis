# Mobilis E2E Tests

End-to-end automated tests using Playwright and Python.

## Prerequisites

- Python 3.8+
- PHP development server running on localhost:8000 (or XAMPP on localhost)
- MySQL database with test data (run `mobilis_sql.sql`)

## Installation

1. Install Python dependencies:
```bash
cd tests
pip install -r requirements.txt
```

2. Install Playwright browsers:
```bash
python -m playwright install
```

Or if playwright is in your PATH:
```bash
playwright install
```

## Configuration

Edit `playwright.config.py` to configure:

- `BASE_URL`: Default is `http://localhost:8000` for PHP dev server
- `BASE_URL_XAMPP`: Set to `http://localhost/Mobilis-System/public` for XAMPP

To use XAMPP instead of PHP dev server, set environment variable:
```bash
export USE_XAMPP=true
```

Or on Windows PowerShell:
```powershell
$env:USE_XAMPP="true"
```

## Running Tests

### Run all tests:
```bash
python -m pytest
```

### Run specific test file:
```bash
python -m pytest test_auth.py
```

### Run specific test:
```bash
python -m pytest test_auth.py::TestAuthentication::test_admin_login_success
```

### Run with headed browser (visible):
```bash
python -m pytest --headed
```

### Run with debug mode:
```bash
python -m pytest --debug
```

### Run specific marker:
```bash
python -m pytest -m auth
python -m pytest -m vehicles
python -m pytest -m bookings
python -m pytest -m customers
python -m pytest -m reports
python -m pytest -m exports
```

Or if pytest is in your PATH:
```bash
pytest
```

## Test Structure

- `test_auth.py` - Authentication tests (login, logout, role-based access)
- `test_vehicles.py` - Vehicles module tests
- `test_bookings.py` - Bookings module tests
- `test_customers.py` - Customers module tests
- `test_reports.py` - Reports module tests
- `test_exports.py` - Export functionality tests
- `conftest.py` - Pytest fixtures and configuration
- `playwright.config.py` - Test configuration
- `pytest.ini` - Pytest settings

## Test Credentials

Default test accounts (from database seed):
- Admin: `admin@mobilis.ph` / `password`
- Staff: `staff@mobilis.ph` / `password`
- Customer: `customer@mobilis.ph` / `password`

## Output

Test results are saved to:
- Screenshots: `test-results/` (on failure)
- Videos: `test-results/` (on failure)
- Traces: `test-results/` (on failure)

## CI/CD Integration

Add to your CI pipeline:

```yaml
- name: Run E2E tests
  run: |
    cd tests
    pip install -r requirements.txt
    python -m playwright install --with-deps
    python -m pytest
```

## Troubleshooting

**Tests fail with connection refused:**
- Ensure PHP server is running: `php -S localhost:8000 -t public`
- Or ensure XAMPP Apache is running

**Tests fail with database errors:**
- Ensure MySQL is running
- Ensure database is seeded: `mysql -u root mobilis_db < mobilis_sql.sql`

**Playwright browsers not found:**
- Run: `python -m playwright install`
- Or add Python Scripts folder to your PATH and run: `playwright install`
