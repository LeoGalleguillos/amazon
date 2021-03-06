<?php
namespace LeoGalleguillos\Amazon\Model\Table\Search;

use Exception;
use LeoGalleguillos\Memcached\Model\Service as MemcachedService;
use Laminas\Db\Adapter\Adapter;

class ProductGroup
{
    const MAX_WORDS = 5;

    public function __construct(
        MemcachedService\Memcached $memcachedService,
        Adapter $adapter
    ) {
        $this->memcachedService = $memcachedService;
        $this->adapter          = $adapter;
    }

    /**
     * Insert into table where product and modified.
     *
     * @param string $table
     * @param string $productGroup
     * @param string $modified
     * @return int
     */
    public function insertIntoTableWhereProductGroupAndModified(
        string $table,
        string $productGroup,
        string $modified
    ) : int {
        if (preg_match('/\W/', $table)) {
            throw new Exception('Invalid table name.');
        }
        $sql = "
            INSERT IGNORE
              INTO `$table`
            SELECT `product_id`, `title`, `modified`
              FROM `amazon`.`product`
             WHERE `amazon`.`product`.`product_group` = ?
               AND `amazon`.`product`.`modified` >= ?
                 ;
        ";
        return $this->adapter->query($sql, [$productGroup, $modified])->getAffectedRows();
    }

    public function selectProductIdWhereMatchTitleAgainst(
        string $table,
        string $query,
        int $offset,
        int $rowCount
    ) {
        if (preg_match('/\W/', $table)) {
            throw new Exception('Invalid table name.');
        }

        $cacheKey = md5(__METHOD__ . $table . $query);
        if (false != ($asins = $this->memcachedService->get($cacheKey))) {
            return $asins;
        }

        $query = $this->keepOnlyFirstWords($query);
        $sql = "
            SELECT `product_id`
                 , MATCH (`title`) AGAINST (?) AS `score`
              FROM `$table`
             WHERE MATCH (`title`) AGAINST (?)
             ORDER
                BY `score` DESC
             LIMIT $offset, $rowCount
                 ;
        ";
        $results = $this->adapter->query($sql, [$query, $query]);

        $productIds = [];

        foreach ($results as $row) {
            $productIds[] = $row['product_id'];
        }

        $this->memcachedService->setForDays($cacheKey, $asins, 5);
        return $productIds;
    }

    public function selectProductIdWhereMatchTitleAgainstAndProductIdDoesNotEqual(
        string $table,
        string $query,
        int $productId,
        int $offset,
        int $rowCount
    ) {
        if (preg_match('/\W/', $table)) {
            throw new Exception('Invalid table name.');
        }

        $cacheKey = md5(__METHOD__ . $table . $query . $productId . $offset . $rowCount);
        if (null !== ($productIds = $this->memcachedService->get($cacheKey))) {
            return $productIds;
        }

        $sql = "
            SELECT `product_id`
              FROM $table
             WHERE MATCH(`title`) AGAINST (?)
               AND `product_id` != ?
             LIMIT $offset, $rowCount
                 ;
        ";
        $rows = $this->adapter->query($sql, [$query, $productId]);

        $productIds = [];
        foreach ($rows as $row) {
            $productIds[] = $row['product_id'];
        }

        $this->memcachedService->setForDays($cacheKey, $productIds, 5);
        return $productIds;
    }

    public function selectCountWhereMatchTitleAgainstAndProductIdDoesNotEqual(
        string $table,
        string $query,
        int $productId
    ) {
        if (preg_match('/\W/', $table)) {
            throw new Exception('Invalid table name.');
        }
        $sql = "
            SELECT COUNT(*) AS `count`
              FROM $table
             WHERE MATCH(`title`) AGAINST (?)
               AND `product_id` != ?
                 ;
        ";
        $row = $this->adapter->query($sql)->execute([$query, $productId])->current();
        return $row['count'];
    }

    public function selectCountWhereMatchTitleAgainst($table, $query) : int
    {
        if (preg_match('/\W/', $table)) {
            throw new Exception('Invalid table name.');
        }

        $cacheKey = md5(__METHOD__ . $table . $query);
        if (false != ($count = $this->memcachedService->get($cacheKey))) {
            return $count;
        }

        $query = $this->keepOnlyFirstWords($query);
        $sql = "
            SELECT COUNT(*) AS `count`
              FROM `$table`
             WHERE MATCH (`title`) AGAINST (?)
                 ;
        ";
        $row = $this->adapter->query($sql, [$query])->current();

        $count = (int) $row['count'];
        $this->memcachedService->setForDays($cacheKey, $count, 5);
        return $count;
    }

    public function selectMaxModifiedFrom(string $table) : string
    {
        if (preg_match('/\W/', $table)) {
            throw new Exception('Invalid table name.');
        }
        $sql = "
            SELECT MAX(`modified`) AS `max_modified`
              FROM `$table`
                 ;
        ";
        $row = $this->adapter->query($sql)->execute()->current();
        return $row['max_modified'] ?? '0000-00-00 00:00:00';
    }

    private function keepOnlyFirstWords($query)
    {
        $words = explode(' ', $query);
        return implode(' ', array_slice($words, 0, self::MAX_WORDS));
    }
}
