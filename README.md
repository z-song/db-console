
#DB Console

DB Console is a database console with support for `Mysql`, `Sqlite`, `Mongodb` and `Redis`. 

## Screenshots
Mysql:

![e1135cfc-c117-4ded-a7f3-e8b186dc43a2](https://cloud.githubusercontent.com/assets/1479100/11388570/b1ac411e-9372-11e5-9542-f9b8f3990145.png)

MongoDB:

![fbafd0a2-f44c-4f53-b1fd-c1cc5cc62f26](https://cloud.githubusercontent.com/assets/1479100/11388615/2f7f6aa8-9373-11e5-9fee-77c363517cf6.png)

Redis:

![690620bb-89a4-4656-a765-a963a1ca044c](https://cloud.githubusercontent.com/assets/1479100/11388578/c63e0900-9372-11e5-92a1-ae10a733df04.png)

## Installation

```sh
composer require encore/dbconsole --dev
```

## Configuration

See [database.php](https://github.com/z-song/db-console/blob/master/config/database.php).

## Usage

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Encore\Dbconsole\Shell;

$shell = new Shell(__DIR__ . '/config/database.php');

// Also you can specify a connection in your configuration.
//$shell->setConnection('redis');

$shell->run();

```

And run this script in your console.

## Work with Laravel

When use in `Laravel`, it will use the database configuration in application.

Add service provider to `config/app.php` in `providers` section:

```php
Encore\Dbconsole\Providers\LaravelServiceProvider::class
```

then run `DB Console` with:

```sh
php artisan db:console
```