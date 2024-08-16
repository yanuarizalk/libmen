# Libmen - Library management API using Lumen

Example restful api using lumen, mysql & redis stack. Providing simple yet optimized logic, including caching & invalidation, million data query sample & few testings.

## Prerequisites
- PHP 8.x
- Composer
- Mysql
- Redis
- PDO / Mysql extension
- Mbstring extension
- Xml extension
- Docker / Valet / Homestead

Note: The markdown only show you in docker way

## Setup
1. Run `composer install`
2. Copy `.env.example` file, name it to `.env`
3. `docker-compose up -d` to start the db & cache instances
4. Execute `php artisan migrate` along with `--seed` option if you want to create million data books

## Run
1. `docker-compose up -d` to run the db & cache instances
2. Serve the app by executing `php -S localhost:8000 -t public`

## Running the tests
The tests use different environment database, so we need to create a new one named `libtest` with same user granted, which can configured on [phpunit.xml](phpunit.xml).

After that you can run `composer exec phpunit`.

## API Documentation
- [Local documentation](http://localhost:8000/api/documentation)
- [Postman](https://www.postman.com/blue-crescent-479369/workspace/yanuar-s-space/collection/11658621-7387e136-f0b0-48f2-a7a3-108909d66f17?action=share&creator=11658621)

## Remarks

The choice to use said framework over the others because it's cover the needs with ease of useness, not to mention it's continuality.
As for optimizing key, there'll be need enhancing on each side from code, database & infras itself.
- Use indexing with correct order, put most searched field on top of each other.
- Restrict the data returned with pagination / infinity scroll method.
- Make use of asynchronous / paralleling simulataneous process rather than synchronous procedural tasking.
- Apply caching to prevent consuming other's resource, on the code showed you how invalidating & caching work on application-level caching. Yet there's more [simple library](https://github.com/mikebronner/laravel-model-caching) out there that can be applied.
- Scale out the system when there's so much user & data to handle, we can apply load balancing along with replication.

I've made some performance test having 2 million data & 1 relational against 5-10 simultaneous user, you can see the [report here](benchmark.pdf). It could be better if it ran on web server like nginx or apache instead of php cli because of the way it handle multiple request without blocking threads / processes.