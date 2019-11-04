<?php
/**
 * Created by LooL.
 * User: ifehrim@gmail.com
 * Date: 2019-11-04
 * Time: 12:57
 */

use Https\Article;
use Packs\Https\Alm as Http;


Http::model('/blog/@action/*', Article::class);

//Http::post('/blog(/@year(/@month(/@day)))', Article::class);

//Http::get('/blog/*', [Home::class, 'getInfo']);

