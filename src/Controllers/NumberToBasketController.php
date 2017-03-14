<?php

namespace NumberToBasket\Controllers;

use Plenty\Modules\Basket\Contracts\BasketItemRepositoryContract;
use Plenty\Plugin\Http\Request;

class NumberToBasketController
{
    public function add(Request $request, BasketItemRepositoryContract $basketItemRepository)
    {
        $data = [];
        
        $basketItem = $basketItemRepository->findExistingOneByData($data);
        if($basketItem instanceof BasketItem)
        {
            $data['id']       = $basketItem->id;
            $data['quantity'] = (int)$data['quantity'] + $basketItem->quantity;
            $basketItemRepository->updateBasketItem($basketItem->id, $data);
        }
        else
        {
            $data['variation_id'] = $request->get('number');
            $data['quantity'] = $request->get('quantity');
            $basketItemRepository->addBasketItem($data);
        }
    
        return $this->getBasketItemsForTemplate();
    }
}