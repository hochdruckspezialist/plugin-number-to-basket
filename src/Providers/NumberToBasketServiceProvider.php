<?php

namespace NumberToBasket\Providers;

use Plenty\Plugin\ServiceProvider;
use NumberToBasket\Providers\NumberToBasketRouteServiceProvider;

class NumberToBasketServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->getApplication()->register(NumberToBasketRouteServiceProvider::class);
    }
}