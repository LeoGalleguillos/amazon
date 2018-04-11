<?php
namespace LeoGalleguillos\AmazonTest\Model\Service\Product;

use LeoGalleguillos\Amazon\Model\Entity as AmazonEntity;
use LeoGalleguillos\Amazon\Model\Service as AmazonService;
use LeoGalleguillos\Amazon\Model\Table as AmazonTable;
use PHPUnit\Framework\TestCase;

class HashtagsTest extends TestCase
{
    protected function setUp()
    {
        $this->productHashtagsRetrievedTableMock = $this->createMock(
            AmazonTable\Product\HashtagsRetrieved::class
        );

        $this->hashtagsService = new AmazonService\Product\Hashtags(
            $this->productHashtagsRetrievedTableMock
        );
    }

    public function testInitialize()
    {
        $this->assertInstanceOf(
            AmazonService\Product\Hashtags::class,
            $this->hashtagsService
        );
    }
}