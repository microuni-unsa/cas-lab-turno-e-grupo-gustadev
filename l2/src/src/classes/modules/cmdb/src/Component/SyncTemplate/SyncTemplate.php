<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\SyncTemplate;

use Exception;
use idoit\Component\Helper\ArrayHelper;
use idoit\Component\PlaceholderReplacer\Config as PlaceholderConfig;
use idoit\Module\Cmdb\Component\SyncMerger\Config;
use idoit\Module\Cmdb\Component\SyncMerger\Merger;
use idoit\Module\Cmdb\Component\SyncTemplate\Exceptions\ProcessDataException;
use idoit\Module\Cmdb\Component\SyncTemplate\Processors\AbstractProcessor;
use idoit\Module\Cmdb\Component\SyncTemplate\Processors\Interfaces\PreSyncModifierInterface;
use idoit\Module\Cmdb\Search\Index\Signals;
use isys_auth;
use isys_auth_cmdb;
use isys_cmdb_dao;
use isys_cmdb_dao_category;
use isys_component_signalcollection;
use isys_event_manager;
use isys_import_handler_cmdb;

class SyncTemplate
{
    private ?isys_cmdb_dao $dao = null;

    private ?ProcessorProvider $processorProvider = null;

    /**
     * @var string
     */
    private string $categoryConstant = '';

    /**
     * @var array
     */
    private array $templateData = [];

    /**
     * @var int|null
     */
    private ?int $creationTime = null;

    /**
     * @var array
     */
    private array $globalCategories = [];

    /**
     * @var array
     */
    private array $specificCategories = [];

    /**
     * @var array
     */
    private array $templates = [];

    /**
     * @var array
     */
    private array $blockedCategories = [
        'C__CATG__CUSTOM_FIELDS',
        'C__CATG__DATABASE_ASSIGNMENT',
        'C__CATG__FILE',
        'C__CATG__IDENTIFIER',
        'C__CATG__IMAGE',
        'C__CATG__IMAGES',
        'C__CATG__LAST_LOGIN_USER',
        'C__CATG__LOGBOOK',
        'C__CATG__RELATION',
        'C__CATG__OVERVIEW',
        'C__CATS__PDU_OVERVIEW',
        'C__CATS__FILE',
        'C__CATS__FILE_VERSIONS',
        'C__CATS__FILE_ACTUAL',
        'C__CATS__FILE_OBJECTS',
        'C__CATS__RELATION_DETAILS',
        'C__CATS__PARALLEL_RELATION',
        'C__CATS__PDU_BRANCH',
        'C__CATS__CHASSIS',
        'C__CATS__CHASSIS_CABLING',
        'C__CATS__CHASSIS_DEVICES',
        'C__CATS__CHASSIS_VIEW',
        'C__CATS__DATABASE_SCHEMA',
        'C__CATS__DATABASE_ACCESS',
        'C__CATS__NET',
        'C__CATS__PDU',
        'C__CATS__PDU_BRANCH',
        'C__CATS__PDU_OVERVIEW',
        'C__CATS__BASIC_AUTH',
        'C__CATS__LICENCE_OVERVIEW',
        'C__CATS__PERSON_NAGIOS',
        'C__CATS__PERSON_GROUP_NAGIOS',
    ];

