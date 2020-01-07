<?php

namespace NumberToBasket\Containers;

use IO\Services\UrlBuilder\UrlQuery;
use Plenty\Plugin\Templates\Twig;

class NumberToBasketContainer
{
    public function call(Twig $twig):string
    {
        /** @var UrlQuery $query */
        $query = pluginApp(UrlQuery::class, ['path' => 'number_to_basket']);
        return $twig->render('NumberToBasket::Templates.NumberToBasket', ['url' => $query->toRelativeUrl()]);
    }
}
