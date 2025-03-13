# Tummy Pillow Bakery

An e-commerce web application for a bakery business that offers online ordering and delivery services.

## Features

- User Authentication (Register/Login)
- Product Catalog with Categories
- Shopping Cart Functionality
- Order Processing
- User Profile Management
- Admin Dashboard
- Sales Reporting
- Delivery Management

## Requirements

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Composer

## Installation

1. Clone the repository:
```
git clone https://github.com/your-username/tummy-pillow.git
cd tummy-pillow
```

2. Install dependencies:
```
composer install
```

3. Create a new database and import the database schema:
```
mysql -u username -p your_database < database/migrations/setup.sql
```

4. Create a configuration file:
```
cp config/config.example.php config/config.php
```

5. Update the configuration file with your database credentials.

6. Set up a web server to point to the `public` directory.

7. (Optional) Import sample data:
```
mysql -u username -p your_database < database/seeds/default_products.sql
```

## Development

- The application follows the MVC architecture pattern
- Controllers are located in `app/controllers/`
- Models are located in `app/models/`
- Views are located in `app/views/`

## Security Features

- CSRF Protection
- Prepared SQL Statements
- Password Hashing
- Input Validation
- XSS Prevention

## License

This project is licensed under the MIT License - see the LICENSE file for details.