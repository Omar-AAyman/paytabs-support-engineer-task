# PayTabs Integration Demo

A lightweight Laravel application demonstrating a seamless **PayTabs Hosted Payment Page (iFrame)** integration for an e-commerce checkout flow.

## 🚀 Features

- **End-to-End Order Flow**: Create, Checkout, Pay, and Refund.
- **PayTabs iFrame Integration**: Secure payments without leaving the merchant site.
- **Order Management**: View order history, status, and precise stock tracking.
- **Refund Management**: Initiate refunds directly from the dashboard with stock restoration.
- **Robust Logging**: Detailed logs of all API Requests (Auth/Refund) and Callback Payloads.
- **Stock Control**: Prevents overselling with real-time validation and lock mechanisms.
- **Zero-Redundancy**: Customer details are pre-filled, and regional fields (Country/Zip) are auto-handled for a seamless experience.

## 🛠️ Technology Stack

- **Framework**: Laravel 12 (PHP 8.2+)
- **Database**: MySQL.
- **Payment Gateway**: PayTabs (Hosted Payment Page - iFrame).
- **Frontend**: Blade Templates + jQuery (AJAX Payment) + Vanilla CSS (Glassmorphism UI).

## 🔧 Setup Instructions

### 1. Clone & Install
```bash
git clone https://github.com/your-username/paytabs-demo.git
cd paytabs-demo
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Configure Database & PayTabs
Edit your `.env` file with your credentials:
```env
DB_CONNECTION=mysql
DB_DATABASE=paytabs_demo

# PayTabs Configuration
PAYTABS_PROFILE_ID=YOUR_PROFILE_ID
PAYTABS_SERVER_KEY=YOUR_SERVER_KEY
PAYTABS_BASE_URL=https://secure-egypt.paytabs.com/payment/request
PAYTABS_CURRENCY=EGP
PAYTABS_CALLBACK_URL=https://your-live-domain.com/paytabs/callback
```
*Note: For local development, use Ngrok to generate a `PAYTABS_CALLBACK_URL`.*

### 3. Migrate & Seed
```bash
php artisan migrate --seed
```
*This will seed 5 sample products (Laptop, Smartphone, etc.) for testing.*

## 🧪 How to Test

### User Flow
1. **New Order**: Go to `/orders/create` and select products (e.g., Laptop).
2. **Checkout**: Enter simplified billing details (Address & City only).
3. **Payment**: The PayTabs payment page loads inside an iFrame.
4. **Success**: Upon payment, you are redirected to the Success Page.
5. **Logs**: Go to Order Details to see the raw API `Auth` payload and response.

### Refund Flow
1. Open a "Completed" order.
2. Click the **Refund Order** button.
3. The system finds the transaction reference and sends a Refund API call.
4. Stock is automatically restored, and a `Refund` log is added.

## 📂 Project Structure Highlights
- `app/Services/PayTabsService.php`: Encapsulates all API logic (Auth & Refund).
- `app/Http/Controllers/PayTabsController.php`: Handles IPN Callbacks, Returns, and Refund logic.
- `public/js/payment.js`: Handles the AJAX request to load the iFrame dynamically.
- `database/migrations/*_payment_logs_table.php`: Stores full JSON payloads for transparent debugging.

## 🤝 Vision Alignment
This project aligns with PayTabs' mission **"To power every digital transaction in the region"** by:
1.  **Simplifying Integration**: Providing a clean, reusable Service layer that any developer can understand.
2.  **Enhancing Trust**: Transparent logging builds confidence for both merchants and customers.
3.  **Localizing Experience**: Pre-filling customer data to reduce friction for regional users.