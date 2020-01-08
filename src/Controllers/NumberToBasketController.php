<?php

namespace NumberToBasket\Controllers;

use IO\Services\BasketService;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Modules\Cloud\ElasticSearch\Lib\ElasticSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchSearchRepositoryContract;
use Plenty\Modules\Item\Search\Filter\ClientFilter;
use Plenty\Modules\Item\Search\Filter\VariationBaseFilter;
use Plenty\Plugin\Application;

class NumberToBasketController extends Controller
{
    /**
     * @param Request $request
     * @param BasketService $basketService
     * @return array
     * @throws \Exception
     */
    public function add(Request $request, BasketService $basketService)
    {
        /** @var ConfigRepository $configRepo */
        $configRepo  = pluginApp(ConfigRepository::class);
        $configValue = (int)$configRepo->get('NumberToBasket.number.item_number', 0);
        
        switch ($configValue) {
            case 0:
                $foundVariationId = $this->findVariation($request->get('number', 0));
                break;
            case 1:
                $foundVariationId = $this->findVariation($request->get('number', ''), false);
                break;
            case 2:
                $foundVariationId = $this->findVariation($request->get('number', ''), false);
                if ($foundVariationId <= 0) {
                    // Backup check, try parsing the number as a variationId
                    $foundVariationId = $this->findVariation($request->get('number', 0));
                }
                break;
            default:
                $foundVariationId = 0;
                break;
        }
        
        if ($foundVariationId <= 0) {
            // Still no results, inform user about wrong id
            throw new \Exception("Nothing found.");
        }
        
        $data['variationId'] = $foundVariationId;
        $data['quantity']    = $request->get('quantity', 1);
        
        $basketService->addBasketItem($data);
        
        return [
            "basketItems" => $basketService->getBasketItemsForTemplate(),
            "basket" => $basketService->getBasketForTemplate()
        ];
    }
    
    private function findVariation($number, $byId = true)
    {
        $variationId = 0;
        
        if (strlen($number)) {
            $app = pluginApp(Application::class);
            
            $documentProcessor = pluginApp(DocumentProcessor::class);
            $documentSearch    = pluginApp(DocumentSearch::class, [$documentProcessor]);
            
            $elasticSearchRepo = pluginApp(VariationElasticSearchSearchRepositoryContract::class);
            $elasticSearchRepo->addSearch($documentSearch);
            
            $clientFilter = pluginApp(ClientFilter::class);
            $clientFilter->isVisibleForClient($app->getPlentyId());
            
            $variationFilter = pluginApp(VariationBaseFilter::class);
            $variationFilter->isActive();
            if ($byId) {
                $variationFilter->hasId($number);
            } else {
                $variationFilter->hasNumber($number, ElasticSearch::SEARCH_TYPE_EXACT);
            }
            
            
            $documentSearch
                ->addFilter($clientFilter)
                ->addFilter($variationFilter);
            
            $result = $elasticSearchRepo->execute();
            
            if (count($result['documents'])) {
                $variationId = $result['documents'][0]['data']['variation']['id'];
            }
        }
        
        return $variationId;
    }
}
