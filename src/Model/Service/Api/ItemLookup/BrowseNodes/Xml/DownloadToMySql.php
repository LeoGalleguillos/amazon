<?php
namespace LeoGalleguillos\Amazon\Model\Service\Api\ItemLookup\BrowseNodes\Xml;

use LeoGalleguillos\Amazon\Model\Service as AmazonService;
use LeoGalleguillos\Amazon\Model\Table as AmazonTable;
use SimpleXMLElement;

class DownloadToMySql
{
    public function __construct(
        AmazonService\Api\Xml\BrowseNode\DownloadToMySql $downloadToMySqlService,
        AmazonTable\BrowseNodeProduct $browseNodeProductTable,
        AmazonTable\Product\Asin $asinTable
    ) {
        $this->downloadToMySqlService = $downloadToMySqlService;
        $this->browseNodeProductTable = $browseNodeProductTable;
        $this->asinTable              = $asinTable;
    }

    public function downloadToMySql(
        SimpleXMLElement $xml
    ): bool {
        $itemXml = $xml->{'Items'}->{'Item'};
        $asin    = (string) $itemXml->{'ASIN'};

        $productId = $this->asinTable->selectProductIdWhereAsin($asin)['product_id'];

        if (empty($itemXml->{'BrowseNodes'})) {
            return false;
        }

        $order = 1;
        foreach ($itemXml->{'BrowseNodes'}->{'BrowseNode'} as $browseNodeXml) {
            $this->downloadToMySqlService->downloadToMySql(
                $browseNodeXml
            );

            $browseNodeId = (int) $browseNodeXml->{'BrowseNodeId'};
            $this->browseNodeProductTable->insertOnDuplicateKeyUpdate(
                $browseNodeId,
                $productId,
                null,
                $order
            );

            $order++;
        }
        return true;
    }
}
