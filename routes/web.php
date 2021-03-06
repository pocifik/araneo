<?php

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('proxy',  ['uses' => 'ProxyController@random']);

    $router->get('proxies',  ['uses' => 'ProxyController@list']);
    $router->get('proxies/{id}', ['uses' => 'ProxyController@get']);
});
