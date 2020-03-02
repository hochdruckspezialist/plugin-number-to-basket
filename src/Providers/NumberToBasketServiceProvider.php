<?php

namespace NumberToBasket\Providers;

use IO\Helper\ResourceContainer;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\ServiceProvider;
use NumberToBasket\Providers\NumberToBasketRouteServiceProvider;
use NumberToBasket\Widgets\WidgetCollection;
use Plenty\Modules\ShopBuilder\Contracts\ContentWidgetRepositoryContract;

class NumberToBasketServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->getApplication()->register(NumberToBasketRouteServiceProvider::class);
    }

    public function boot(Dispatcher $dispatcher)
    {

        // register shop builder widgets
        /** @var ContentWidgetRepositoryContract $widgetRepository */
        $widgetRepository = pluginApp(ContentWidgetRepositoryContract::class);
        $widgetClasses = WidgetCollection::all();
        foreach ($widgetClasses as $widgetClass) {
            $widgetRepository->registerWidget($widgetClass);
        }

        $dispatcher->listen('IO.Resources.Import', function (ResourceContainer $container)
        {
            $container->addScriptTemplate('NumberToBasket::Templates.Scripts');
        }, 0);
    }
}
