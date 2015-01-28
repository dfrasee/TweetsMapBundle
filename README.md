
Tweets Map Bundle
=================

This is a test-bundle represent for searching city tweets on google map.

Here is the <a target="_blank" href="http://tweetsmap.punkun-arn.com/web">demo example</a>.

Distribution: Best used with <a target="_blank" href="https://github.com/symfony/symfony-standard">Symfony Standard Edition</a>

Requirements
------------

Symfony <a target="_blank" href="https://github.com/symfony/symfony">(https://github.com/symfony/symfony)</a> obviously.

Installation
------------

### Add the deps for the needed bundles

``` php
[AcmePizzaBundle]
    git=https://github.com/dfrasee/TweetsMapBundle.git
    target=/bundles/Tweets/MapBundle
```
Next, run the vendors script to download the bundles:

``` bash
$ php bin/vendors install
```

### Add to autoload.php

``` php
$loader->registerNamespaces(array(
    'Tweets'             => __DIR__.'/../vendor/bundles',
    // ...
```

### Register TweetsMapBundle to Kernel

``` php
<?php

    # app/AppKernel.php
    //...
    $bundles = array(
        //...
        new Tweets\MapBundle\TweetsMapBundle(),
    );
    //...
```

### Create database and schema

``` bash
$ php app/console doctrine:database:create
$ php app/console doctrine:schema:create
```

### Enable routing configuration

``` yaml
# app/config/routing.yml
TweetsMapBundle:
    resource: "@TweetsMapBundle/Controller/"
    type:     annotation
    prefix:   /tweets-map
```

### Refresh asset folder

``` bash
$ php app/console assets:install web/
```

### Webserver configuration
If you start this as a new site you might have to setup your webserver for your site.
[Web server configuration](http://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html)

Usage
-----

Go to `yoursite/tweets-map/tweets/` and start seaching tweets.

Testing
-------

The testing document will be update soon.
