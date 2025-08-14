# Project Structure

## Laravel Application Structure

### Core Application (`app/`)
- `app/Http/Controllers/` - HTTP request handlers
- `app/Models/` - Eloquent models and database entities
- `app/Providers/` - Service providers for dependency injection

### Configuration (`config/`)
- Database, cache, session, mail, and other service configurations
- Environment-specific settings loaded from `.env`

### Database (`database/`)
- `migrations/` - Database schema versioning
- `factories/` - Model factories for testing
- `seeders/` - Database seeding classes
- `database.sqlite` - SQLite database file (development)

### Frontend Assets (`resources/`)
- `css/` - Stylesheets (Tailwind CSS)
- `js/` - JavaScript files and components
- `views/` - Blade templates

### Routes (`routes/`)
- Web and API route definitions

### Public Assets (`public/`)
- `index.php` - Application entry point
- Static assets served directly by web server

### Testing (`tests/`)
- `Feature/` - Integration tests
- `Unit/` - Unit tests
- `TestCase.php` - Base test class

### Storage (`storage/`)
- `app/` - Application file storage
- `framework/` - Framework cache and sessions
- `logs/` - Application logs

### Build Configuration
- `composer.json` - PHP dependencies and scripts
- `package.json` - Node.js dependencies
- `vite.config.js` - Frontend build configuration
- `phpunit.xml` - Testing configuration

## Naming Conventions
- Controllers: PascalCase with `Controller` suffix
- Models: PascalCase, singular
- Database tables: snake_case, plural
- Routes: kebab-case for URLs
- Views: snake_case with `.blade.php` extension