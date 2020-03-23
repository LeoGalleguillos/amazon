<?php
namespace LeoGalleguillos\AmazonTest\Model\Factory;

use DateTime;
use Generator;
use LeoGalleguillos\Amazon\Model\Entity as AmazonEntity;
use LeoGalleguillos\Amazon\Model\Factory as AmazonFactory;
use LeoGalleguillos\Amazon\Model\Table as AmazonTable;
use LeoGalleguillos\Image\Model\Factory as ImageFactory;
use LeoGalleguillos\Test\Hydrator as TestHydrator;
use PHPUnit\Framework\TestCase;
use Laminas\Db\Adapter\Driver\Pdo\Result;
use TypeError;

class ProductTest extends TestCase
{
    protected function setUp()
    {
        $this->bindingFactoryMock = $this->createMock(AmazonFactory\Binding::class);
        $this->productGroupFactoryMock = $this->createMock(AmazonFactory\ProductGroup::class);
        $this->imageFactoryMock = $this->createMock(ImageFactory\Image::class);
        $this->productTableMock = $this->createMock(AmazonTable\Product::class);
        $this->asinTableMock = $this->createMock(
            AmazonTable\Product\Asin::class
        );
        $this->productEanProductIdTableMock = $this->createMock(
            AmazonTable\ProductEan\ProductId::class
        );
        $this->productFeatureTableMock = $this->createMock(AmazonTable\ProductFeature::class);
        $this->productImageTableMock = $this->createMock(AmazonTable\ProductImage::class);
        $this->productIsbnProductIdTableMock = $this->createMock(
            AmazonTable\ProductIsbn\ProductId::class
        );
        $this->productUpcProductIdTableMock = $this->createMock(
            AmazonTable\ProductUpc\ProductId::class
        );

        $this->productFactory = new AmazonFactory\Product(
            $this->bindingFactoryMock,
            $this->productGroupFactoryMock,
            $this->imageFactoryMock,
            $this->productTableMock,
            $this->asinTableMock,
            $this->productEanProductIdTableMock,
            $this->productFeatureTableMock,
            $this->productImageTableMock,
            $this->productIsbnProductIdTableMock,
            $this->productUpcProductIdTableMock
        );

        $this->productEanResultMock = $this->createMock(
            Result::class
        );
        $this->productIsbnResultMock = $this->createMock(
            Result::class
        );
        $this->productUpcResultMock = $this->createMock(
            Result::class
        );
    }

    public function test_buildFromArray()
    {
        $this->initializeProductEanResultMock();
        $this->productEanProductIdTableMock
            ->method('selectWhereProductId')
            ->willReturn(
                $this->productEanResultMock
            );
        $this->productFeatureTableMock
            ->method('selectWhereAsin')
            ->willReturn(
                $this->yieldProductFeatureArrays()
            );
        $this->productImageTableMock
            ->method('selectWhereProductId')
            ->willReturn(
                $this->yieldProductImageArrays()
            );
        $this->initializeProductIsbnResultMock();
        $this->productIsbnProductIdTableMock
            ->method('selectWhereProductId')
            ->willReturn(
                $this->productIsbnResultMock
            );
        $this->initializeProductUpcResultMock();
        $this->productUpcProductIdTableMock
            ->method('selectWhereProductId')
            ->willReturn(
                $this->productUpcResultMock
            );

        $array = [
            'asin'                     => 'ASIN',
            'brand'                    => 'the brand',
            'color'                    => 'Red',
            'height_units'             => 'inches',
            'height_value'             => '1.0',
            'is_adult_product'         => 1,
            'is_eligible_for_trade_in' => 1,
            'is_valid'                 => '1',
            'length_units'             => 'cm',
            'length_value'             => '3.14159',
            'list_price'               => '1.23',
            'model'                    => 'the model',
            'manufacturer'             => 'the manufacturer',
            'part_number'              => 'the part #',
            'product_id'               => '12345',
            'released'                 => '2020-02-08 12:12:45',
            'size'                     => 'Medium',
            'title'                    => 'Title',
            'trade_in_price'           => 19.95,
            'unit_count'               => 7,
            'warranty'                 => 'The warranty for the product',
            'weight_units'             => 'LBS',
            'weight_value'             => '1000',
            'width_units'              => 'feet',
            'width_value'              => '10.0',
        ];

        $productEntity = (new AmazonEntity\Product())
            ->setAsin('ASIN')
            ->setBrand('the brand')
            ->setColor('Red')
            ->setEans([
                '1234567890123',
                '1234567890124',
                '1234567890125',
            ])
            ->setFeatures([
                'This is the first feature.',
                'This is the second feature.',
            ])
            ->setIsAdultProduct(true)
            ->setIsbns([
                '1234567890',
            ])
            ->setIsEligibleForTradeIn(true)
            ->setIsValid(true)
            ->setHeightUnits('inches')
            ->setHeightValue('1.0')
            ->setLengthUnits('cm')
            ->setLengthValue('3.14159')
            ->setProductId('12345')
            ->setListPrice('1.23')
            ->setManufacturer('the manufacturer')
            ->setModel('the model')
            ->setPartNumber('the part #')
            ->setReleased(new DateTime('2020-02-08 12:12:45'))
            ->setSize('Medium')
            ->setTitle('Title')
            ->setTradeInPrice(19.95)
            ->setUnitCount(7)
            ->setUpcs([
                '123456789012',
                '123456789013',
            ])
            ->setVariantImages([
                null,
                null
            ])
            ->setWarranty('The warranty for the product')
            ->setWeightUnits('LBS')
            ->setWeightValue('1000')
            ->setWidthUnits('feet')
            ->setWidthValue('10.0')
            ;

        $this->assertEquals(
            $productEntity,
            $this->productFactory->buildFromArray($array)
        );
    }

