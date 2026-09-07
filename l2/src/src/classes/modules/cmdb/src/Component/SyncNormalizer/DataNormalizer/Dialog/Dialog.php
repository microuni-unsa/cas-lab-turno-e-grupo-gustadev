<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\Dialog;

use Exception;
use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\DataNormalizerInterface;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizerProviderConfig;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\AbstractShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\CustomDialogShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\StringShape;
use isys_application;
use isys_cmdb_dao_dialog;
use Throwable;

class Dialog implements DataNormalizerInterface
{
    private static array $dialogCache = [];

    /**
     * @param DataNormalizerProviderConfig $config
     * @param string                       $propertyKey
     * @param array                        $requestData
     *
     * @return bool
     */
    public static function isApplicable(DataNormalizerProviderConfig $config, string $propertyKey, array $requestData): bool
    {
        $property = $config->getProperties()[$propertyKey];
        $callback = $property->getFormat()->getCallback();
        $references = $property->getData()->getReferences();

        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__DIALOG
            && is_array($references)
            && !empty($references)
            && $callback[1] !== 'get_yes_or_no';
    }

    /**
     * @param DataNormalizerProviderConfig $config
     * @param string                       $propertyKey
     * @param array                        $requestData
     * @param AbstractShape                $valueShape
     *
     * @return mixed|void
     * @throws \Exception
     */
    public static function normalizeData(DataNormalizerProviderConfig $config, string $propertyKey, array $requestData, AbstractShape $valueShape)
    {
        if (!$valueShape instanceof StringShape && !$valueShape instanceof CustomDialogShape) {
            return;
        }
        $value = $valueShape->getValue();

        if ($valueShape instanceof CustomDialogShape) {
            $value = $valueShape->getValue()['title'];
        }

        $property = $config->getProperties()[$propertyKey];
        $references = $property->getData()->getReferences();
        $referenceTitleField = $references[0] . '__title';
        $referenceIdField = $references[0] . '__id';

        $dao = isys_cmdb_dao_dialog::instance(isys_application::instance()->container->get('database'));
        $dialogData = $dao->set_table($references[0])->load()->get_data();
        $data = array_filter($dialogData, fn ($item) => $item['title'] === $value);

        if (!empty($data)) {
            $valueShape->setValue((int) key($data));
        }
    }

    /**
     * @param DataNormalizerProviderConfig $config
     * @param string                       $propertyKey
     * @param array                        $requestData
     * @param AbstractShape                $valueShape
     *
     * @return mixed|void
     * @throws \Exception
     */
    public static function denormalizeData(DataNormalizerProviderConfig $config, string $propertyKey, array $requestData, AbstractShape $valueShape)
    {
        $value = $valueShape->getValue();

        if ($valueShape instanceof CustomDialogShape) {
            $value = $valueShape->getValue()['id'];
        }

        if (is_array($value)) {
            $value = current($value);
        }

        if (strlen((string)(int)$value) !== strlen((string)$value)) {
            return;
        }

        $property = $config->getProperties()[$propertyKey];
        $references = $property->getData()->getReferences();

        $title = self::getDialogTitleById($references, (int)$value);

        if ($title === null) {
            return;
        }

        $valueShape->setValue($title);
    }

    /**
     * Only check given values explicitly to save memory.
     *
     * @param array $reference
     * @param int   $id
     * @return string|null
     * @throws Exception
     */
    private static function getDialogTitleById(array $reference, int $id): ?string
    {
        if ($id <= 0) {
            return null;
        }

        $referenceTable = $reference[0];
        $referenceTitleField = $reference[2] ?? $referenceTable . '__title';
        $referenceIdField = $referenceTable . '__id';

        $cacheKey = "{$referenceTable}:{$id}";

        if (!array_key_exists($cacheKey, self::$dialogCache)) {
            $dao = isys_cmdb_dao_dialog::instance(isys_application::instance()->container->get('database'));
            self::$dialogCache[$cacheKey] = null;

            try {
                $query = "SELECT {$referenceTitleField} AS title
                    FROM {$referenceTable}
                    WHERE {$referenceIdField} = {$id}
                    LIMIT 1;";

                $title = $dao->retrieve($query)->get_row_value('title');

                if ($title === null) {
                    return null;
                }

                $title = trim($title);

                if (str_starts_with($title, 'LC_')) {
                    $title = isys_application::instance()->container->get('language')->get($title);
                }

                self::$dialogCache[$cacheKey] = $title;
            } catch (Throwable $e) {
                // Do not consider the exception.
            }
        }

        return self::$dialogCache[$cacheKey];
    }
}
