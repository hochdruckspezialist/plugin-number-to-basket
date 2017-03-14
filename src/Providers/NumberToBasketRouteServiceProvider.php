<?php

namespace NumberToBasket\Providers;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;

class NumberToBasketRouteServiceProvider extends RouteServiceProvider
{
    public function map(Router $router)
    {
        $router->post('number_to_basket', 'NumberToBasketController@add');
    }
}