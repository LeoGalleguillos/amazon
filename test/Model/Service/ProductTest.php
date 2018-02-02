<?php
namespace LeoGalleguillos\AmazonTest\Model\Service;

use LeoGalleguillos\Amazon\Model\Factory as AmazonFactory;
use LeoGalleguillos\Amazon\Model\Service as AmazonService;
use LeoGalleguillos\Amazon\Model\Table as AmazonTable;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    protected function setUp()
    {
        $this->productFactoryMock = $this->createMock(
            AmazonFactory\Product::class
        );
        $this->apiServiceMock = $this->createMock(
            AmazonService\Api::class
        );
        $this->apiProductXmlServiceMock = $this->createMock(
            AmazonService\Api\Product\Xml::class
        );
        $this->productDownloadServiceMock = $this->createMock(
            AmazonService\Product\Download::class
        );
        $this->productTableMock = $this->createMock(
            AmazonTable\Product::class
        );

        $this->productService = new AmazonService\Product(
            $this->productFactoryMock,
            $this->apiServiceMock,
            $this->apiProductXmlServiceMock,
            $this->productDownloadServiceMock,
            $this->productTableMock
        );
    }

    public function testInitialize()
    {
        $this->assertInstanceOf(
            AmazonService\Product::class,
            $this->productService
        );
    }
}