    /**
     * @var int|null
     */
    private ?int $objectId = null;
    private ?string $objectTitle = null;
    private ?int $objectTypeId = null;
    private ?string $sysid = null;
    private ?isys_component_signalcollection $signals = null;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->dao = isys_cmdb_dao::instance(\isys_application::instance()->container->get('database'));
        $this->processorProvider = ProcessorProvider::factory();
        $this->signals = \isys_application::instance()->container->get('signals');
    }


    /**
     * @param array $blockedCategories
     *
     * @return $this
     */
    protected function setBlockedCategories(array $blockedCategories): SyncTemplate
    {
        $this->blockedCategories = $blockedCategories;

        return $this;
    }

    /**
     * @param string|null $objectTitle
     *
     * @return $this
     */
    protected function setObjectTitle(?string $objectTitle): SyncTemplate
    {
        $this->objectTitle = $objectTitle;

        return $this;
    }

    /**
     * @param int|null $objectTypeId
     *
     * @return $this
     */
    protected function setObjectTypeId(?int $objectTypeId): SyncTemplate
    {
        $this->objectTypeId = $objectTypeId;

        return $this;
    }

    /**
     * @param string|null $sysid
     *
     * @return $this
     */
    protected function setSysid(?string $sysid): SyncTemplate
    {
        $this->sysid = $sysid;

        return $this;
    }

    /**
     * @param int[] $templates
     *
     * @return $this
     */
    protected function setTemplates(array $templates): SyncTemplate
    {
        $this->templates = $templates;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getObjectId(): ?int
    {
        return $this->objectId;
    }

    /**
     * @return string|null
     */
    public function getObjectTitle(): ?string
    {
        return $this->objectTitle;
    }

    /**
     * @param int         $objectTypeId
     * @param string|null $objectTitle
     * @param string|null $sysid
     * @param array|null  $blockedCategories
     *
     * @return SyncTemplate
     */
    public static function factory(int $objectTypeId, ?string $objectTitle, ?string $sysid = null, ?array $blockedCategories = [], ?array $templates = [])
    {
        $instance = new self();

        if (!empty($blockedCategories)) {
            $instance->setBlockedCategories($blockedCategories);
        }

        if ($objectTitle) {
            $objectTitle = \isys_application::instance()->container->get('idoit.component.placeholder-replacer')
                ->replacePlaceholder($objectTitle, PlaceholderConfig::factory(
                    (int)$instance->getDao()->getLastAutoIncrementIdFromTable('isys_obj'),
                    (int)$objectTypeId,
                    null,
                    $sysid,
                    'isys_catg_global_list'
                ));

            $instance->setObjectTitle($objectTitle);
        }

        $instance
            ->setTemplates($templates)
            ->setObjectTypeId($objectTypeId)
            ->setSysid($sysid)
            ->loadTemplate();

        return $instance;
    }

    /**
     * @return isys_cmdb_dao|null
     */
    protected function getDao(): ?isys_cmdb_dao
    {
        return $this->dao;
    }

    /**
     * @return void
     * @throws \isys_exception_cmdb
     * @throws \isys_exception_dao
     */
    public function create(): void
    {
        $this->creationTime = (int)microtime(true);
        $this->objectId = $this->dao->insert_new_obj($this->objectTypeId, null, $this->objectTitle, $this->sysid, C__RECORD_STATUS__NORMAL);

        if (isset($this->templateData['C__CATG__GLOBAL']) && $this->objectTitle) {
            $this->templateData['C__CATG__GLOBAL'][C__PROPERTY__DATA][0][Config::CONFIG_PROPERTIES]['title'][C__DATA__VALUE] = $this->objectTitle;
        }

        isys_event_manager::getInstance()
            ->triggerCMDBEvent(
                'C__LOGBOOK_EVENT__OBJECT_CREATED',
                '',
                $this->objectId,
                $this->objectTypeId,
                null,
                serialize([
                    'isys_cmdb_dao_category_g_global::title' => [
                        'from' => '',
                        'to' => $this->objectTitle
                    ]
                ]),
                null,
                null,
                null,
                null,
                1
            );

        $this->applyTemplateData();
        $this->generateIndex();
    }

    /**
     * @return void
     * @throws ProcessDataException|Exception
     */
    private function applyTemplateData(): void
    {
        if (empty($this->templateData)) {
            return;
        }

        $syncIssues = $syncData = [];
        foreach ($this->templateData as $categoryConst => $templateData) {
            if (empty($templateData['data']) || !isys_auth_cmdb::instance()->category(isys_auth::CREATE, $categoryConst)) {
                continue;
            }

            if ($categoryConst === 'C__CATG__GLOBAL') {
                unset($templateData['data'][0][Config::CONFIG_PROPERTIES]['type']);
            }

            foreach ($templateData['data'] as $entry) {
                if ($entry[Config::CONFIG_DATA_ID] > 0) {
                    $entry[Config::CONFIG_DATA_ID] = null;
                    $entry[Config::CONFIG_PROPERTIES]['id'] = null;
                    $syncData[$categoryConst][] = $entry;
                }
            }
        }

        foreach($syncData as $categoryConst => $entries) {
            $processor = $this->processorProvider->getProcessor($categoryConst);
            if ($processor instanceof AbstractProcessor) {
                $processor->setDependentSyncData($syncData);
            }

            $categoryData = AbstractProcessor::getByCategory([$categoryConst]);
            $className = $categoryData[0]['className'] ?? null;

            if (!class_exists($className)) {
                $syncIssues[$categoryConst][] = "The category dao for '{$categoryConst}' does not exist.";
                continue;
            }

            /** @var isys_cmdb_dao_category $categoryDao */
            $categoryDao = new $className($this->dao->get_database_component());

            $categoryClassName = get_class($categoryDao);

            if ($categoryDao->get_category_type() === C__CMDB__CATEGORY__TYPE_SPECIFIC) {
                $this->specificCategories[] = $categoryConst;
            } else {
                $this->globalCategories[] = $categoryConst;
            }

            foreach ($entries as $data) {
                $mode = isys_import_handler_cmdb::C__CREATE;

                try {
                    if ($processor instanceof PreSyncModifierInterface) {
                        $data = $processor->preSyncModify($data, $this->objectId);
                    }
                    $validationIssues = $categoryDao->validate($data);
                    if (is_array($validationIssues)) {
                        $syncIssues[$categoryConst] = array_merge($syncIssues[$categoryConst], $validationIssues);
                        continue;
                    }

                    $entryId = $this->getDao()->getLastAutoIncrementIdFromTable($categoryDao->get_table());
                    $categoryDao->sync($data, $this->objectId, $mode);

                    $changes = [];

                    foreach ($data[isys_import_handler_cmdb::C__PROPERTIES] as $key => $value) {
                        if (!isset($value[C__DATA__VALUE]) || empty($value[C__DATA__VALUE]) || trim($value[C__DATA__VALUE]) === '') {
                            continue;
                        }

                        $changes[$categoryClassName . '::' . $key] = [
                            'from' => '',
                            'to'   => $value[C__DATA__VALUE]
                        ];
                    }

                    // Emitting signal mod.cmdb.afterCreateCategoryEntry:
                    $this->signals->emit(
                        'mod.cmdb.afterCreateCategoryEntry',
                        $categoryDao->get_category_id(),
                        $entryId,
                        true,
                        $this->objectId,
                        $categoryDao
                    );

                    // Emitting signal mod.cmdb.afterCategorySync:
                    $this->signals->emit(
                        'mod.cmdb.afterCategorySync',
                        $categoryDao->get_category_id(),
                        $data,
                        $entryId,
                        $this->objectId,
                        C__RECORD_STATUS__NORMAL,
                        $categoryDao->get_category_type(),
                        $categoryDao
                    );

                    // Emit category signal (afterCategoryEntrySave).
                    $this->signals->emit(
                        "mod.cmdb.afterCategoryEntrySave",
                        $categoryDao,
                        $entryId,
                        true,
                        $this->objectId,
                        $data,
                        $changes
                    );

                    isys_event_manager::getInstance()
                        ->triggerCMDBEvent(
                            'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                            '',
                            $this->objectId,
                            $this->objectTypeId,
                            $categoryDao->getCategoryTitle(),
                            serialize($changes),
                            null,
                            null,
                            null,
                            null,
                            count($changes)
                        );
                } catch (Exception $e) {
                    $syncIssues[$categoryConst][] = $e->getMessage();
                }
            }
        }

        if (!empty($syncIssues)) {
            throw ProcessDataException::SyncIssues($syncIssues);
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    private function generateIndex(): void
    {
        Signals::instance()->onPostImport(
            $this->creationTime,
            array_unique($this->globalCategories),
            array_unique($this->specificCategories)
        );
    }

    /**
     * @return bool
     */
    protected function loadTemplate(): bool
    {
        $query = 'SELECT GROUP_CONCAT(isys_obj__id) as id FROM isys_obj
          LEFT JOIN isys_obj_type ON isys_obj_type__default_template = isys_obj__id
          WHERE isys_obj_type__id = ' . $this->dao->convert_sql_id($this->objectTypeId);

        if (!empty($this->templates)) {
            $query .= ' OR isys_obj__id IN (' . implode(',', array_map(fn ($class) => $this->dao->convert_sql_id($class), $this->templates)) . ')';
        }

        $templateInfo = $this->dao->retrieve($query)->get_row();

        if (!$templateInfo['id']) {
            return false;
        }

        $templates = array_map('intval', explode(',', $templateInfo['id']));
        $result = $this->dao->get_all_catg_2_objtype_id($this->objectTypeId);

        while ($data = $result->get_row()) {
            $categoryClass = $data['isysgui_catg__class_name'];
            $categoryConst = $data['isysgui_catg__const'];

            if (in_array($categoryConst, $this->blockedCategories) || !class_exists($categoryClass)) {
                continue;
            }

            /**
             * @var isys_cmdb_dao_category $categoryDao
             */
            $categoryDao = $categoryClass::instance($this->dao->get_database_component());
            $this->templateData[$categoryConst] = [
                'isMultivalued' => false,
                'data' => []
            ];

            if ($categoryDao->is_multivalued()) {
                $this->templateData[$categoryConst]['isMultivalued'] = true;
                $this->handleMultiValueTemplateEntries($categoryConst, $categoryDao, $templates);
                continue;
            }
            $this->handleSingleValueTemplateEntry($categoryConst, $categoryDao, $templates);
        }

        return true;
    }

    /**
     * @param string                 $categoryConst
     * @param isys_cmdb_dao_category $categoryDao
     * @param array                  $templateInfo
     */
    private function handleMultiValueTemplateEntries(string $categoryConst, isys_cmdb_dao_category $categoryDao, array $templateInfo): void
    {
        foreach ($templateInfo as $objId) {
            $result = $categoryDao->get_data(null, $objId);
            if ($result->count() === 0) {
                unset($this->templateData[$categoryConst]);

                return;
            }

            $table = $categoryDao->get_table();
            while ($data = $result->get_row()) {
                $entryId = $data[$table . '__id'];
                $fakeEntry = [
                    Config::CONFIG_DATA_ID    => $entryId ?? null,
                    Config::CONFIG_PROPERTIES => ['id' => $entryId ?? null]
                ];

                $categoryData = Merger::instance(Config::instance($categoryDao, $objId, $fakeEntry))
                    ->getDataForSync();
                $this->templateData[$categoryConst]['data'][] = $categoryData;
            }
        }
    }

    /**
     * @param string                 $categoryConst
     * @param isys_cmdb_dao_category $categoryDao
     * @param array                  $templateInfo
     */
    private function handleSingleValueTemplateEntry(string $categoryConst, isys_cmdb_dao_category $categoryDao, array $templateInfo): void
    {
        foreach ($templateInfo as $objId) {
            $table = $categoryDao->get_table();
            $entryId = $categoryDao->get_data(null, $objId)
                ->get_row_value($table . '__id');

            if (!$entryId) {
                continue;
            }

            $fakeEntry = [
                Config::CONFIG_DATA_ID    => $entryId ?? null,
                Config::CONFIG_PROPERTIES => ['id' => $entryId ?? null]
            ];

            $categoryData = Merger::instance(Config::instance($categoryDao, $objId, $fakeEntry))
                ->getDataForSync();

            if (isset($this->templateData[$categoryConst]['data'][0])) {
                $this->templateData[$categoryConst]['data'][0] = ArrayHelper::merge($this->templateData[$categoryConst]['data'][0], $categoryData);
                continue;
            }

            $this->templateData[$categoryConst]['data'][] = $categoryData;
        }
    }
}
