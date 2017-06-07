<?php

namespace NumberToBasket\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Modules\Basket\Models\BasketItem;
use Plenty\Modules\Basket\Contracts\BasketItemRepositoryContract;

use Plenty\Modules\Cloud\ElasticSearch\Lib\ElasticSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\IncludeSource;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchSearchRepositoryContract;
use Plenty\Modules\Item\Search\Filter\ClientFilter;
use Plenty\Modules\Item\Search\Filter\SearchFilter;
use Plenty\Modules\Item\Search\Filter\VariationBaseFilter;
use Plenty\Plugin\Application;

class NumberToBasketController extends Controller
{
    public function add(Request $request, BasketItemRepositoryContract $basketItemRepository)
    {
        $data['variationId'] = $this->findItemByNumber($request->get('number', ''));;
        $data['quantity'] = $request->get('quantity', 1);
        
        $basketItem = $basketItemRepository->findExistingOneByData($data);
        if($basketItem instanceof BasketItem)
        {
            $data['id']       = $basketItem->id;
            $data['quantity'] = (int)$data['quantity'] + $basketItem->quantity;
            $basketItemRepository->updateBasketItem($basketItem->id, $data);
        }
        else
        {
            $basketItemRepository->addBasketItem($data);
        }
        
        return '';
    }
    
    private function findItemByNumber($number)
    {
        $variationId = $number;
        
        if(strlen($number))
        {
            $app = pluginApp(Application::class);
            
            $documentProcessor = pluginApp(DocumentProcessor::class);
            $documentSearch = pluginApp(DocumentSearch::class, [$documentProcessor]);
        
            $elasticSearchRepo = pluginApp(VariationElasticSearchSearchRepositoryContract::class);
            $elasticSearchRepo->addSearch($documentSearch);
        
            $clientFilter = pluginApp(ClientFilter::class);
            $clientFilter->isVisibleForClient($app->getPlentyId());
        
            $variationFilter = pluginApp(VariationBaseFilter::class);
            $variationFilter->isActive();
            $variationFilter->hasNumber($number, ElasticSearch::SEARCH_TYPE_EXACT);
        
            $documentSearch
                ->addFilter($clientFilter)
                ->addFilter($variationFilter);
        
            $result = $elasticSearchRepo->execute();
            
            if(count($result['documents']))
            {
                $variationId = $result['documents'][0]['data']['variation']['id'];
            }
        }
        
        return $variationId;
    }
    
}