<?php

namespace idoit\Module\Cmdb\Search\Index\Data\Source\Category;

use idoit\Module\Search\Index\Document;
use idoit\Module\Search\Index\DocumentMetadata;
use idoit\Module\Search\Index\Engine\SearchEngine;
use isys_exception_database;

class isys_cmdb_dao_category_g_network_port extends AbstractCategorySource
{
    /**
     * Map data from retrieveData to Documents
     *
     * @param array $data
     * @return Document[]
     * @throws isys_exception_database
     */
    public function mapDataToDocuments(array $data)
    {
        $documents = parent::mapDataToDocuments($data);

        // Only process entries that contain a mac address.
        $data = array_filter($data, fn ($item) => trim($item['isys_catg_port_list__mac'] ?? '') !== '');

        foreach ($data as $set) {
            // The 'original' mac address was already indexed, we simply want to also index some variations.
            $variations = array_unique($this->prepareMacAddressVariations($set['isys_catg_port_list__mac']));

            foreach ($variations as $index => $variation) {
                $metadata = new DocumentMetadata(
                    get_class($this->categoryDao),
                    $this->getIdentifier(),
                    $set['isys_obj__isys_obj_type__id'],
                    $set['isys_obj__id'],
                    $set['isys_obj__status'],
                    $this->categoryDao->getCategoryTitle(),
                    $set['isys_catg_port_list__id'],
                    $set['isys_catg_port_list__status'],
                    'LC__CMDB__CATG__PORT__MAC'
                );

                $document = new Document($metadata);
                $document->setVersion(SearchEngine::VERSION);
                $document->setType('cmdb');
                $document->setKey(sprintf(
                    '%s.%s.%s.%s.%s',
                    $set['isys_obj__isys_obj_type__id'],
                    $set['isys_obj__id'],
                    $this->categoryDao->getCategoryTitle(),
                    $set['isys_catg_port_list__id'],
                    'LC__CMDB__CATG__PORT__MAC' . $index
                ));
                $document->setReference($set['isys_obj__id']);
                $document->setValue($variation);

                $documents[$document->getKey()] = $document;
            }
        }

        return $documents;
    }

    /**
     * @param string $mac
     * @return array
     */
    private function prepareMacAddressVariations(string $mac): array
    {
        $macChunks = explode(':', $mac);
        $fourCharChunk = array_chunk($macChunks, 2);

        return [
            implode('', $macChunks), // aabbccddeeff
            implode('.', array_map(fn ($chunk) => implode('', $chunk), $fourCharChunk)), // aabb.ccdd.eeff
        ];
    }
}
