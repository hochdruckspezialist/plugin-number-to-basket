<?php

namespace NumberToBasket\Widgets;

use NumberToBasket\Widgets\NumberToBasketWidget;

class WidgetCollection
{
    const MAIN_WIDGETS = [
        NumberToBasketWidget::class
    ];

    public static function all()
    {
        return array_merge(
            self::MAIN_WIDGETS
        );
    }

}
