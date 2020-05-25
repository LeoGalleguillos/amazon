<?php
namespace LeoGalleguillos\AmazonTest\Model\Service\Product\Products\Search;

use Laminas\Db as LaminasDb;
use LeoGalleguillos\Amazon\Model\Entity as AmazonEntity;
use LeoGalleguillos\Amazon\Model\Factory as AmazonFactory;
use LeoGalleguillos\Amazon\Model\Service as AmazonService;
use LeoGalleguillos\Amazon\Model\Table as AmazonTable;
use LeoGalleguillos\Test\Hydrator as TestHydrator;
use PHPUnit\Framework\TestCase;

class ResultsTest extends TestCase
{
    protected function setUp()
    {
        $this->productFactoryMock = $this->createMock(
            AmazonFactory\Product::class
        );
        $this->productSearchTableMock = $this->createMock(
            AmazonTable\ProductSearch::class
        );
        $this->resultsService = new AmazonService\Product\Products\Search\Results(
            $this->productFactoryMock,
            $this->productSearchTableMock
        );
    }

    public function test_getResults()
    {
        $resultMock = $this->createMock(
            LaminasDb\Adapter\Driver\Pdo\Result::class
        );
        $hydrator = new TestHydrator\CountableIterator();
        $hydrator->hydrate(
            $resultMock,
            [
                ['product_id' => '123'],
                ['product_id' => '456'],
                ['product_id' => '7890'],
            ]
        );
        $productEntities = array_fill(0, 3, new AmazonEntity\Product());
        $this->productSearchTableMock
            ->expects($this->once())
            ->method('selectProductIdWhereMatchAgainstLimit')
            ->with('the search query', 200, 100)
            ->willReturn($resultMock);
        $this->productFactoryMock
            ->expects($this->exactly(3))
            ->method('buildFromProductId')
            ->withConsecutive(
                [123],
                [456],
                [7890]
            )
            ->willReturnOnConsecutiveCalls(
                $productEntities[0],
                $productEntities[1],
                $productEntities[2]
            );

        $generator = $this->resultsService->getResults(
            'the search query',
            3
        );
        $results = iterator_to_array($generator);
        for ($iteration = 0; $iteration <= 2; $iteration++) {
            $this->assertSame(
                $productEntities[$iteration],
                $results[$iteration]
            );
        }
    }
}
