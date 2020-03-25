<?php
namespace LeoGalleguillos\Amazon\Model\Table;

use Generator;
use LeoGalleguillos\Amazon\Model\Entity as AmazonEntity;
use Zend\Db\Adapter\Adapter;

class ProductFeature
{
    /**
     * @var Adapter
     */
    protected $adapter;

    public function __construct(
        Adapter $adapter
    ) {
        $this->adapter = $adapter;
    }

    public function selectWhereProductId(int $productId): Generator
    {
        $sql = '
            SELECT `product_feature`.`product_id`
                 , `product_feature`.`feature`
              FROM `product_feature`
             WHERE `product_id` = ?
                 ;
        ';
        $parameters = [
            $productId,
        ];
        foreach ($this->adapter->query($sql)->execute($parameters) as $array) {
            yield $array;
        }
    }

    public function insert(
        int $productId,
        string $asin,
        string $feature
    ): int {
        $sql = '
            INSERT
              INTO `product_feature` (`product_id`, `asin`, `feature`)
            VALUES (?, ?, ?)
           ;
        ';
        $parameters = [
            $productId,
            $asin,
            $feature,
        ];
        return (int) $this->adapter
            ->query($sql)
            ->execute($parameters)
            ->getAffectedRows();
    }
}
