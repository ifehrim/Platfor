# Platfor MicroServices Framework [PHP]

[![Build Status](https://travis-ci.org/ifehrim/Platfor.svg?branch=master)](https://travis-ci.org/ifehrim/Platfor)
[![Coverage Status](https://coveralls.io/repos/github/ifehrim/Platfor/badge.svg?branch=master)](https://coveralls.io/github/ifehrim/Platfor?branch=master)

Platfor is a PHP and IPO based micro framework that helps you quickly write simple yet powerful micro services applications and APIs.

### Hello World Tutorial For [Index.php]

Instantiate a Platfor application:
```php

$app = App::init();

Http::get('/blog/*', $app, [Home::class, 'getInfo']);

$app->execute();
```

### Hello World Tutorial For [Home.php]

Instantiate a Platfor application:
```php
class Home
{
    use _Frame;
    
    //middileware requirments if You need
    static $__before=[
        Auth::class
    ];

    public static function getInfo(App $app){

        $app->take('Home',[
            'name'=>'Title',
            'page'=>'Hello World!',
            'date'=>time(),
        ]);
        
        return $app;
    }
}
```
Response For That:
```json
{
  "edit": "ok",
  "Home": {
    "name": "Title",
    "page": "Hello World!",
    "date": 1571915345
  }
}
```


## Features

* Micro Services
    * Standard IPO architecture
    * Micro services architecture
* Powerful router
    * Standard and custom HTTP methods
    * Route parameters with wildcards and conditions
    * Route redirect, halt, and pass
    * Route middleware
* Flash messages
    * Message Taker
    * Multi url request once
* HTTP caching
* Middleware and hook architecture
* Simple configuration

## Getting Started

### Install

You may install the Platfor Framework with Git (recommended) or manually.

Git:
```bash
$ git clone https://github.com/ifehrim/Platfor.git
```

Composer:
```bash
$ composer require platfor/ifehrim
```


You may quickly test this using the built-in PHP server:
```bash
$ php -S localhost:8000 -t ./
```

Going to http://localhost:8000/blog/edit/HelloWorld?token=123 will now display "Hello, world".


### System Requirements

You need **PHP >= 5.3.0**. If you use encrypted cookies, you'll also need the `mcrypt` extension.



### Setup your web server

#### Apache

Ensure the `.htaccess` and `index.php` files are in the same public-accessible directory. The `.htaccess` file
should contain this code:
```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [QSA,L]
```
Additionally, make sure your virtual host is configured with the AllowOverride option so that the .htaccess rewrite rules can be used:
```
AllowOverride All
```
#### Nginx

The nginx configuration file should contain this code (along with other settings you may need) in your `location` block:
```
try_files $uri $uri/ /index.php?$args;
```
This assumes that Platfor's `index.php` is in the root folder of your project (www root).

#### HipHop Virtual Machine for PHP

Your HipHop Virtual Machine configuration file should contain this code (along with other settings you may need).
Be sure you change the `ServerRoot` setting to point to your Platfor app's document root directory.
```
Server {
    SourceRoot = /path/to/public/directory
}

ServerVariables {
    SCRIPT_NAME = /index.php
}

VirtualHost {
    * {
        Pattern = .*
        RewriteRules {
                * {
                        pattern = ^(.*)$
                        to = index.php/$1
                        qsa = true
                }
        }
    }
}
```
#### lighttpd ####

Your lighttpd configuration file should contain this code (along with other settings you may need). This code requires
lighttpd >= 1.4.24.
```
url.rewrite-if-not-file = ("(.*)" => "/index.php/$0")
```
This assumes that Platfor's `index.php` is in the root folder of your project (www root).

#### IIS

Ensure the `Web.config` and `index.php` files are in the same public-accessible directory. The `Web.config` file should contain this code:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <rewrite>
            <rules>
                <rule name="Platfor" patternSyntax="Wildcard">
                    <match url="*" />
                    <conditions>
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="index.php" />
                </rule>
            </rules>
        </rewrite>
    </system.webServer>
</configuration>
```
#### Google App Engine

Two steps are required to successfully run your Platfor application on Google App Engine. First, ensure the `app.yaml` file includes a default handler to `index.php`:
```
application: your-app-name
version: 1
runtime: php
api_version: 1

handlers:
# ...
- url: /.*
  script: public_html/index.php
```
Next, edit your `index.php` file so Platfor knows about the incoming URI:
```php
$app = new Platfor();

Http::post('/blog(/@year(/@month(/@day)))', $app, Article::class);

// ...
$app->run();
```
   
## Documentation

updating ...

## How to Contribute


*NOTE: We are only accepting security fixes for Platfor 2 (master branch). All development is concentrated on Platfor 3 which is on the develop branch.*


### Pull Requests

1. Fork the Platfor Framework repository
2. Create a new branch for each feature or improvement
3. Send a pull request from each feature branch to the **develop** branch

It is very important to separate new features or improvements into separate feature branches, and to send a pull
request for each branch. This allows me to review and pull in new features or improvements individually.

### Style Guide

All pull requests must adhere to the [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) standard.

### Unit Testing

All pull requests must be accompanied by passing unit tests and complete code coverage. The Platfor Framework uses
`phpunit` for testing.

[Learn about PHPUnit](https://github.com/sebastianbergmann/phpunit/)

## Community

### Forum and Knowledgeable

Visit Platfor's official forum and knowledge base at <Platforframework> where you can find announcements,
chat with fellow Platfor users, ask questions, help others, or show off your cool Platfor Framework apps.

### Twitter

updating...

## Author

The Platfor Framework is created and maintained by [Alm.Pazel] . Alm is a senior
backend developer at [Lool Ltd Shanghai]. Alm also a student at SISU.

PHP programmers to best practices and good information.

## License

The Platfor Framework is released under the MIT public license.

<https://github.com/ifehrim/Platfor/blob/master/LICENSE>
