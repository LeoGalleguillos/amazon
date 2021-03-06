<?php
namespace LeoGalleguillos\Amazon\Model\Table;

use Laminas\Db\Adapter\Driver\Pdo\Result;
use LeoGalleguillos\Amazon\Model\Table as AmazonTable;
use Generator;
use TypeError;
use Laminas\Db\Adapter\Adapter;

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

    public function getSelect(): string
    {
        return '
            SELECT `product_video`.`product_video_id`
                 , `product_video`.`product_id`
                 , `product_video`.`asin`
                 , `product_video`.`title`
                 , `product_video`.`description`
                 , `product_video`.`duration_milliseconds`
                 , `product_video`.`views`
                 , `product_video`.`created`
                 , `product_video`.`modified`
        ';
    }

    public function insertOnDuplicateKeyUpdate(
        int $productId,
        string $asin,
        string $title,
        string $description = null,
        int $durationMilliseconds
    ): int {
        $sql = '
            INSERT
              INTO `product_video` (
                       `product_id`
                     , `asin`
                     , `title`
                     , `description`
                     , `duration_milliseconds`
                     , `created`
                   )
            VALUES (?, ?, ?, ?, ?, UTC_TIMESTAMP())

                ON DUPLICATE KEY UPDATE
                   `title` = ?
                 , `description` = ?
                 , `duration_milliseconds` = ?
                 , `modified` = UTC_TIMESTAMP()

                 ;
        ';
        $parameters = [
            $productId,
            $asin,
            $title,
            $description,
            $durationMilliseconds,
            $title,
            $description,
            $durationMilliseconds,
        ];
        return (int) $this->adapter
                          ->query($sql)
                          ->execute($parameters)
                          ->getGeneratedValue();
    }

    public function select(
        int $limitOffset,
        int $limitRowCount
    ): Generator {
        $sql = $this->getSelect()
             . "
              FROM `product_video`

             ORDER
                BY `product_video`.`product_video_id` ASC
             LIMIT $limitOffset, $limitRowCount
                 ;
        ";
        foreach ($this->adapter->query($sql)->execute() as $array) {
            yield $array;
        }
    }

    public function selectAsinWhereMatchAgainst(string $query): Generator
    {
        $sql = '
            SELECT `product_video`.`asin`
                 , MATCH(`product_video`.`title`) AGAINST (?) AS `score`
              FROM `product_video`
             WHERE MATCH(`product_video`.`title`) AGAINST (?)
             ORDER
                BY `score` DESC
             LIMIT 11
                 ;
        ';
        $parameters = [
            $query,
            $query,
        ];
        foreach ($this->adapter->query($sql)->execute($parameters) as $array) {
            yield $array['asin'];
        }
    }

    public function selectCount(): int
    {
        $sql = '
            SELECT COUNT(*) AS `count`
              FROM `product_video`
                 ;
        ';
        return (int) $this->adapter->query($sql)->execute()->current()['count'];
    }

    public function selectCountWhereBrowseNodeId(
        int $browseNodeId
    ): int {
        $sql = '
            SELECT COUNT(*) AS `count`

              FROM `product_video`

              JOIN `browse_node_product`
             USING (`product_id`)

             WHERE `browse_node_product`.`browse_node_id` = ?
                 ;
        ';
        $parameters = [
            $browseNodeId,
        ];
        $array = $this->adapter->query($sql)->execute($parameters)->current();
        return (int) $array['count'];
    }

    public function selectCountWhereBrowseNodeName(
        string $name
    ): int {
        $sql = "
            SELECT COUNT(*) AS `count`
              FROM `product_video`

              JOIN `browse_node_product`
                ON `browse_node_product`.`product_id` = `product_video`.`product_id`
               AND `browse_node_product`.`order` = 1

              JOIN `browse_node`
             USING (`browse_node_id`)

             WHERE `browse_node`.`name` = ?
                 ;
        ";
        $parameters = [
            $name,
        ];
        $array = $this->adapter->query($sql)->execute($parameters)->current();
        return (int) $array['count'];
    }

    public function selectCountWhereBrowseNodeNameNotIn(
        array $browseNodeNames
    ): int {
        $questionMarks = array_fill(0, count($browseNodeNames), '?');
        $questionMarks = implode(', ', $questionMarks);

        $sql = "
            SELECT COUNT(*) AS `count`

              FROM `product_video`

              JOIN `browse_node_product`
                ON `browse_node_product`.`product_id` = `product_video`.`product_id`
               AND `browse_node_product`.`order` = 1

              JOIN `browse_node`
             USING (`browse_node_id`)

             WHERE `browse_node`.`name` NOT IN ($questionMarks)
                 ;
        ";
        $parameters = $browseNodeNames;
        $array = $this->adapter->query($sql)->execute($parameters)->current();
        return (int) $array['count'];
    }

    /**
     * @throws TypeError
     */
    public function selectProductIdWhereDescriptionIsNullLimit1(): int
    {
        $sql = '
            SELECT `product_video`.`product_id`
              FROM `product_video`
             WHERE `product_video`.`description` IS NULL
             LIMIT 1
                 ;
        ';
        $array = $this->adapter->query($sql)->execute()->current();

        if (empty($array)) {
            throw new TypeError('Product ID not found.');
        }

        return (int) $array['product_id'];
    }

    public function selectOrderByCreatedDesc(): Generator
    {
        $sql = $this->getSelect()
            . '
                 , `browse_node`.`name` AS `browse_node.name`

              FROM `product_video`

              LEFT
              JOIN `browse_node_product`
                ON `browse_node_product`.`product_id` = `product_video`.`product_id`
               AND `browse_node_product`.`order` = 1

              LEFT
              JOIN `browse_node`
             USING (`browse_node_id`)

             ORDER
                BY `product_video`.`created` DESC

             LIMIT 100

        ';
        foreach ($this->adapter->query($sql)->execute() as $array) {
            yield $array;
        }
    }

    /**
     * @throws TypeError
     */
    public function selectProductIdWhereModifiedIsNullLimit1(): int
    {
        $sql = '
            SELECT `product_id`
              FROM `product_video`
             WHERE `modified` IS NULL
             LIMIT 1
                 ;
        ';
        $array = $this->adapter->query($sql)->execute()->current();

        if (empty($array)) {
            throw new TypeError('Product ID not found.');
        }

        return (int) $array['product_id'];
    }

    /**
     * @throws TypeError
     */
    public function selectWhereAsin(string $asin): array
    {
        $sql = $this->getSelect()
             . '
                 , `browse_node`.`name` AS `browse_node.name`

              FROM `product_video`

              LEFT
              JOIN `browse_node_product`
                ON `browse_node_product`.`product_id` = `product_video`.`product_id`
               AND `browse_node_product`.`order` = 1

              LEFT
              JOIN `browse_node`
             USING (`browse_node_id`)

             WHERE `product_video`.`asin` = ?
                 ;
        ';
        $parameters = [
            $asin,
        ];
        return $this->adapter->query($sql)->execute($parameters)->current();
    }

    public function selectProductVideoIdWhereBrowseNodeId(
        int $browseNodeId,
        int $limitOffset,
        int $limitRowCount
    ): Result {
        $sql = '
            SELECT `product_video`.`product_video_id`

              FROM `product_video`
               USE
             INDEX (`product_id_created`)

              JOIN `browse_node_product`
             USING (`product_id`)

             WHERE `browse_node_product`.`browse_node_id` = ?

             ORDER
                BY `product_video`.`created` DESC
                 , `product_video`.`product_video_id` DESC

             LIMIT ?, ?
                 ;
        ';
        $parameters = [
            $browseNodeId,
            $limitOffset,
            $limitRowCount,
        ];
        return $this->adapter->query($sql)->execute($parameters);
    }

    public function selectWhereBrowseNodeName(
        string $name,
        int $limitOffset,
        int $limitRowCount
    ): Generator {
        $sql = $this->getSelect()
             . "
                 , `browse_node`.`name` AS `browse_node.name`

              FROM `product_video`

              JOIN `browse_node_product`
             USING (`product_id`)

              JOIN `browse_node`
             USING (`browse_node_id`)

             WHERE `browse_node`.`name` = ?

             ORDER
                BY `product_video`.`created` DESC

             LIMIT $limitOffset, $limitRowCount
                 ;
        ";
        $parameters = [
            $name,
        ];
        foreach ($this->adapter->query($sql)->execute($parameters) as $array) {
            yield $array;
        }
    }

    public function selectWhereBrowseNodeNameNotIn(
        array $browseNodeNames,
        int $limitOffset,
        int $limitRowCount
    ): Generator {
        $questionMarks = array_fill(0, count($browseNodeNames), '?');
        $questionMarks = implode(', ', $questionMarks);

        $sql = $this->getSelect()
             . "
                 , `browse_node`.`name` AS `browse_node.name`

              FROM `product_video`

              JOIN `browse_node_product`
                ON `browse_node_product`.`product_id` = `product_video`.`product_id`
               AND `browse_node_product`.`order` = 1

              JOIN `browse_node`
             USING (`browse_node_id`)

             WHERE `browse_node`.`name` NOT IN ($questionMarks)

             ORDER
                BY `product_video`.`created` DESC

             LIMIT $limitOffset, $limitRowCount
                 ;
        ";
        $parameters = $browseNodeNames;
        foreach ($this->adapter->query($sql)->execute($parameters) as $array) {
            yield $array;
        }
    }

    /**
     * @deprecated Instead of this method, use the following method:
     * AmazonTable\ProductVideo\Modified::selectWhereModifiedIsNullAndBrowseNodeIdIsNullLimit
     *
     * @throws TypeError
     */
    public function selectWhereModifiedIsNullAndBrowseNodeIdIsNullLimit1(): array
    {
        $sql = $this->getSelect()
             . '
              FROM `product_video`

              LEFT
              JOIN `browse_node_product`
             USING (`product_id`)

             WHERE `product_video`.`modified` IS NULL
               AND `browse_node_product`.`browse_node_id` IS NULL

             LIMIT 1
        ';
        return $this->adapter->query($sql)->execute()->current();
    }

    /**
     * @throws TypeError
     */
    public function selectWhereProductId(int $productId): array
    {
        $sql = $this->getSelect()
             . '
              FROM `product_video`
             WHERE `product_id` = ?
                 ;
        ';
        $parameters = [
            $productId,
        ];
        return $this->adapter->query($sql)->execute($parameters)->current();
    }

    /**
     * @throws TypeError
     */
    public function selectWhereProductVideoId(int $productVideoId): array
    {
        $sql = $this->getSelect()
             . '
                 , `browse_node`.`name` AS `browse_node.name`

              FROM `product_video`

              LEFT
              JOIN `browse_node_product`
                ON `browse_node_product`.`product_id` = `product_video`.`product_id`
               AND `browse_node_product`.`order` = 1

              LEFT
              JOIN `browse_node`
             USING (`browse_node_id`)

             WHERE `product_video_id` = ?
                 ;
        ';
        $parameters = [
            $productVideoId,
        ];
        return $this->adapter->query($sql)->execute($parameters)->current();
    }

    public function updateSetDescriptionWhereProductId(
        string $description,
        int $productId
    ): int {
        $sql = '
            UPDATE `product_video`
               SET `product_video`.`description` = ?
             WHERE `product_video`.`product_id` = ?
                 ;
        ';
        $parameters = [
            $description,
            $productId,
        ];
        return (int) $this->adapter
                          ->query($sql)
                          ->execute($parameters)
                          ->getAffectedRows();
    }
}