    public function testBuildFromArrayIsAdultProductIsNull()
    {
        $this->productImageTableMock
            ->method('selectWhereProductId')
            ->willReturn(
                $this->yieldProductImageArrays()
            );

        $array = [
            'asin'             => 'ASIN001',
            'product_id'       => 1,
            'is_adult_product' => null,
        ];

        $productEntity = $this->productFactory->buildFromArray($array);

        $this->expectException(TypeError::class);
        $productEntity->getIsAdultProduct();
    }

    public function testBuildFromArrayIsValidIsNull()
    {
        $this->productImageTableMock
            ->method('selectWhereProductId')
            ->willReturn(
                $this->yieldProductImageArrays()
            );

        $array = [
            'asin'       => 'ASIN001',
            'product_id' => 1,
            'is_valid'   => null,
        ];

        $productEntity = $this->productFactory->buildFromArray($array);

        $this->expectException(TypeError::class);
        $productEntity->getIsAdultProduct();
    }

    public function testBuildFromAsin()
    {
        $resultHydrator = new TestHydrator\Result();

        $resultMock = $this->createMock(
            Result::class
        );
        $resultHydrator->hydrate(
            $resultMock,
            [
                [
                    'product_id' => 12345,
                    'asin'       => 'ASIN12345',
                ]
            ]
        );
        $this->asinTableMock
            ->method('selectWhereAsin')
            ->willReturn($resultMock);

        $this->productFeatureTableMock
            ->method('selectWhereAsin')
            ->willReturn(
                $this->yieldProductFeatureArrays()
            );

        $this->productImageTableMock
            ->method('selectWhereProductId')
            ->willReturn(
                $this->yieldProductImageArrays()
            );

        $productEntity = (new AmazonEntity\Product())
            ->setAsin('ASIN12345')
            ->setFeatures([
                'This is the first feature.',
                'This is the second feature.',
            ])
            ->setProductId('12345')
            ->setVariantImages([
                null,
                null
            ]);
            ;

        $this->assertEquals(
            $productEntity,
            $this->productFactory->buildFromAsin('ASIN12345')
        );
    }

    public function testBuildFromProductId()
    {
        $this->productTableMock
            ->method('selectWhereProductId')
            ->willReturn([
                'product_id' => 12345,
                'asin'       => 'ASIN12345',
            ]);

        $this->productFeatureTableMock
            ->method('selectWhereAsin')
            ->willReturn(
                $this->yieldProductFeatureArrays()
            );

        $this->productImageTableMock
            ->method('selectWhereProductId')
            ->willReturn(
                $this->yieldProductImageArrays()
            );

        $productEntity = (new AmazonEntity\Product())
            ->setAsin('ASIN12345')
            ->setFeatures([
                'This is the first feature.',
                'This is the second feature.',
            ])
            ->setProductId('12345')
            ->setVariantImages([
                null,
                null
            ]);
            ;

        $this->assertEquals(
            $productEntity,
            $this->productFactory->buildFromProductId(12345)
        );
    }

    protected function initializeProductEanResultMock()
    {
        $this->productEanResultMock
            ->method('current')
            ->will(
                $this->onConsecutiveCalls(
                    ['product_id' => '12345', 'ean' => '1234567890123'],
                    ['product_id' => '12345', 'ean' => '1234567890124'],
                    ['product_id' => '12345', 'ean' => '1234567890125']
                )
            );
        $this->productEanResultMock
            ->method('key')
            ->will(
                $this->onConsecutiveCalls(
                    0,
                    1,
                    2
                )
            );
        $this->productEanResultMock
            ->method('valid')
            ->will(
                $this->onConsecutiveCalls(
                    true,
                    true,
                    true
                )
            );
    }

    protected function initializeProductIsbnResultMock()
    {
        $this->productIsbnResultMock
            ->method('current')
            ->will(
                $this->onConsecutiveCalls(
                    ['product_id' => '12345', 'isbn' => '1234567890']
                )
            );
        $this->productIsbnResultMock
            ->method('key')
            ->will(
                $this->onConsecutiveCalls(
                    0
                )
            );
        $this->productIsbnResultMock
            ->method('valid')
            ->will(
                $this->onConsecutiveCalls(
                    true
                )
            );
    }

    protected function initializeProductUpcResultMock()
    {
        $this->productUpcResultMock
            ->method('current')
            ->will(
                $this->onConsecutiveCalls(
                    ['product_id' => '12345', 'upc' => '123456789012'],
                    ['product_id' => '12345', 'upc' => '123456789013']
                )
            );
        $this->productUpcResultMock
            ->method('key')
            ->will(
                $this->onConsecutiveCalls(
                    0,
                    1
                )
            );
        $this->productUpcResultMock
            ->method('valid')
            ->will(
                $this->onConsecutiveCalls(
                    true,
                    true
                )
            );
    }

    protected function yieldProductFeatureArrays(): Generator
    {
        yield [
            'product_id' => 1,
            'asin'       => 'ASIN12345',
            'feature'    => 'This is the first feature.',
        ];

        yield [
            'product_id' => 1,
            'asin'       => 'ASIN12345',
            'feature'    => 'This is the second feature.',
        ];
    }

    protected function yieldProductImageArrays(): Generator
    {
        yield [
            'url'      => 'https://www.example.com/images/product/asin12345/1.jpg',
            'category' => 'primary',
        ];

        yield [
            'url'      => 'https://www.example.com/images/product/asin12345/2.jpg',
            'category' => 'variant',
        ];

        yield [
            'url'      => 'https://www.example.com/images/product/asin12345/3.jpg',
            'category' => 'variant',
        ];
    }
}
