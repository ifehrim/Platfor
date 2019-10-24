<?php

use Packs\Http;
use Pages\Admin\Home;
use Pages\Article;

include 'bootstrap.php';

$app = App::init();

Http::model('/blog/@action/*', $app, Article::class);

Http::post('/blog(/@year(/@month(/@day)))', $app, Article::class);

Http::get('/blog/*', $app, [Home::class, 'getInfo']);

$app->execute();














