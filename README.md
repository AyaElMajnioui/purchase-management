# Purchase Management - Aya EL MAJNIOUI

A Symfony-based application developed by **Aya EL MAJNIOUI** for managing purchases, suppliers, and procurement workflows efficiently.

## Features

- Manage purchase orders and requests
- Track suppliers and products
- Monitor procurement status
- User-friendly interface built with Symfony

## Requirements

- PHP 8.1 or higher
- Composer
- Symfony CLI (optional but recommended)
- A database supported by Symfony (MySQL, PostgreSQL, etc.)

## Installation

1. **Clone the repository:**

```bash
git clone https://github.com/AyaElMajnioui/purchase-management.git
cd purchase-management
Install dependencies:

bash
Copier le code
composer install
Set up environment variables:

Copy .env to .env.local and update database credentials:

bash
Copier le code
cp .env .env.local
Create the database:

bash
Copier le code
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
Run the Symfony server:

bash
Copier le code
symfony server:start
Access the application at http://localhost:8000.

Usage
Navigate through the web interface to manage purchases.

Add suppliers, create purchase orders, and track their status.

Contributing
Contributions are welcome! Please follow these steps:

Fork the repository.

Create a new branch: git checkout -b feature-name

Make your changes.

Commit your changes: git commit -m "Add new feature"

Push to the branch: git push origin feature-name

Open a pull request.
