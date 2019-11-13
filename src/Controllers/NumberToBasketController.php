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
        $configRepo = pluginApp(ConfigRepository::class);
        $configValue = (int)$configRepo->get('NumberToBasket.number.item_number', 0);
        
        switch($configValue)
        {
            case 0:
                $foundVariationId = $this->findVariationById($request->get('number', 0));
                break;
            case 1:
                $foundVariationId = $this->findVariationByNumber($request->get('number', ''));
                break;
            case 2:
                $foundVariationId = $this->findVariationByNumber($request->get('number', ''));
                if($foundVariationId <= 0)
                {
                    // Backup check, try parsing the number as a variationId
                    $foundVariationId = $this->findVariationById($request->get('number', 0));
                }
                break;
            default:
                $foundVariationId = 0;
                break;
        }

        if($foundVariationId <= 0)
        {
            // Still no results, inform user about wrong id
            throw new \Exception("Nothing found.");
        }

        $data['variationId'] = $foundVariationId;
        $data['quantity'] = $request->get('quantity', 1);

        $basketService->addBasketItem($data);

        return [
            "basketItems" => $basketService->getBasketItemsForTemplate(),
            "basket" => $basketService->getBasketForTemplate()
        ];
    }

    /**
     * Use ElasticSearch to find a variation by it's variation.number
     * Returns the found variations id, or 0 for not found
     *
     * @param $number
     * @return int
     */
    private function findVariationByNumber($number)
    {
        $variationId = 0;

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

    /**
     * @param $id
     * @return int
     */
    private function findVariationById($id)
    {
        $variationId = 0;

        if($id > 0)
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
            $variationFilter->hasId($id);

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
