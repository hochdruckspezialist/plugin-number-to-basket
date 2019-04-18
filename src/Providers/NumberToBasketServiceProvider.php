<?php

namespace NumberToBasket\Providers;

use IO\Helper\ResourceContainer;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\ServiceProvider;
use NumberToBasket\Providers\NumberToBasketRouteServiceProvider;

class NumberToBasketServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->getApplication()->register(NumberToBasketRouteServiceProvider::class);
    }
    
    public function boot(Dispatcher $dispatcher)
    {
        $dispatcher->listen('IO.Resources.Import', function (ResourceContainer $container)
        {
            $container->addScriptTemplate('NumberToBasket::Templates.Scripts');
        }, 0);
    }
}