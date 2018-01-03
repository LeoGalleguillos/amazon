<?php
namespace LeoGalleguillos\Amazon\Model\Table\Product;

use Website\Model\Entity\Amazon\Product as AmazonProductEntity;
use LeoGalleguillos\Memcached\Model\Service\Memcached as MemcachedService;
use Zend\Db\Adapter\Adapter;

class Feature
{
    /**
     * @var Adapter
     */
    private $adapter;

    public function __construct(
        MemcachedService $memcached,
        Adapter $adapter
    ) {
        $this->memcached = $memcached;
        $this->adapter   = $adapter;
    }

    public function getArraysFromAsin($asin)
    {
        $cacheKey = md5(__METHOD__ . $asin);
        if (null !== ($rows = $this->memcached->get($cacheKey))) {
            return $rows;
        }

        $sql = '
            SELECT `product_feature`.`asin`
                 , `product_feature`.`feature`
              FROM `product_feature`
             WHERE `asin` = ?
                 ;
        ';
        $results = $this->adapter->query($sql, [$asin]);

        $rows = [];
        foreach ($results as $row) {
            $rows[] = (array) $row;
        }

        $this->memcached->setForDays($cacheKey, $rows, 3);
        return $rows;
    }

    public function insertProductIfNotExists(AmazonProductEntity $product)
    {
        return $this->insertWhereNotExists($product);
    }

    private function insertWhereNotExists(AmazonProductEntity $product)
    {
        foreach ($product->features as $feature) {
            if (strlen($feature)) {
                $feature = substr($feature, 0, 255);
            }
            $feature = utf8_encode($feature);
            $sql = '
                INSERT
                  INTO `product_feature` (`asin`, `feature`)
                    SELECT ?, ?
                    FROM `product_feature`
                   WHERE NOT EXISTS (
                       SELECT `asin`
                         FROM `product_feature`
                        WHERE `asin` = ?
                          AND `feature` = ?
                      COLLATE utf8_general_ci
                   )
                   LIMIT 1
               ;
            ';
            $parameters = [
                $product->asin,
                $feature,
                $product->asin,
                $feature
            ];
            $this->adapter
                        ->query($sql, $parameters)
                        ->getGeneratedValue();
        }
    }
}