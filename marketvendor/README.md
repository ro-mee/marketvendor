# BlueLedger Finance - Loan Management System

## Project Structure

This project contains multiple specialized portals for different aspects of loan management:

### Portal Files
- **`client-portal.html`** - Client/Vendor portal for loan applications and tracking
- **`admin-portal.html`** - Admin portal for system management and operations
- **`loan-management.html`** - Dedicated loan operations and package management
- **`vendor-management.html`** - Vendor onboarding and compliance management
- **`test.html`** - Original combined portal (role-based via URL parameter)
- **`test1.html`** - Enhanced combined portal with additional features

### Shared Assets
- **`styles.css`** - Basic CSS styles for original portals
- **`enhanced-styles.css`** - Enhanced CSS with additional components and styling
- **`client.html`** - Placeholder file (can be removed or repurposed)

## Portal Features

### Client Portal (`client-portal.html`)
- Dashboard with active loan overview
- Loan application form with document upload
- Payment tracking and amortization schedule
- Daily sales input tracker
- Cash flow summary and loan impact visualization
- Workflow status tracking

### Loan Management Portal (`loan-management.html`)
- Loan package creation and configuration
- Application processing and approval workflows
- Amortization schedule generation
- Payment tracking and collections
- Risk assessment and analytics
- Performance monitoring and reporting

### Vendor Management Portal (`vendor-management.html`)
- Vendor onboarding and registration
- Document verification and compliance
- Application processing and approval
- Performance tracking and analytics
- Compliance monitoring
- Vendor directory and management

### Admin Portal (`admin-portal.html`)
- Operations dashboard with key metrics
- System administration and settings
- User management and access control
- Reports and analytics
- Activity logs and auditing

## Usage

1. **Client Access**: Open `client-portal.html` in a web browser
2. **Loan Management**: Open `loan-management.html` for loan operations
3. **Vendor Management**: Open `vendor-management.html` for vendor operations
4. **Admin Access**: Open `admin-portal.html` for system administration
5. **Legacy Mode**: Use `test.html?role=admin` or `test.html?role=vendor` for original combined interface
6. **Enhanced Mode**: Use `test1.html?role=admin` or `test1.html?role=vendor` for enhanced combined interface

## Technical Details

- **Frontend**: Pure HTML, CSS, and JavaScript
- **Styling**: Custom CSS with CSS variables for theming
- **Layout**: Responsive sidebar navigation with grid-based content
- **Color Scheme**: Dark blue theme optimized for financial applications

## Next Steps

- Add backend database integration
- Implement user authentication
- Add form validation and submission
- Create API endpoints for data operations
- Add real-time notifications
- Implement file upload functionality
