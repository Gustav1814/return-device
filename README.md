<<<<<<< HEAD
# 🔄 Device Return — SaaS Device Recovery Platform

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-10-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 10">
  <img src="https://img.shields.io/badge/React-19-61DAFB?style=for-the-badge&logo=react&logoColor=black" alt="React 19">
  <img src="https://img.shields.io/badge/Vite-5-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite 5">
  <img src="https://img.shields.io/badge/TailwindCSS-3-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 3">
  <img src="https://img.shields.io/badge/MySQL-8-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL 8">
  <img src="https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker Ready">
</p>

A full-stack **multi-tenant SaaS platform** that automates the end-to-end device recovery process — from employee onboarding and shipping label generation to payment processing and order tracking. Built with **Laravel 10** (API + Blade) and a modern **React 19 SPA** dashboard.

---

## ✨ Features

### 📊 Dashboard & Analytics
- Real-time KPI cards (total orders, in-progress, completed, revenue)
- Interactive charts powered by **Chart.js**
- Company-level and system-wide analytics

### 🏢 Multi-Tenant Company Management
- White-label support with per-company theming (logo, colors, favicon)
- Company onboarding with recipients and employee management
- Custom pricing and commission structures per company

### 📦 Order Lifecycle Management
- **Create** individual orders or **bulk-import** via CSV
- Order statuses: Pending → Label Created → In Transit → Delivered → Completed
- Detailed order view with full audit trail
- Edit orders with employee address/device updates

### 🏷️ Shipping & Label Generation
- Integrated with **Shippo API** for UPS/USPS/FedEx label generation
- Automatic tracking number assignment
- Downloadable PDF shipping labels (via **DomPDF**)

### 💳 Payment Processing
- **PayPal Payflow Pro** integration for secure credit card payments
- Insurance rate calculations
- Coupon/discount code system with flexible validation rules

### 👥 User & Access Management
- Role-based authentication (Admin, Company User)
- Laravel Breeze + Sanctum for secure API auth
- User CRUD with company assignment

### 📧 Automated Notifications
- Status-based email triggers (configurable per company)
- SMS tracking notifications
- BCC support for audit trails

### 🔌 REST API
- Full API for third-party integrations
- API key management per user
- Paginated endpoints with filtering

### ⚙️ System Settings
- Global system configuration panel
- Per-company settings override
- Price management and carrier selection

---

## 🏗️ Tech Stack

| Layer        | Technology                                          |
|-------------|-----------------------------------------------------|
| **Backend**  | PHP 8.1, Laravel 10, Eloquent ORM                   |
| **Frontend** | React 19, TypeScript, Vite 5, TailwindCSS 3         |
| **UI/UX**    | Lucide React icons, Framer Motion animations         |
| **Database** | MySQL 8 with 45+ migrations                          |
| **Payments** | PayPal Payflow Pro                                    |
| **Shipping** | Shippo API (UPS, USPS, FedEx)                        |
| **PDF**      | Barryvdh DomPDF                                      |
| **Auth**     | Laravel Breeze + Sanctum (SPA token auth)            |
| **DevOps**   | Docker, Docker Compose, Vite HMR                     |

---

## 🚀 Quick Start

### Prerequisites

- **PHP** ≥ 8.1 with extensions: `pdo_mysql`, `mbstring`, `xml`, `curl`, `zip`
- **Composer** ≥ 2.x
- **Node.js** ≥ 18 with **npm**
- **MySQL** ≥ 8.0

### Local Setup

```bash
# 1. Clone the repository
git clone https://github.com/Gustav1814/return-device.git
cd return-device

# 2. Install PHP dependencies
composer install

# 3. Install JS dependencies
npm install

# 4. Environment setup
cp .env.example .env
php artisan key:generate

# 5. Configure your database in .env
#    DB_DATABASE=returndevice
#    DB_USERNAME=root
#    DB_PASSWORD=your_password

# 6. Run migrations
php artisan migrate

# 7. Start the development servers (run in separate terminals)
php artisan serve          # Backend → http://localhost:8000
npm run dev                # Vite HMR  → http://127.0.0.1:5173
```

Open **http://localhost:8000/saas** for the React dashboard.

---

## 🐳 Docker Setup (Recommended)

Spin up the entire stack with a single command:

```bash
# 1. Clone and enter the project
git clone https://github.com/Gustav1814/return-device.git
cd return-device

# 2. Build and start all services
docker-compose up --build -d

# 3. First-time setup (run once)
docker-compose exec app composer install
docker-compose exec app cp .env.example .env
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
docker-compose exec app npm install
docker-compose exec app npm run build
```

The application will be available at **http://localhost:8000**.

To stop:
```bash
docker-compose down
```

---

## 📁 Project Structure

