<?php
namespace LeoGalleguillos\Amazon\Model\Table;

use Zend\Db\Adapter\Adapter;

class ProductVideo
{
    /**
     * @var Adapter
     */
    protected $adapter;

    public function __construct(
        Adapter $adapter
    ) {
        $this->adapter   = $adapter;
    }

    public function insert(
        int $productId,
        string $rru
    ): int {
        $sql = '
            INSERT
              INTO `product_video` (`product_id`, `rru`)
            VALUES (?, ?)
                 ;
        ';
        $parameters = [
            $productId,
            $rru,
        ];
        return (int) $this->adapter
                          ->query($sql)
                          ->execute($parameters)
                          ->getGeneratedValue();
    }
}