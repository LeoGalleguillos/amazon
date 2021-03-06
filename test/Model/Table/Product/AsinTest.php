<?php
namespace LeoGalleguillos\AmazonTest\Model\Table\Product;

use LeoGalleguillos\Amazon\Model\Table as AmazonTable;
use LeoGalleguillos\Test\TableTestCase as TableTestCase;
use TypeError;

class AsinTest extends TableTestCase
{
    protected function setUp(): void
    {
        $this->setForeignKeyChecks(0);
        $this->dropAndCreateTable('product');
        $this->setForeignKeyChecks(1);

        $this->productTable = new AmazonTable\Product(
            $this->getAdapter()
        );
        $this->asinTable = new AmazonTable\Product\Asin(
            $this->getAdapter(),
            $this->productTable
        );
    }

    public function testSelectProductIdWhereAsin()
    {
        try {
            $array = $this->asinTable->selectProductIdWhereAsin('ASIN001');
            $this->fail();
        } catch (TypeError $typeError) {
            $this->assertSame(
                'Return value of',
                substr($typeError->getMessage(), 0, 15)
            );
        }

        $this->productTable->insert(
            'ASIN001',
            'Title',
            'Product Group',
            null,
            null,
            4.99
        );

        $array = $this->asinTable->selectProductIdWhereAsin('ASIN001');
        $this->assertSame(
            '1',
            $array['product_id']
        );
    }

    public function test_selectWhereAsin()
    {
        $result = $this->asinTable->selectWhereAsin('ASIN001');
        $this->assertEmpty($result);
        $this->assertSame(
            0,
            count($result)
        );

        $this->productTable->insert(
            'ASIN001',
            'Title',
            'Product Group',
            null,
            null,
            4.99
        );

        $result = $this->asinTable->selectWhereAsin('ASIN001');
        $this->assertSame(
            1,
            count($result)
        );
        $array = $result->current();
        $this->assertSame(
            '1',
            $array['product_id']
        );
        $this->assertSame(
            'ASIN001',
            $array['asin']
        );
        $this->assertSame(
            'Product Group',
            $array['product_group']
        );
    }

    public function testUpdateSetModifiedToUtcTimestampWhereAsin()
    {
        $affectedRows = $this->asinTable->updateSetModifiedToUtcTimestampWhereAsin('ASIN001');
        $this->assertSame(
            0,
            $affectedRows
        );

        $this->productTable->insert(
            'ASIN001',
            'Title',
            'Product Group',
            null,
            null,
            4.99
        );

        $affectedRows = $this->asinTable->updateSetModifiedToUtcTimestampWhereAsin('ASIN001');
        $this->assertSame(
            1,
            $affectedRows
        );
    }

    public function test_updateSetIsValidWhereAsin()
    {
        $affectedRows = $this->asinTable
            ->updateSetIsValidWhereAsin(0, 'ASIN001')
            ->getAffectedRows();
        $this->assertSame(
            0,
            $affectedRows
        );

        $this->productTable->insert(
            'ASIN001',
            'Title',
            'Product Group',
            null,
            null,
            4.99
        );

        $affectedRows = $this->asinTable
            ->updateSetIsValidWhereAsin(0, 'ASIN001')
            ->getAffectedRows();
        $this->assertSame(
            1,
            $affectedRows
        );
        $affectedRows = $this->asinTable
            ->updateSetIsValidWhereAsin(1, 'ASIN001')
            ->getAffectedRows();
        $this->assertSame(
            1,
            $affectedRows
        );
        $affectedRows = $this->asinTable
            ->updateSetIsValidWhereAsin(1, 'ASIN001')
            ->getAffectedRows();
        $this->assertSame(
            0,
            $affectedRows
        );
    }

    public function test_updateSetParentAsinWhereAsin()
    {
        $affectedRows = $this->asinTable
            ->updateSetParentAsinWhereAsin(0, 'ASIN001')
            ->getAffectedRows();
        $this->assertSame(
            0,
            $affectedRows
        );

        $this->productTable->insert(
            'ASIN001',
            'Title',
            'Product Group',
            null,
            null,
            4.99
        );

        $affectedRows = $this->asinTable
            ->updateSetParentAsinWhereAsin('PARENTASIN', 'ASIN001')
            ->getAffectedRows();

        $this->assertSame(
            1,
            $affectedRows
        );
        $affectedRows = $this->asinTable
            ->updateSetParentAsinWhereAsin('PARENTASI2', 'ASIN001')
            ->getAffectedRows();
        $this->assertSame(
            1,
            $affectedRows
        );
        $affectedRows = $this->asinTable
            ->updateSetParentAsinWhereAsin('PARENTASI2', 'ASIN001')
            ->getAffectedRows();
        $this->assertSame(
            0,
            $affectedRows
        );
    }
}
