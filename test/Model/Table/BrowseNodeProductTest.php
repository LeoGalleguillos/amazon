<?php
namespace LeoGalleguillos\AmazonTest\Model\Table;

use LeoGalleguillos\Amazon\Model\Table as AmazonTable;
use LeoGalleguillos\Memcached\Model\Service as MemcachedService;
use LeoGalleguillos\Test\TableTestCase;

class BrowseNodeProductTest extends TableTestCase
{
    protected function setUp()
    {
        $this->memcachedService = $this->createMock(MemcachedService\Memcached::class);
        $this->productTable = new AmazonTable\Product(
            $this->memcachedService,
            $this->getAdapter()
        );
        $this->browseNodeProductTable = new AmazonTable\BrowseNodeProduct(
            $this->getAdapter()
        );

        $this->setForeignKeyChecks0();
        $this->dropAndCreateTables(['browse_node_product', 'product']);
        $this->setForeignKeyChecks1();
    }

    public function testInsertIgnore()
    {
        $this->productTable->insert(
            'ASIN',
            'Title',
            'Product Group',
            'Binding',
            'Brand',
            3.14
        );

        $this->assertSame(
            1,
            $this->browseNodeProductTable->insertOnDuplicateKeyUpdate(1, 1, 1, 1)
        );
        $this->assertSame(
            2,
            $this->browseNodeProductTable->insertOnDuplicateKeyUpdate(1, 1, 2, 2)
        );
        $this->assertSame(
            1,
            $this->browseNodeProductTable->insertOnDuplicateKeyUpdate(2, 1, 123, 1)
        );
        $this->assertSame(
            0,
            $this->browseNodeProductTable->insertOnDuplicateKeyUpdate(2, 1, 123, 1)
        );
        $this->assertSame(
            2,
            $this->browseNodeProductTable->insertOnDuplicateKeyUpdate(2, 1, null, 10)
        );
    }

    public function testSelectProductIdWhereSimilarRetrievedIsNullAndBrowseNodeIdLimit1()
    {
        $productId = $this->browseNodeProductTable->selectProductIdWhereSimilarRetrievedIsNullAndBrowseNodeIdLimit1(
            12345
        );
        $this->assertSame(
            0,
            $productId
        );
    }

    public function testSelectProductIdWhereSimilarRetrievedIsNullAndBrowseNodeIdInLimit1()
    {
        $productId = $this->browseNodeProductTable->selectProductIdWhereSimilarRetrievedIsNullAndBrowseNodeIdInLimit1(
            [1, 2, 3, 4, 5]
        );
        $this->assertSame(
            0,
            $productId
        );
    }

    public function testSelectProductIdWhereVideoGeneratedIsNullAndBrowseNodeIdInLimit1()
    {
        $productId = $this->browseNodeProductTable->selectProductIdWhereVideoGeneratedIsNullAndBrowseNodeIdInLimit1(
            [1, 2, 3, 4, 5]
        );
        $this->assertSame(
            0,
            $productId
        );
    }

    public function testSelectProductIdWhereVideoGeneratedIsNullAndBrowseNodeIdLimit1()
    {
        $productId = $this->browseNodeProductTable->selectProductIdWhereVideoGeneratedIsNullAndBrowseNodeIdLimit1(
            12345
        );
        $this->assertSame(
            0,
            $productId
        );
    }

    public function testSelectWhereProductId()
    {
        $this->assertSame(
            [],
            iterator_to_array($this->browseNodeProductTable->selectWhereProductId(12345))
        );

        $this->productTable->insert(
            'ASIN',
            'Title',
            'Product Group',
            'Binding',
            'Brand',
            3.14
        );
        $this->browseNodeProductTable->insertOnDuplicateKeyUpdate(948, 1, null, 2);
        $this->browseNodeProductTable->insertOnDuplicateKeyUpdate(12345, 38576, 123, 1);
        $this->browseNodeProductTable->insertOnDuplicateKeyUpdate(11, 1, 123, 1);

        $this->assertSame(
            [
                0 => [
                    'browse_node_id' => '11',
                    'product_id'     => '1',
                    'sales_rank'     => '123',
                    'order'          => '1',
                ],
                1 => [
                    'browse_node_id' => '948',
                    'product_id'     => '1',
                    'sales_rank'     => null,
                    'order'          => '2',
                ],
            ],
            iterator_to_array($this->browseNodeProductTable->selectWhereProductId(1))
        );
    }
}
