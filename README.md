## About NOWrss-laravel

NOWnews 外送系統

## COMMAND LINE

### HOW TO START

```
composer install
composer update
```

### NEEDED TOOLS

```
apt-get install sqlite3
touch database/database.sqlite
```

### CONFIG SETTING

```
cp .env.example .env && vim .env
```

#### .env
```
...
DB_CONNECTION=sqlite
DB_HOST=127.0.0.1
DB_PORT=3306
#DB_DATABASE=homestead
DB_USERNAME=homestead
DB_PASSWORD=secret
...
```

### RUN SQL MIGRATION

```
php artison migrate
```

### START
for localhost : 
```
php artison serve
```
for production : vim /etc/nginx/sites-available/default
```
...
root /home/developer/NOWrss-laravel/public;
...
```
