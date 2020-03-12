<?php
namespace LeoGalleguillos\Amazon\Model\Service\Product\Products;

use Generator;
use LeoGalleguillos\Amazon\Model\Entity as AmazonEntity;
use LeoGalleguillos\Amazon\Model\Factory as AmazonFactory;
use LeoGalleguillos\Amazon\Model\Table as AmazonTable;
use LeoGalleguillos\String\Model\Service as StringService;
use Zend\Db\Adapter\Exception\InvalidQueryException;

class Similar
{
    public function __construct(
        AmazonFactory\Product $productFactory,
        AmazonTable\Product\ProductId $productIdTable,
        AmazonTable\Product\Title $titleTable,
        StringService\KeepFirstWords $keepFirstWordsService
    ) {
        $this->productFactory        = $productFactory;
        $this->productIdTable        = $productIdTable;
        $this->titleTable            = $titleTable;
        $this->keepFirstWordsService = $keepFirstWordsService;
    }

    public function getSimilarProducts(AmazonEntity\Product $productEntity): Generator
    {
        $query = $this->keepFirstWordsService->keepFirstWords(
            $productEntity->getTitle(),
            3
        );
        $query = str_replace('"', '', $query);

        try {
            $result = $this->titleTable->selectProductIdWhereMatchAgainst($query);
        } catch (InvalidQueryException $invalidQueryException) {
            return;
        }

        $productIds = [];
        foreach ($result as $array) {
            $productIds[] = $array['product_id'];
        }

        if (empty($productIds)) {
            return;
        }

        $result = $this->productIdTable->selectWhereProductIdIn($productIds);
        foreach ($result as $array) {
            if ($array['product_id'] == $productEntity->getProductId()) {
                continue;
            }
            yield $this->productFactory->buildFromArray($array);
        }
    }
}