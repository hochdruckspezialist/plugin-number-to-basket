<?php

namespace NumberToBasket\Containers;

use Plenty\Plugin\Templates\Twig;

class NumberToBasketContainer
{
    public function call(Twig $twig):string
    {
        return $twig->render('NumberToBasket::Templates.NumberToBasket');
    }
}