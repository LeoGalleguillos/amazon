<?php
namespace LeoGalleguillos\AmazonTest\Model\Table\Product;

use LeoGalleguillos\Amazon\Model\Table as AmazonTable;
use LeoGalleguillos\Memcached\Model\Service as MemcachedService;
use LeoGalleguillos\Test\TableTestCase as TableTestCase;

class ModifiedProductIdTest extends TableTestCase
{
    protected function setUp()
    {
        $this->productTable = new AmazonTable\Product(
            $this->createMock(MemcachedService\Memcached::class),
            $this->getAdapter()
        );
        $this->asinTable = new AmazonTable\Product\Asin(
            $this->getAdapter()
        );
        $this->modifiedProductIdTable = new AmazonTable\Product\ModifiedProductId(
            $this->getAdapter()
        );

        $this->dropTable('product');
        $this->createTable('product');
    }

    public function testSelectAsinOrderByModifiedIsNullDescModifiedAscProductIdAscLimitRowCount()
    {
        $generator = $this->modifiedProductIdTable
            ->selectAsinOrderByModifiedIsNullDescModifiedAscProductIdAscLimitRowCount(0);
        $this->assertEmpty(
            iterator_to_array($generator)
        );

        $this->productTable->insert(
            'ASIN001',
            'Title',
            'Product Group',
            null,
            null,
            4.99
        );

        $generator = $this->modifiedProductIdTable
            ->selectAsinOrderByModifiedIsNullDescModifiedAscProductIdAscLimitRowCount(1);
        $this->assertCount(
            1,
            iterator_to_array($generator)
        );

        $this->productTable->insert(
            'ASIN002',
            'Title',
            'Product Group',
            null,
            null,
            4.99
        );
        $this->productTable->insert(
            'ASIN003',
            'Title',
            'Product Group',
            null,
            null,
            4.99
        );

        $generator = $this->modifiedProductIdTable
            ->selectAsinOrderByModifiedIsNullDescModifiedAscProductIdAscLimitRowCount(2);
        $array = iterator_to_array($generator);
        $this->assertCount(
            2,
            $array
        );
        $this->assertSame(
            'ASIN001',
            $array[0]['asin']
        );

        $this->asinTable->updateSetModifiedToUtcTimestampWhereAsin('ASIN001');

        $generator = $this->modifiedProductIdTable
            ->selectAsinOrderByModifiedIsNullDescModifiedAscProductIdAscLimitRowCount(3);
        $array = iterator_to_array($generator);
        $this->assertCount(
            3,
            $array
        );
        $this->assertSame(
            'ASIN001',
            $array[2]['asin']
        );
    }
}