<?php
namespace LeoGalleguillos\Amazon\Model\Service\ProductHiResImage;

use LeoGalleguillos\Amazon\Model\Entity as AmazonEntity;
use LeoGalleguillos\Amazon\Model\Service as AmazonService;
use LeoGalleguillos\Amazon\Model\Table as AmazonTable;

class DownloadUrls
{
    public function __construct(
        AmazonService\Product\SourceCode $sourceCodeService,
        AmazonService\ProductHiResImage\ArrayFromSourceCode $arrayFromSourceCodeService,
        AmazonTable\ProductHiResImage $productHiResImageTable
    ) {
        $this->sourceCodeService          = $sourceCodeService;
        $this->arrayFromSourceCodeService = $arrayFromSourceCodeService;
        $this->productHiResImageTable     = $productHiResImageTable;
    }

    public function downloadUrls(AmazonEntity\Product $productEntity)
    {
        $sourceCode = $this->sourceCodeService->getSourceCode(
            $productEntity
        );

        $urls = $this->arrayFromSourceCodeService->getArrayFromSourceCode(
            $sourceCode
        );

        $order = 0;
        foreach ($urls as $url) {
            $order++;
            $this->productHiResImageTable->insert(
                $productEntity->getProductId(),
                $url,
                $order
            );
        }

        return (bool) $order;
    }
}
