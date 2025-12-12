# Giftos

A role‑based e-commerce platform built with Laravel. Users can browse products, manage carts and orders, write reviews, and receive real-time notifications. Managers get inventory and sales reporting features while admins have full user and product management capabilities.

## App Overview

Giftos helps small businesses manage their online store:

- **Customers** browse products, manage carts, place orders, write reviews, and track their purchases
- **Managers** manage inventory, view sales reports, and handle order fulfillment
- **Admins** manage users, products, categories, coupons, and all store operations
- Optional real-time notifications (Laravel Broadcasting + Redis) and email alerts for orders

## Why I built it

A comprehensive, production-ready Laravel app to practice:

- Advanced role-based access control (RBAC) with 50+ granular permissions
- RESTful API design with Laravel Sanctum authentication
- E-commerce architecture (cart, checkout, payments)
- Multi-tier caching strategy with Redis
- Queue-based background processing
- Docker containerization for production deployment

### Getting Started


## Local Setup

```bash
# clone
git clone <repo-url>
cd giftos

# install PHP dependencies
composer install

# install JS dependencies
npm install

# env
cp .env.example .env
# then edit .env and set DB/MAIL/REDIS/PAYMENT configs

# generate app key
php artisan key:generate

# database
php artisan migrate --seed

# build assets
npm run build

# run Redis (dev)
# macOS: brew services start redis
# Ubuntu: sudo service redis-server start
# Windows: use Memurai or WSL + redis-server

# run server (dev)
php artisan serve
# App will be available at http://localhost:8000

# in another terminal, start queue worker
php artisan queue:work

# (optional) for development with hot reload
npm run dev
```

## Docker Setup

```bash
# Start all services
docker-compose up -d

# Initial setup (run these after first start)
docker-compose exec php-fpm php artisan key:generate --force
docker-compose exec php-fpm php artisan migrate:refresh --force
docker-compose exec php-fpm php artisan db:seed --force
docker-compose exec php-fpm php artisan storage:link
docker-compose exec php-fpm php artisan optimize:clear

# Access application
# http://localhost:8000

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

## Environment Variables (.env)

```env
APP_NAME=Giftos
APP_ENV=local
APP_KEY=base64:your_generated_key
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database (MySQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=giftos
DB_USERNAME=root
DB_PASSWORD=secret

# Redis (Recommended for caching and queues)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Cache & Session
CACHE_STORE=redis
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="noreply@giftos.com"
MAIL_FROM_NAME="${APP_NAME}"

# Payment Gateway
STRIPE_KEY=pk_test_your_key
STRIPE_SECRET=sk_test_your_secret

# File Storage
FILESYSTEM_DISK=local
```

## Features

### Authentication & Roles

- Email login with Sanctum API tokens
- Roles: Admin, Manager, Staff, Customer
- 50+ granular permissions
- Email verification and password reset flow

### Products & Inventory

- Full CRUD for products with variants and options
- Product images with automatic thumbnail generation
- Category management with hierarchical structure
- SKU and stock tracking
- Product search and filtering

### Shopping Experience

- Shopping cart with session/database persistence
- Guest and authenticated checkout
- Wishlist functionality
- Product reviews and ratings
- Advanced search with filters

### Orders & Payment

- Complete order lifecycle management
- Stripe and Square payment integration
- Order tracking and status updates
- Email notifications for order events

### Coupons & Discounts

- Percentage and fixed-amount coupons
- Minimum purchase requirements
- Usage limits and expiration dates
- Automatic discount calculation

### Reporting & Analytics

- Sales analytics dashboard
- Product performance metrics
- User behavior tracking
- Export to Excel (orders, customers, products)

### Admin Features

- User management with role assignment
- Product and category management
- Review moderation
- Coupon management
- System health monitoring

### API

- RESTful API for all major entities
- API Resources for consistent responses
- Rate limiting with Redis
- Sanctum token authentication
- Comprehensive error handling

### Real-time Features

- Queue-based email notifications
- Background job processing
- Order status updates
- Low-stock alerts

### UI/UX

- Responsive design with Tailwind CSS
- Mobile-first approach
- Alpine.js for interactivity
- Bootstrap 5 components
- Clean, modern interface

## Technologies Used

- **Backend:** PHP 8.2+, Laravel 12
- **Authentication:** Laravel Sanctum
- **Database:** MySQL 8.0, PostgreSQL 13+ (tested)
- **Cache/Queue:** Redis 6.0+
- **Payment:** Stripe
- **File Upload:** Intervention Image 3.x
- **Frontend:** Blade Templates, Alpine.js, Tailwind CSS, Bootstrap 5
- **Build Tools:** Vite
- **Testing:** PHPUnit 11.5
- **DevOps:** Docker, Docker Compose, Nginx, PHP-FPM
- **Data Export:** Maatwebsite Excel
- **Backup:** Spatie Laravel Backup

## Attributions

- [Laravel Framework](https://laravel.com) - PHP Framework
- [Laravel Sanctum](https://laravel.com/docs/sanctum) - API Authentication
- [Stripe](https://stripe.com) - Payment Processing
- [Intervention Image](http://image.intervention.io) - Image Processing
- [Tailwind CSS](https://tailwindcss.com) - CSS Framework
- [Alpine.js](https://alpinejs.dev) - JavaScript Framework
- [Redis](https://redis.io) - In-Memory Data Store

## Lessons Learned

- **RBAC Implementation:** Building a flexible permission system with Laravel policies and gates
- **API Design:** Creating consistent RESTful endpoints with proper resource transformation
- **Caching Strategy:** Multi-layer caching with Redis for optimal performance
- **Docker Optimization:** Multi-stage builds and layer caching for production deployments
- **Payment Integration:** Handling Stripe webhooks and payment flows
- **Queue Processing:** Background jobs for emails, notifications, and heavy tasks
- **Security:** Implementing security headers, rate limiting, and input validation
- **Testing:** Writing comprehensive feature and unit tests for e-commerce flows

## Next Steps

- [ ] Add real-time inventory updates with Laravel Echo and Pusher
- [ ] Implement customer loyalty points system
- [ ] Add product recommendations based on browsing history
- [ ] Build mobile app with Flutter/React Native using the API
- [ ] Add multi-language support
- [ ] Implement advanced analytics dashboard
- [ ] Add social login (Google, Facebook)
- [ ] Set up CI/CD pipeline with GitHub Actions
- [ ] Implement image CDN integration
- [ ] Add product comparison feature

## Author

**Name:** Mohammad Malabeh  
**GitHub:** [Mohammad-Malaabeh](https://github.com/Mohammad-Malaabeh)  
**Contact:** malaabehmohamed@gmail.com

