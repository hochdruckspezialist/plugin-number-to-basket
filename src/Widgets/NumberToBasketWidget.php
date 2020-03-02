<?php

namespace NumberToBasket\Widgets;

use Ceres\Widgets\Helper\BaseWidget;
use Ceres\Widgets\Helper\Factories\WidgetSettingsFactory;
use Ceres\Widgets\Helper\WidgetCategories;
use Ceres\Widgets\Helper\Factories\WidgetDataFactory;
use Ceres\Widgets\Helper\WidgetTypes;

class NumberToBasketWidget extends BaseWidget
{
    protected $template = 'NumberToBasket::Widgets.NumberToBasketWidget';

    public function getData()
    {
      return WidgetDataFactory::make("NumberToBasket::NumberToBasketWidget")
        ->withLabel("QuickAdd.widgetLabel")
        ->withType(WidgetTypes::DEFAULT)
        ->withCategory(WidgetCategories::BASKET)
        ->withPosition(200)
        ->toArray();
    }

    public function getSettings()
    {
      /** @var WidgetSettingsFactory $settings */
      $settings = pluginApp(WidgetSettingsFactory::class);
      $settings->createCustomClass();
      $settings->createAppearance();
      $settings->createSpacing(true, true);

      return $settings->toArray();
    }
}
