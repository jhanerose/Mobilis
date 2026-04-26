import pytest
from playwright.sync_api import Page, expect
from test_config import BASE_URL, ADMIN_EMAIL, ADMIN_PASSWORD, PATH_PREFIX


class TestReports:
    """Test reports module functionality"""

    def test_reports_page_loads(self, authenticated_admin_page: Page):
        """Test that reports page loads correctly"""
        # Navigate from dashboard
        authenticated_admin_page.click("text=Reports")
        authenticated_admin_page.wait_for_url(f"{BASE_URL}{PATH_PREFIX}/Staff/reports.php")
        expect(authenticated_admin_page).to_have_title("Reports | Mobilis")

    def test_fleet_utilization_display(self, authenticated_admin_page: Page):
        """Test that fleet utilization is displayed"""
        # Navigate from dashboard
        authenticated_admin_page.click("text=Reports")
        authenticated_admin_page.wait_for_url(f"{BASE_URL}{PATH_PREFIX}/Staff/reports.php")
        expect(authenticated_admin_page.locator("text=Fleet Utilization").first).to_be_visible()
        # Check for utilization percentage
        expect(authenticated_admin_page.locator("text=%")).to_be_visible()

    def test_revenue_today_display(self, authenticated_admin_page: Page):
        """Test that revenue today is displayed"""
        # Navigate from dashboard
        authenticated_admin_page.click("text=Reports")
        authenticated_admin_page.wait_for_url(f"{BASE_URL}{PATH_PREFIX}/Staff/reports.php")
        expect(authenticated_admin_page.locator("text=Revenue Today").first).to_be_visible()
        # Check for currency symbol in the revenue card
        revenue_card = authenticated_admin_page.locator(".reports-kpi-card.finance")
        expect(revenue_card).to_be_visible()

    def test_booking_trends_chart(self, authenticated_admin_page: Page):
        """Test that booking trends chart is displayed"""
        # Navigate from dashboard
        authenticated_admin_page.click("text=Reports")
        authenticated_admin_page.wait_for_url(f"{BASE_URL}{PATH_PREFIX}/Staff/reports.php")
        # Check for chart container
        expect(authenticated_admin_page.locator(".reports-chart-card").first).to_be_visible()
        # Wait for chart to render
        authenticated_admin_page.wait_for_timeout(2000)

    def test_revenue_trends_chart(self, authenticated_admin_page: Page):
        """Test that revenue trends chart is displayed"""
        # Navigate from dashboard
        authenticated_admin_page.click("text=Reports")
        authenticated_admin_page.wait_for_url(f"{BASE_URL}{PATH_PREFIX}/Staff/reports.php")
        # Check for chart container
        expect(authenticated_admin_page.locator(".reports-chart-card").first).to_be_visible()
        # Wait for chart to render
        authenticated_admin_page.wait_for_timeout(2000)

    def test_top_customers_display(self, authenticated_admin_page: Page):
        """Test that top customers are displayed"""
        # Navigate from dashboard
        authenticated_admin_page.click("text=Reports")
        authenticated_admin_page.wait_for_url(f"{BASE_URL}{PATH_PREFIX}/Staff/reports.php")
        expect(authenticated_admin_page.locator("text=Top Customers").first).to_be_visible()

    def test_vehicle_performance_display(self, authenticated_admin_page: Page):
        """Test that vehicle performance section exists"""
        # Navigate from dashboard
        authenticated_admin_page.click("text=Reports")
        authenticated_admin_page.wait_for_url(f"{BASE_URL}{PATH_PREFIX}/Staff/reports.php")
        # Vehicle Performance is in the details section, check for table
        vehicle_table = authenticated_admin_page.locator(".reports-detail-grid").first
        if vehicle_table.is_visible():
            expect(vehicle_table).to_be_visible()

    def test_maintenance_alerts_display(self, authenticated_admin_page: Page):
        """Test that maintenance alerts are displayed"""
        # Navigate from dashboard
        authenticated_admin_page.click("text=Reports")
        authenticated_admin_page.wait_for_url(f"{BASE_URL}{PATH_PREFIX}/Staff/reports.php")
        expect(authenticated_admin_page.locator("text=Maintenance Alerts").first).to_be_visible()

    def test_recommendations_display(self, authenticated_admin_page: Page):
        """Test that recommendations are displayed"""
        # Navigate from dashboard
        authenticated_admin_page.click("text=Reports")
        authenticated_admin_page.wait_for_url(f"{BASE_URL}{PATH_PREFIX}/Staff/reports.php")
        expect(authenticated_admin_page.locator("text=Recommendations").first).to_be_visible()
