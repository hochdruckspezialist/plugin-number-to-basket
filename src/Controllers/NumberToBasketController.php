<?php

namespace NumberToBasket\Controllers;

use IO\Services\BasketService;
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
     */
    public function add(Request $request, BasketService $basketService)
    {
        $data['variationId'] = $this->findItemByNumber($request->get('number', ''));;
        $data['quantity'] = $request->get('quantity', 1);

        $basketService->addBasketItem($data);

        return [
            "basketItems" => $basketService->getBasketItemsForTemplate(),
            "basket" => $basketService->getBasketForTemplate()
        ];
    }

    /**
     * @param $number
     * @return mixed
     */
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
