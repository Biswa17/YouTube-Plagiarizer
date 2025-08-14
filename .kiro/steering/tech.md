# Technology Stack

## Backend Framework
- **Laravel 12.0** - PHP web application framework
- **PHP 8.2+** - Required PHP version

## Frontend
- **Vite** - Build tool and dev server
- **Tailwind CSS 4.0** - Utility-first CSS framework
- **Axios** - HTTP client for API requests

## Database
- **SQLite** - Default database for development
- Supports MySQL, PostgreSQL, and other Laravel-compatible databases

## Development Tools
- **Laravel Pint** - PHP code style fixer
- **Laravel Sail** - Docker development environment
- **Laravel Pail** - Log viewer
- **PHPUnit** - Testing framework
- **Faker** - Test data generation

## Common Commands

### Development
```bash
# Start development server with all services
composer run dev

# Individual services
php artisan serve          # Start Laravel server
npm run dev               # Start Vite dev server
php artisan queue:listen  # Start queue worker
php artisan pail          # View logs
```

### Testing
```bash
composer run test         # Run all tests
php artisan test         # Alternative test command
```

### Database
```bash
php artisan migrate       # Run migrations
php artisan migrate:fresh # Fresh migration
php artisan db:seed      # Run seeders
```

### Code Quality
```bash
./vendor/bin/pint        # Fix code style
```

### Build
```bash
npm run build            # Build for production
```