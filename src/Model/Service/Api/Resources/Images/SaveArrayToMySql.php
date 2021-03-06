<?php
namespace LeoGalleguillos\Amazon\Model\Service\Api\Resources\Images;

use Laminas\Db\Adapter\Driver\Pdo\Connection;
use LeoGalleguillos\Amazon\Model\Service as AmazonService;
use LeoGalleguillos\Amazon\Model\Table as AmazonTable;

class SaveArrayToMySql
{
    public function __construct(
        AmazonTable\ProductImage $productImageTable,
        Connection $connection
    ) {
        $this->productImageTable = $productImageTable;
        $this->connection        = $connection;
    }

    public function saveArrayToMySql(
        array $imagesArray,
        int $productId
    ) {
        $this->connection->beginTransaction();

        $this->productImageTable->deleteWhereProductId($productId);

        if (isset($imagesArray['Primary'])) {
            $this->productImageTable->insertIgnore(
                $productId,
                'primary',
                $imagesArray['Primary']['Large']['URL'],
                $imagesArray['Primary']['Large']['Width'],
                $imagesArray['Primary']['Large']['Height']
            );
        }

        if (isset($imagesArray['Variants'])) {
            foreach ($imagesArray['Variants'] as $imageArray) {
                $this->productImageTable->insertIgnore(
                    $productId,
                    'variant',
                    $imageArray['Large']['URL'],
                    $imageArray['Large']['Width'],
                    $imageArray['Large']['Height']
                );
            }
        }

        $this->connection->commit();
    }
}
