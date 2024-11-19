
# News Aggregator API

## Setup Instructions

### 1. Clone the Repository
```bash
git clone https://github.com/mhsun/news-aggregator-API.git
cd news-aggregator-API
```

### 2. Set Up Environment
Copy the `.env.example` file and configure the necessary environment variables:
```bash
cp .env.example .env
```
Set the following variables:
- **Database**: Configure `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`.
- **News API Keys**: Provide API keys for your selected external news APIs.
```env
NEWSAPI_KEY=your_newsapi_key
THE_GUARDIAN_KEY=your_guardian_key
NY_TIMES_KEY=your_nytimes_key
```

### 3. Install Dependencies
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

### 4. Set Up Laravel Sail (Docker)
Ensure Docker is installed and running. Start the development environment:
```bash
./vendor/bin/sail up -d
```

### 5. Run Migrations and Seeders
```bash
./vendor/bin/sail artisan migrate --seed
```

### 6. Generate Application Key
```bash
./vendor/bin/sail artisan key:generate
```

## How to Run the Application
Start the Laravel server using Sail or PHP's built-in server:
```bash
./vendor/bin/sail up
```
The application will be accessible at: [http://localhost](http://localhost)

## Testing the Application

### 1. Run Unit and Feature Tests
Execute the test suite using Laravel's testing tools:
```bash
./vendor/bin/sail artisan test
```

### 2. Testing Manual Endpoints
You can test the API using tools like Postman or cURL. Alternatively, visit the API documentation (see below).

## Visit API Documentation
Swagger/OpenAPI documentation is available at:
```arduino
http://localhost/api/docs
```
Use this for interactive testing and understanding all available endpoints.

## Assumptions and Considerations

### Data Aggregation
- Data from external APIs are fetched daily using Laravel's scheduler. This can be adjusted as needed.
If we opt to fetch data in real-time, we can use a queue system to handle this. For the simplicity of this project, 
we fetch data daily as the third party news providers has a restriction on rate limiting and searching data in a broader
range. If we have a clear idea about the data size, we can use multi-threading/pool to fetch data from multiple 
sources to reduce the time taken to fetch data for larger payload. And to avoid rate limiting, we can use a delay 
between each request. In this project, we've avoided such complexity due to the simplicity of the project 
and not having enough information.


- Only locally stored data is used for filtering and searching.

### Caching
- Articles are cached for 1 hour. This can be adjusted. We're caching any kind of data that is fetched from the database
in any order/combination. We can cache the data for a longer time if the data is not updated frequently.


- Cache is automatically cleared when articles are modified.


- To clear all application cache:

```bash
./vendor/bin/sail artisan cache:clear
```

### Scheduler
To run the scheduling, we need to run the following command:
```bash
./vendor/bin/sail artisan schedule:run
```

### API Rate Limiting
- Rate limiting is in place to prevent abuse of the API. The rate limit is set to 60 requests per minute.

### Error Handling
- Errors are handled gracefully with appropriate HTTP status codes and error messages.
- Validation errors are returned with detailed messages for each field.

### Command
- A custom artisan command is available to fetch data from external APIs manually:
```bash
./vendor/bin/sail artisan articles:fetch
```
Which takes an optional `--keyword` to select the keyword to search for. This can be used to fetch data for a specific keyword.
In this project we've used it to minimize the data fetched from the external sources due to rate limiting of a third party API.
By default, it fetches data for the keyword "technology".

### Security
- All endpoints are protected with Sanctum tokens where necessary.
- Input validations and protection against common vulnerabilities (SQL injection, XSS) are in place.

### Environment
- The application is built assuming local development using Docker.
- Production setup requires configuring cache drivers (Redis) and queue drivers for optimal performance.

## Deployment Notes
- Ensure your `.env` file is properly configured for production, including `APP_ENV=production` and `APP_DEBUG=false`.
- Use `php artisan config:cache` and `php artisan route:cache` to optimize performance.
- Set up a cron job for Laravel's scheduler to ensure data aggregation runs regularly:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```
```
