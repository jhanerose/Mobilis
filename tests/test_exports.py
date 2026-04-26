import pytest
from playwright.sync_api import Page, expect
from test_config import BASE_URL, ADMIN_EMAIL, ADMIN_PASSWORD, PATH_PREFIX


class TestExports:
    """Test export functionality"""

    def test_bookings_export_modal_opens(self, authenticated_admin_page: Page):
        """Test that bookings export modal opens correctly"""
        # Navigate from dashboard
        authenticated_admin_page.click("text=Bookings")
        authenticated_admin_page.wait_for_url(f"{BASE_URL}{PATH_PREFIX}/Staff/bookings.php")
        # Click export button to open modal
        export_button = authenticated_admin_page.locator("[data-export-modal]")
        if export_button.is_visible():
            export_button.click()
            authenticated_admin_page.wait_for_timeout(1000)
            # Verify modal is open
            expect(authenticated_admin_page.locator("#export-modal")).to_be_visible()
            expect(authenticated_admin_page.locator("text=Export Data")).to_be_visible()
            # Close modal using the close button (scoped to export modal)
            authenticated_admin_page.locator("#export-modal .modal-close").click()
            authenticated_admin_page.wait_for_timeout(500)

    def test_vehicles_export_modal_opens(self, authenticated_admin_page: Page):
        """Test that vehicles export modal opens correctly"""
        # Navigate from dashboard
        authenticated_admin_page.click("text=Vehicles")
        authenticated_admin_page.wait_for_url(f"{BASE_URL}{PATH_PREFIX}/Staff/vehicles.php")
        # Click export button to open modal
        export_button = authenticated_admin_page.locator("[data-export-modal]")
        if export_button.is_visible():
            export_button.click()
            authenticated_admin_page.wait_for_timeout(1000)
            # Verify modal is open
            expect(authenticated_admin_page.locator("#export-modal")).to_be_visible()
            expect(authenticated_admin_page.locator("text=Export Data")).to_be_visible()
            # Close modal using the close button (scoped to export modal)
            authenticated_admin_page.locator("#export-modal .modal-close").click()
            authenticated_admin_page.wait_for_timeout(500)

    def test_customers_export_modal_opens(self, authenticated_admin_page: Page):
        """Test that customers export modal opens correctly"""
        # Navigate from dashboard
        authenticated_admin_page.click("text=Customers")
        authenticated_admin_page.wait_for_url(f"{BASE_URL}{PATH_PREFIX}/Staff/customers.php")
        # Click export button to open modal
        export_button = authenticated_admin_page.locator("[data-export-modal]")
        if export_button.is_visible():
            export_button.click()
            authenticated_admin_page.wait_for_timeout(1000)
            # Verify modal is open
            expect(authenticated_admin_page.locator("#export-modal")).to_be_visible()
            expect(authenticated_admin_page.locator("text=Export Data")).to_be_visible()
            # Close modal using the close button (scoped to export modal)
            authenticated_admin_page.locator("#export-modal .modal-close").click()
            authenticated_admin_page.wait_for_timeout(500)

    def test_payments_export_modal_opens(self, authenticated_admin_page: Page):
        """Test that payments export modal opens correctly"""
        # Navigate from dashboard
        authenticated_admin_page.click("text=Payments")
        authenticated_admin_page.wait_for_url(f"{BASE_URL}{PATH_PREFIX}/Staff/payments.php")
        # Click export button to open modal
        export_button = authenticated_admin_page.locator("[data-export-modal]")
        if export_button.is_visible():
            export_button.click()
            authenticated_admin_page.wait_for_timeout(1000)
            # Verify modal is open
            expect(authenticated_admin_page.locator("#export-modal")).to_be_visible()
            expect(authenticated_admin_page.locator("text=Export Data")).to_be_visible()
            # Close modal using the close button (scoped to export modal)
            authenticated_admin_page.locator("#export-modal .modal-close").click()
            authenticated_admin_page.wait_for_timeout(500)

    def test_export_format_selection_csv(self, authenticated_admin_page: Page):
        """Test CSV format selection"""
        # Navigate from dashboard
        authenticated_admin_page.click("text=Bookings")
        authenticated_admin_page.wait_for_url(f"{BASE_URL}{PATH_PREFIX}/Staff/bookings.php")
        # Click export button
        export_button = authenticated_admin_page.locator("[data-export-modal]")
        if export_button.is_visible():
            export_button.click()
            authenticated_admin_page.wait_for_timeout(1000)
            # Click on CSV card label instead of radio input
            csv_card = authenticated_admin_page.locator(".export-card").first
            if csv_card.is_visible():
                csv_card.click()
                authenticated_admin_page.wait_for_timeout(500)
                csv_radio = authenticated_admin_page.locator("input[name='export-format'][value='csv']")
                expect(csv_radio).to_be_checked()
            # Close modal using the close button (scoped to export modal)
            authenticated_admin_page.locator("#export-modal .modal-close").click()
            authenticated_admin_page.wait_for_timeout(500)

    def test_export_format_selection_excel(self, authenticated_admin_page: Page):
        """Test Excel format selection"""
        # Navigate from dashboard
        authenticated_admin_page.click("text=Bookings")
        authenticated_admin_page.wait_for_url(f"{BASE_URL}{PATH_PREFIX}/Staff/bookings.php")
        # Click export button
        export_button = authenticated_admin_page.locator("[data-export-modal]")
        if export_button.is_visible():
            export_button.click()
            authenticated_admin_page.wait_for_timeout(1000)
            # Click on Excel card label instead of radio input
            excel_card = authenticated_admin_page.locator(".export-card").nth(1)
            if excel_card.is_visible():
                excel_card.click()
                authenticated_admin_page.wait_for_timeout(500)
                excel_radio = authenticated_admin_page.locator("input[name='export-format'][value='xlsx']")
                expect(excel_radio).to_be_checked()
            # Close modal using the close button (scoped to export modal)
            authenticated_admin_page.locator("#export-modal .modal-close").click()
            authenticated_admin_page.wait_for_timeout(500)

    def test_export_format_selection_pdf(self, authenticated_admin_page: Page):
        """Test PDF format selection"""
        # Navigate from dashboard
        authenticated_admin_page.click("text=Bookings")
        authenticated_admin_page.wait_for_url(f"{BASE_URL}{PATH_PREFIX}/Staff/bookings.php")
        # Click export button
        export_button = authenticated_admin_page.locator("[data-export-modal]")
        if export_button.is_visible():
            export_button.click()
            authenticated_admin_page.wait_for_timeout(1000)
            # Click on PDF card label instead of radio input
            pdf_card = authenticated_admin_page.locator(".export-card").nth(2)
            if pdf_card.is_visible():
                pdf_card.click()
                authenticated_admin_page.wait_for_timeout(500)
                pdf_radio = authenticated_admin_page.locator("input[name='export-format'][value='pdf']")
                expect(pdf_radio).to_be_checked()
            # Close modal using the close button (scoped to export modal)
            authenticated_admin_page.locator("#export-modal .modal-close").click()
            authenticated_admin_page.wait_for_timeout(500)

    def test_export_submit_button_visible(self, authenticated_admin_page: Page):
        """Test that export confirm button is visible"""
        # Navigate from dashboard
        authenticated_admin_page.click("text=Bookings")
        authenticated_admin_page.wait_for_url(f"{BASE_URL}{PATH_PREFIX}/Staff/bookings.php")
        # Click export button
        export_button = authenticated_admin_page.locator("[data-export-modal]")
        if export_button.is_visible():
            export_button.click()
            authenticated_admin_page.wait_for_timeout(1000)
            expect(authenticated_admin_page.locator("#export-confirm")).to_be_visible()
            # Close modal using the close button (scoped to export modal)
            authenticated_admin_page.locator("#export-modal .modal-close").click()
            authenticated_admin_page.wait_for_timeout(500)
