<?php

namespace idoit\Module\Report\Configuration;

use Exception;
use isys_library_xml;

abstract class AbstractConfiguration
{
    const FIELDMAP_TABLE = 'table';
    const FIELDMAP_DBFIELD = 'dbField';
    const FIELDMAP_NULLABLE = 'nullable';
    const FIELDMAP_CDATA = 'cdata';
    const REPORT_TABLE = 'isys_report';
    const REPORT_CATEGORY_TABLE = 'isys_report_category';

    /**
     * @var isys_library_xml|null
     */
    private ?isys_library_xml $xmlElement = null;

    /**
     * @return array|string[]
     */
    public function getFieldMap()
    {
        return [
            'title' => [
                self::FIELDMAP_TABLE => 'isys_report',
                self::FIELDMAP_DBFIELD => 'isys_report__title',
                self::FIELDMAP_NULLABLE => false,
                self::FIELDMAP_CDATA => true,
            ],
            'description' => [
                self::FIELDMAP_TABLE => 'isys_report',
                self::FIELDMAP_DBFIELD => 'isys_report__description',
                self::FIELDMAP_NULLABLE => false,
                self::FIELDMAP_CDATA => true,
            ],
            'query' => [
                self::FIELDMAP_TABLE => 'isys_report',
                self::FIELDMAP_DBFIELD => 'isys_report__query',
                self::FIELDMAP_NULLABLE => false,
                self::FIELDMAP_CDATA => true,
            ],
            'query_row' => [
                self::FIELDMAP_TABLE => 'isys_report',
                self::FIELDMAP_DBFIELD => 'isys_report__query_row',
                self::FIELDMAP_NULLABLE => true,
                self::FIELDMAP_CDATA => false,
            ],
            'datetime' => [
                self::FIELDMAP_TABLE => 'isys_report',
                self::FIELDMAP_DBFIELD => 'isys_report__datetime',
                self::FIELDMAP_NULLABLE => false,
                self::FIELDMAP_CDATA => false,
            ],
            'last_edited' => [
                self::FIELDMAP_TABLE => 'isys_report',
                self::FIELDMAP_DBFIELD => 'isys_report__last_edited',
                self::FIELDMAP_NULLABLE => false,
                self::FIELDMAP_CDATA => false,
            ],
            'type' => [
                self::FIELDMAP_TABLE => 'isys_report',
                self::FIELDMAP_DBFIELD => 'isys_report__type',
                self::FIELDMAP_NULLABLE => false,
                self::FIELDMAP_CDATA => false,
            ],
            'user_specific' => [
                self::FIELDMAP_TABLE => 'isys_report',
                self::FIELDMAP_DBFIELD => 'isys_report__user_specific',
                self::FIELDMAP_NULLABLE => false,
                self::FIELDMAP_CDATA => false,
            ],
            'querybuilder_data' => [
                self::FIELDMAP_TABLE => 'isys_report',
                self::FIELDMAP_DBFIELD => 'isys_report__querybuilder_data',
                self::FIELDMAP_NULLABLE => false,
                self::FIELDMAP_CDATA => false,
            ],
            'empty_values' => [
                self::FIELDMAP_TABLE => 'isys_report',
                self::FIELDMAP_DBFIELD => 'isys_report__empty_values',
                self::FIELDMAP_NULLABLE => false,
                self::FIELDMAP_CDATA => false,
            ],
            'display_relations' => [
                self::FIELDMAP_TABLE => 'isys_report',
                self::FIELDMAP_DBFIELD => 'isys_report__display_relations',
                self::FIELDMAP_NULLABLE => false,
                self::FIELDMAP_CDATA => false,
            ],
            'category_report' => [
                self::FIELDMAP_TABLE => 'isys_report',
                self::FIELDMAP_DBFIELD => 'isys_report__category_report',
                self::FIELDMAP_NULLABLE => false,
                self::FIELDMAP_CDATA => false,
            ],
            'const' => [
                self::FIELDMAP_TABLE => 'isys_report',
                self::FIELDMAP_DBFIELD => 'isys_report__const',
                self::FIELDMAP_NULLABLE => true,
                self::FIELDMAP_CDATA => false,
            ],
            'compressed_multivalue_results' => [
                self::FIELDMAP_TABLE => 'isys_report',
                self::FIELDMAP_DBFIELD => 'isys_report__compressed_multivalue_results',
                self::FIELDMAP_NULLABLE => false,
                self::FIELDMAP_CDATA => false,
            ],
            'show_html' => [
                self::FIELDMAP_TABLE => 'isys_report',
                self::FIELDMAP_DBFIELD => 'isys_report__show_html',
                self::FIELDMAP_NULLABLE => false,
                self::FIELDMAP_CDATA => false,
            ],
            'keep_description_format' => [
                self::FIELDMAP_TABLE => 'isys_report',
                self::FIELDMAP_DBFIELD => 'isys_report__keep_description_format',
                self::FIELDMAP_NULLABLE => false,
                self::FIELDMAP_CDATA => false,
            ],
            'category__title' => [
                self::FIELDMAP_TABLE => 'isys_report_category',
                self::FIELDMAP_DBFIELD => 'isys_report_category__title',
                self::FIELDMAP_NULLABLE => true,
                self::FIELDMAP_CDATA => false,
            ],
            'category__const' => [
                self::FIELDMAP_TABLE => 'isys_report_category',
                self::FIELDMAP_DBFIELD => 'isys_report_category__const',
                self::FIELDMAP_NULLABLE => true,
                self::FIELDMAP_CDATA => false,
            ],
            'category__description' => [
                self::FIELDMAP_TABLE => 'isys_report_category',
                self::FIELDMAP_DBFIELD => 'isys_report_category__description',
                self::FIELDMAP_NULLABLE => true,
                self::FIELDMAP_CDATA => false,
            ],
            'category__property' => [
                self::FIELDMAP_TABLE => 'isys_report_category',
                self::FIELDMAP_DBFIELD => 'isys_report_category__property',
                self::FIELDMAP_NULLABLE => true,
                self::FIELDMAP_CDATA => false,
            ],
            'category__sort' => [
                self::FIELDMAP_TABLE => 'isys_report_category',
                self::FIELDMAP_DBFIELD => 'isys_report_category__sort',
                self::FIELDMAP_NULLABLE => true,
                self::FIELDMAP_CDATA => false,
            ],
        ];
    }

    /**
     * @return isys_library_xml|null
     */
    public function getXml()
    {
        return $this->xmlElement;
    }

    /**
     * @param string $xmlContent
     *
     * @return $this
     * @throws \Exception
     */
    public function setXml(string $xmlContent)
    {
        try {
            $this->xmlElement = new isys_library_xml($xmlContent);
        } catch (Exception $e) {
            throw ImportException::importFileIsNotValid('Invalid XML Document');
        }
        return $this;
    }
}
