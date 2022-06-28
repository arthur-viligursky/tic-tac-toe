## Project configuration
```shell
cp .env.sample .env
cp www/.env.sample www/.env
npm install
npm run dev
docker-compose up
```

Then run the following inside the php container
```shell
composer install
php artisan key:generate
php artisan migrate
```

By default the application will be accessible at http://127.0.0.1:4000/
