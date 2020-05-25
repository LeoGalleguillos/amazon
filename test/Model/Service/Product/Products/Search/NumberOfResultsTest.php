<?php
namespace LeoGalleguillos\AmazonTest\Model\Service\Product\Products\Search;

use Laminas\Db as LaminasDb;
use LeoGalleguillos\Amazon\Model\Service as AmazonService;
use LeoGalleguillos\Amazon\Model\Table as AmazonTable;
use PHPUnit\Framework\TestCase;

class NumberOfResultsTest extends TestCase
{
    protected function setUp()
    {
        $this->productSearchTableMock = $this->createMock(
            AmazonTable\ProductSearch::class
        );
        $this->numberOfResultsService = new AmazonService\Product\Products\Search\NumberOfResults(
            $this->productSearchTableMock
        );
    }

    public function test_getNumberOfResults()
    {
        $result = $this->createMock(
            LaminasDb\Adapter\Driver\Pdo\Result::class
        );
        $this->productSearchTableMock
            ->expects($this->once())
            ->method('selectCountWhereMatchAgainst')
            ->with('the search query')
            ->willReturn($result);

        $this->numberOfResultsService->getNumberOfResults('the search query');
    }
}
