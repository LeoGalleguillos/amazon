<?php
namespace LeoGalleguillos\Amazon\Model\Factory;

use LeoGalleguillos\Amazon\Model\Entity as AmazonEntity;
use LeoGalleguillos\Amazon\Model\Table as AmazonTable;

class ProductGroup
{
    public function __construct(
        AmazonTable\ProductGroup $productGroupTable
    ) {
        $this->productGroupTable = $productGroupTable;
    }

    public function buildFromProductGroupId(
        int $productGroupId
    ) : AmazonEntity\ProductGroup {
        $arrayObject = $this->productGroupTable->selectWhereProductGroupId(
            $productGroupId
        );

        $productGroupEntity                 = new AmazonEntity\ProductGroup();
        $productGroupEntity->productGroupId = $arrayObject['product_group_id'] ?? null;
        $productGroupEntity->name           = $arrayObject['name'] ?? null;
        $productGroupEntity->slug           = $arrayObject['slug'] ?? null;
        $productGroupEntity->searchTable    = $arrayObject['search_table'] ?? null;

        return $productGroupEntity;
    }

    public function buildFromName(string $name)
    {
        $arrayObject = $this->productGroupTable->selectWhereName(
            $name
        );

        $productGroupEntity                 = new AmazonEntity\ProductGroup();
        $productGroupEntity->productGroupId = $arrayObject['product_group_id'] ?? null;
        $productGroupEntity->name           = $arrayObject['name'] ?? null;
        $productGroupEntity->slug           = $arrayObject['slug'] ?? null;
        $productGroupEntity->searchTable    = $arrayObject['search_table'] ?? null;

        return $productGroupEntity;
    }
}