```
return-device/
├── app/
│   ├── Http/Controllers/     # API & web controllers
│   ├── Models/                # Eloquent models (11 models)
│   ├── Libraries/             # Custom helper libraries
│   └── Providers/             # Service providers
├── config/                    # Laravel configuration
├── database/
│   └── migrations/            # 45 database migrations
├── resources/
│   ├── css/                   # TailwindCSS styles
│   ├── js/
│   │   ├── react/             # ⚛️ React SPA
│   │   │   ├── screens/       # 19 page components
│   │   │   ├── components/    # Shared UI components
│   │   │   ├── api/           # API client layer
│   │   │   ├── context/       # React context providers
│   │   │   ├── hooks/         # Custom React hooks
│   │   │   ├── theme/         # Theming system
│   │   │   └── utils/         # Utility functions
│   │   └── app.js             # Blade entry point
│   └── views/                 # Blade templates
├── routes/
│   ├── web.php                # Web routes
│   ├── api.php                # API routes
│   └── auth.php               # Auth routes
├── public/                    # Public assets
├── docker-compose.yml         # Docker orchestration
├── Dockerfile                 # Container build
└── vite.config.js             # Vite + React + Laravel plugin
```

---

## 🔐 Environment Variables

Copy `.env.example` to `.env` and configure:

| Variable               | Description                            |
|------------------------|----------------------------------------|
| `DB_*`                 | MySQL connection details               |
| `PAYFLOW_*`            | PayPal Payflow Pro credentials         |
| `SHIPPO_PRIVATE`       | Shippo API key for shipping labels     |
| `MAIL_*`               | SMTP email configuration               |
| `REMOTE_COMPANY_*`     | Default company details                |
| `CURR_DOMAIN`          | Application domain for white-labeling  |
| `ORDER_AMT`            | Default order amount                   |
| `INSURANCE_RATE`       | Shipping insurance percentage          |
| `LABEL_CARRIER`        | Default shipping carrier (UPS/USPS)    |

---

## 📸 Screenshots

> Screenshots coming soon — run the app locally or via Docker to preview.

---

## 🛠️ Development

```bash
# Run backend tests
php artisan test

# Build production assets
npm run build

# Fresh migration with seeders
php artisan migrate:fresh --seed
```

---

## 📄 License

This project is open-sourced under the [MIT License](https://opensource.org/licenses/MIT).

---

<p align="center">
  Built with ❤️ by <a href="https://github.com/Gustav1814">Gustav1814</a>
</p>
=======
# DeviceReturn

A modern return-logistics platform for IT asset recovery, shipment tracking, and operations management.

Built as a full-stack web application with a Laravel backend and a React frontend, this project focuses on practical workflow design: teams can create orders, monitor in-progress and completed returns, and track payment metrics from a single dashboard.

---

## Overview

DeviceReturn helps operations teams manage the full return lifecycle:

- Create and process return orders
- Track shipment status in real time
- Manage users, companies, coupons, and commissions
- Monitor revenue and fulfillment metrics from a centralized dashboard
- Support both light and dark dashboard experiences

---

## Demo Screens

<p align="center">
  <img src="docs/screenshots/login-dark.png" alt="Login - dark theme" width="48%" />
  <img src="docs/screenshots/login-light.png" alt="Login - light theme" width="48%" />
</p>

<p align="center">
  <img src="docs/screenshots/dashboard-light.png" alt="Dashboard - light theme" width="48%" />
  <img src="docs/screenshots/dashboard-dark.png" alt="Dashboard - dark theme" width="48%" />
</p>

---

## Core Features

- Authentication and role-aware access
- Order lifecycle management (new, in progress, completed)
- Bulk order support
- Company and user administration
- Coupon and commission modules
- Analytics dashboard for shipments and payments
- API integration area for external systems
- Theme toggle (light/dark mode)

---

## Tech Stack

- **Backend:** Laravel, PHP
- **Frontend:** React, TypeScript, Vite
- **Database:** MySQL
- **Styling:** Custom CSS (dashboard and SaaS UI layers)

---

## Project Structure

```text
app/                Laravel controllers, models, services
resources/js/react/ React SPA screens, routes, components
resources/views/    Blade views
routes/             Web and API route definitions
database/           Migrations and seeders
public/             Static assets
docs/screenshots/   README demo images
```

---

## Run Locally

### Prerequisites

- PHP 8+
- Composer
- Node.js 18+
- MySQL

### Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Update your `.env` values (database, mail, app URL), then run:

```bash
php artisan migrate
php artisan serve
npm run dev
```

Application URL: `http://127.0.0.1:8000`

---

## Portfolio Note

This repository is a portfolio-safe version of the project.  
Sensitive credentials and private environment configuration are intentionally excluded.
>>>>>>> ef70610 (Refresh portfolio README with a polished project narrative and visual demo gallery.)
