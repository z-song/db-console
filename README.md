
#DB Console

DB Console is a database console with support for `Mysql`, `Sqlite`, `Mongodb` and `Redis`. 

## Screenshots
Mysql:

![ff11be54-5cc8-4790-876b-a4950d00fe32](https://cloud.githubusercontent.com/assets/1479100/11439445/715a479e-9536-11e5-9c40-35fb13160b38.png)

MongoDB:

![9e0543f3-c44b-44a9-9419-9d55da21c540](https://cloud.githubusercontent.com/assets/1479100/11439473/9b2c102a-9536-11e5-8ae6-c8a09fc54a3f.png)

Redis:

![51a07cbe-089e-428b-a1a3-01bc47da4207](https://cloud.githubusercontent.com/assets/1479100/11439470/9838351a-9536-11e5-9344-e4df446575b9.png)

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

// Also you can specify a connection which in your configuration.
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
