<?php
namespace LeoGalleguillos\Amazon\Model\Service\ProductVideo;

use LeoGalleguillos\Amazon\Model\Entity as AmazonEntity;
use LeoGalleguillos\Amazon\Model\Factory as AmazonFactory;
use LeoGalleguillos\Amazon\Model\Service as AmazonService;
use LeoGalleguillos\Amazon\Model\Table as AmazonTable;

/**
 * Everything
 *
 * Everything required to generated a product video from hi-res photos
 */
class Everything
{
    public function __construct(
        AmazonFactory\Product $productFactory,
        AmazonService\ProductHiResImage\DownloadUrls $downloadUrlsService,
        AmazonService\ProductHiResImage\DownloadHiResImages $downloadHiResImagesService,
        AmazonService\ProductVideo\Generate $generateService,
        AmazonTable\Product\HiResImagesRetrieved $hiResImagesRetrievedTable,
        AmazonTable\Product\VideoGenerated $videoGeneratedTable,
        AmazonTable\ProductVideo $productVideoTable
    ) {
        $this->productFactory             = $productFactory;
        $this->downloadUrlsService        = $downloadUrlsService;
        $this->downloadHiResImagesService = $downloadHiResImagesService;
        $this->generateService            = $generateService;
        $this->hiResImagesRetrievedTable  = $hiResImagesRetrievedTable;
        $this->videoGeneratedTable        = $videoGeneratedTable;
        $this->productVideoTable          = $productVideoTable;
    }

    public function doEverything(AmazonEntity\Product $productEntity): bool
    {
        $this->downloadUrlsService->downloadUrls($productEntity);
        $this->hiResImagesRetrievedTable->updateSetToUtcTimestampWhereProductId(
            $productEntity->getProductId()
        );

        $productEntity = $this->productFactory->buildFromAsin(
            $productEntity->getAsin()
        );

        $this->downloadHiResImagesService->downloadHiResImages($productEntity);

        $productEntity = $this->productFactory->buildFromAsin(
            $productEntity->getAsin()
        );

        $this->videoGeneratedTable->updateSetToUtcTimestampWhereProductId(
            $productEntity->getProductId()
        );

        $this->generateService->generate($productEntity);

        $this->productVideoTable->insert(
            $productEntity->getProductId(),
            $productEntity->getTitle()
        );

        return true;
    }
}
