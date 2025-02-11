# Laravel 11 News Aggregator API Directory Structure

```
news-aggregator-api/
├── .github/
│   └── workflows/                    # CI/CD workflows
│       ├── testing.yml
│       └── deployment.yml
├── app/                             # Application Core
│   ├── Actions/                     # Action classes (new in Laravel 11)
│   │   ├── Auth/
│   │   │   ├── RegisterUser.php
│   │   │   └── ResetPassword.php
│   │   └── Articles/
│   │       ├── FetchArticles.php
│   │       └── ProcessArticles.php
│   ├── Data/                        # Data objects (new in Laravel 11)
│   │   ├── ArticleData.php
│   │   ├── UserData.php
│   │   └── PreferenceData.php
│   ├── Exceptions/
│   │   ├── Handler.php
│   │   └── NewsApi/
│   │       ├── RateLimitException.php
│   │       └── FetchException.php
│   ├── Http/
│   │   ├── Api/                     # API Controllers (flattened structure)
│   │   │   ├── ArticleController.php
│   │   │   ├── AuthController.php
│   │   │   └── PreferenceController.php
│   │   ├── Middleware/
│   │   │   ├── RateLimit.php
│   │   │   └── ValidateApiToken.php
│   │   └── Requests/               # Form requests
│   │       ├── StoreArticleRequest.php
│   │       └── UpdatePreferenceRequest.php
│   ├── Models/
│   │   ├── Article.php
│   │   ├── User.php
│   │   ├── NewsSource.php
│   │   └── Preference.php
│   └── Services/                   # Business logic
│       ├── News/
│       │   ├── NewsApiService.php
│       │   ├── GuardianService.php
│       │   └── NytService.php
│       └── Cache/
│           └── ArticleCacheService.php
├── bootstrap/
│   ├── app.php                     # Application bootstrap
│   └── cache/                      # Framework cache
├── config/                         # Configuration files
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── cors.php
│   ├── database.php
│   ├── logging.php
│   ├── news-sources.php
│   ├── sanctum.php
│   ├── services.php
│   └── queue.php
├── database/
│   ├── factories/                  # Model factories
│   │   ├── ArticleFactory.php
│   │   └── UserFactory.php
│   ├── migrations/
│   │   ├── 2024_01_01_create_users_table.php
│   │   ├── 2024_01_02_create_articles_table.php
│   │   └── 2024_01_03_create_preferences_table.php
│   └── seeders/
│       └── DatabaseSeeder.php
├── docker/                         # Docker configuration
│   ├── nginx/
│   │   └── nginx.conf
│   ├── php/
│   │   └── Dockerfile
│   └── redis/
│       └── redis.conf
├── lang/                          # Localization files
│   └── en/
│       └── api.php
├── public/                        # Public directory
│   ├── index.php                 # Main entry point
│   ├── .htaccess                 # Apache configuration
│   ├── robots.txt                # Search engine rules
│   ├── favicon.ico               # Website icon
│   └── build/                    # Compiled assets
├── resources/
│   ├── views/                    # Views (for emails)
│   │   └── emails/
│   │       ├── password-reset.blade.php
│   │       └── welcome.blade.php
│   └── lang/                     # Language files
├── routes/                        # Routes directory
│   ├── api.php                    # API routes
│   ├── channels.php               # Broadcasting channels
│   └── console.php                # Console routes
├── storage/                       # Storage directory
│   ├── app/
│   │   └── public/
│   ├── framework/
│   │   ├── cache/
│   │   ├── sessions/
│   │   └── views/
│   └── logs/
├── tests/                         # Test directory
│   ├── Feature/
│   │   ├── Api/
│   │   │   ├── ArticleTest.php
│   │   │   └── AuthTest.php
│   │   └── Services/
│   │       └── NewsApiTest.php
│   ├── Unit/
│   │   ├── Actions/
│   │   │   └── FetchArticlesTest.php
│   │   └── Services/
│   │       └── ArticleCacheTest.php
│   └── TestCase.php
├── vendor/                        # Composer dependencies
├── .dockerignore                  # Docker ignore file
├── .env                          # Environment file
├── .env.example                  # Environment example file
├── .gitignore                    # Git ignore file
├── artisan                       # Laravel command line tool
├── composer.json                 # Composer dependencies file
├── composer.lock                 # Composer lock file
├── docker-compose.yml            # Docker compose configuration
├── package.json                  # NPM dependencies file
├── phpunit.xml                   # PHPUnit configuration
└── README.md                     # Project documentation
```

## Directory Structure Notes

1. **Application Core (`app/`)**
   - `Actions/`: Single-purpose business logic classes
   - `Data/`: Type-safe data objects
   - `Http/Api/`: API controllers with flat structure
   - `Services/`: External service integrations

2. **Configuration (`config/`)**
   - Separate config files for each major component
   - Custom news-sources configuration

3. **Docker Setup (`docker/`)**
   - Separate configurations for each service
   - Optimized for development and production

4. **Public Directory (`public/`)**
   - Web server document root
   - Application entry point
   - Static files and assets

5. **Testing (`tests/`)**
   - Feature tests for API endpoints
   - Unit tests for business logic
   - Service integration tests

6. **Storage (`storage/`)**
   - Application storage
   - Framework cache
   - Logs

This structure follows Laravel 11's conventions while accommodating the specific needs of a news aggregator API. It's organized for optimal performance, maintainability, and scalability.