<?php

namespace idoit\Component\Processor\ObjectType;

use idoit\Component\Processor\Dto\ObjectType\CreateRequest;
use idoit\Component\Processor\Dto\ObjectType\CreateResponse;
use idoit\Component\Processor\Dto\ObjectType\Dto as ObjectType;
use idoit\Component\Processor\Dto\ObjectType\RankResponse;
use idoit\Component\Processor\Dto\ObjectType\ReadRequest;
use idoit\Component\Processor\Dto\ObjectType\ReadResponse;
use idoit\Component\Processor\Dto\ObjectType\UpdateRequest;
use idoit\Component\Processor\Dto\ObjectType\UpdateResponse;
use idoit\Component\Processor\Exception\AuthorizationException;
use idoit\Component\Processor\Exception\InternalSystemException;
use idoit\Component\Processor\Exception\ValidationException;
use idoit\Component\Processor\Helper;
use idoit\Context\Context;
use Idoit\Dto\Serialization\Serializer;
use Idoit\Dto\Validation\Validation;
use Idoit\Dto\Validation\ValidationMessage;
use idoit\Exception\Exception;
use idoit\Module\Cmdb\Model\CiTypeCategoryAssigner;
use isys_application;
use isys_auth;
use isys_auth_cmdb;
use isys_auth_cmdb_object_types;
use isys_cache;
use isys_cmdb_dao;
use isys_component_signalcollection;
use isys_component_template_language_manager;
use isys_helper;
use isys_helper_color;
use isys_module_cmdb;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Throwable;

/**
 * Object type processor.
 */
class ObjectTypeProcessor
{
    private static ObjectTypeProcessor $instance;

    private isys_auth_cmdb $auth;

    private isys_cmdb_dao $dao;

    private isys_component_template_language_manager $language;

    private UrlGenerator $routeGenerator;

    public static function instance(isys_cmdb_dao $dao, isys_component_template_language_manager $language, UrlGenerator $routeGenerator): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($dao, $language, $routeGenerator);
        }

        return self::$instance;
    }

    private function __construct(isys_cmdb_dao $dao, isys_component_template_language_manager $language, UrlGenerator $routeGenerator)
    {
        $this->auth = isys_module_cmdb::getAuth();
        $this->dao = $dao;
        $this->language = $language;
        $this->routeGenerator = $routeGenerator;
    }

    /**
     * @param ReadRequest $read
     *
     * @return ReadResponse
     * @throws AuthorizationException
     * @throws InternalSystemException
     * @throws ValidationException
     */
    public function read(ReadRequest $read): ReadResponse
    {
        try {
            $objectTypeCondition = 'WHERE isys_obj_type__id ' . $this->dao->prepare_in_condition($read->ids);
            $statusCondition = count($read->status) === 0
                ? ''
                : 'AND isys_obj_type__status ' . $this->dao->prepare_in_condition($read->status);

            $query = "SELECT
                isys_obj_type__id AS id,
                isys_obj_type__isys_obj_type_group__id AS objectTypeGroup,
                isys_obj_type__title AS titleRaw,
                isys_obj_type__const AS constant,
                isys_obj_type__description AS description,
                isys_obj_type__id AS image,
                isys_obj_type__id AS icon,
                isys_obj_type__isysgui_cats__id AS specificCategory,
                isys_obj_type__selfdefined AS isSelfDefined,
                isys_obj_type__container AS isContainer,
                isys_obj_type__show_in_rack AS isPositionableInRack,
                isys_obj_type__show_in_tree AS isVisible,
                isys_obj_type__relation_master AS isRelationMaster,
                isys_obj_type__overview AS hasOverviewPage,
                isys_obj_type__sort AS sort,
                isys_obj_type__color AS color,
                isys_obj_type__default_template AS defaultTemplate,
                isys_obj_type__status AS status,
                isys_obj_type__sysid_prefix AS sysidPrefix
                FROM isys_obj_type
                {$objectTypeCondition}
                {$statusCondition}";

            $objectTypes = [];
            $queryResult = $this->dao->retrieve($query);

            // Check if we are allowed to read object types.
            $allowedObjectTypes = isys_auth_cmdb_object_types::instance()->get_allowed_objecttypes(isys_auth::VIEW);

            if ($allowedObjectTypes === false) {
                return new ReadResponse([]);
            }

            while ($row = $queryResult->get_row()) {
                if (is_array($allowedObjectTypes) && !in_array($row['id'], $allowedObjectTypes)) {
                    continue;
                }

                $assignedCategories = [];
                $categoriesOnOverviewPage = [];

                if ($read->withCategories) {
                    $assignedCategories = Helper::prepareAssignedCategories((int)$row['id']);
                    $categoriesOnOverviewPage = Helper::prepareOverviewCategories((int)$row['id'], $row['constant']);
                }

                $row['title'] = $this->language->get($row['titleRaw']);
                $row['objectTypeGroup'] = is_numeric($row['objectTypeGroup']) ? (int)$row['objectTypeGroup'] : null;
                $row['image'] = $this->routeGenerator->generate('cmdb.object-type.image', ['objectTypeId' => $row['id']]);
                $row['icon'] = $this->routeGenerator->generate('cmdb.object-type.icon', ['objectTypeId' => $row['id']]);
                $row['status'] = (int)$row['status'];
                $row['specificCategory'] = is_numeric($row['specificCategory']) ? (int)$row['specificCategory'] : null;
                $row['sort'] = (int)($row['sort'] ?? C__PROPERTY__DEFAULT_SORT);
                $row['defaultTemplate'] = is_numeric($row['defaultTemplate']) ? (int)$row['defaultTemplate'] : null;
                $row['assignedCategories'] = $assignedCategories;
                $row['categoriesOnOverviewPage'] = $categoriesOnOverviewPage;

                $objectTypes[] = Serializer::fromJson(ObjectType::class, $row);
            }

            return new ReadResponse($objectTypes);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\isys_exception_auth $exception) {
            throw new AuthorizationException($exception->getMessage(), 0, $exception);
        } catch (Throwable $exception) {
            throw new InternalSystemException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * @param int  $id
     * @param bool $withCategories
     *
     * @return ReadResponse
     * @throws AuthorizationException
     * @throws InternalSystemException
     * @throws ValidationException
     */
    public function readById(int $id, bool $withCategories = false): ReadResponse
    {
        return $this->read(new ReadRequest([$id], [], $withCategories));
    }

    /**
     * @param int $id
     *
     * @return RankResponse
     * @throws AuthorizationException
     * @throws InternalSystemException
     * @throws ValidationException
     */
    public function purge(int $id): RankResponse
    {
        try {
            // Check, if the user is allowed to purge object types.
            $this->auth->check(isys_auth::DELETE, 'OBJ_TYPE');

            $response = $this->readById($id);

            if ($response->total() === 0) {
                throw new Exception("Object type #{$id} does not exist.");
            }

            /** @var isys_component_signalcollection $signal */
            $signal = isys_application::instance()->container->get('signals');

            Context::instance()
                ->setContextTechnical(Context::CONTEXT_OBJECT_TYPE_PURGE)
                ->setGroup(Context::CONTEXT_GROUP_DAO)
                ->setContextCustomer(Context::CONTEXT_OBJECT_TYPE_PURGE);

            $objectType = $response->first();

            $this->auth->check(isys_auth::DELETE, 'OBJ_TYPE/' . $objectType->constant);

            // Check if the object type is deletable.
            if (!$objectType->isSelfDefined) {
                throw new Exception("Object type: '{$objectType->title}' is not self-defined.");
            }

            $signalObjectTypeData = $this->dao->get_objtype($objectType->id)->get_row();

            $signal->emit('mod.cmdb.beforeObjectTypePurge', $objectType->id, $objectType->title, $signalObjectTypeData);

            // Check if the object type contains any objects.
            $countQuery = "SELECT COUNT(1) AS cnt FROM isys_obj WHERE isys_obj__isys_obj_type__id = {$objectType->id};";
            $objectNum = (int)$this->dao->retrieve($countQuery)->get_row_value('cnt');

            if ($objectNum > 0) {
                throw new Exception("The object type '{$objectType->title}' contains {$objectNum} objects! Delete them in order to delete this type.");
            }

            $assignmentTables = [
                'isys_obj_type_2_isysgui_catg' => 'isys_obj_type_2_isysgui_catg__isys_obj_type__id',
                'isys_obj_type_2_isysgui_catg_custom' => 'isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id',
                'isys_obj_type_2_isysgui_catg_custom_overview' => 'isys_obj_type__id',
                'isys_obj_type_2_isysgui_catg_overview' => 'isys_obj_type__id',
            ];

            $this->dao->begin_update();

            foreach ($assignmentTables as $table => $field) {
                $removeCategoryAssignmentQuery = "DELETE
                FROM {$table}
                WHERE {$field} = {$objectType->id};";

                $this->dao->update($removeCategoryAssignmentQuery);
            }

            $sql = 'DELETE FROM isys_obj_type WHERE isys_obj_type__id = ' . $this->dao->convert_sql_id($objectType->id) . ';';

            $this->dao->update($sql);
            $this->dao->apply_update();

            $signal->emit("mod.cmdb.afterObjectTypeDeleted", $objectType->id, $this->dao->get_object_type($objectType->id));

            \isys_event_manager::getInstance()
                ->triggerCMDBEvent('C__LOGBOOK_EVENT__OBJECTTYPE_PURGED', '', null, $objectType->id, $objectType->title);

            $signal->emit('mod.cmdb.afterObjectTypePurge', $objectType->id, $objectType->title, true, $signalObjectTypeData);

            $this->clearCaches();

            return new RankResponse($id);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\isys_exception_auth $exception) {
            throw new AuthorizationException($exception->getMessage(), 0, $exception);
        } catch (Throwable $exception) {
            throw new InternalSystemException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * @param CreateRequest $create
     *
     * @return CreateResponse
     * @throws AuthorizationException
     * @throws InternalSystemException
     * @throws ValidationException
     */
    public function create(CreateRequest $create): CreateResponse
    {
        try {
            $errors = Validation::validate($create);

            // Check, if the user is allowed to create objects of the given type.
            $this->auth->check(isys_auth::EDIT, 'OBJ_TYPE');

            if (count($errors)) {
                throw (new ValidationException('Validation errors:' . PHP_EOL . implode(
                    PHP_EOL,
                    array_map(fn ($error) => ($error instanceof ValidationMessage) ? implode(', ', $error->getPath()) . ': ' . $error->getMessage() : $error, $errors)
                )))->setErrors($errors);
            }

            // Normalize data, in case a user passed string values or constants instead of IDs.
            $normalizedData = $this->normalize($create);

            $objectTypeId = $this->dao->insert_new_objtype(
                $normalizedData['objectTypeGroup'],
                $normalizedData['title'],
                $normalizedData['constant'],
                true,
                $normalizedData['isContainer'],
                $normalizedData['image'],
                $normalizedData['icon'],
                $normalizedData['sort'],
                C__RECORD_STATUS__NORMAL,
                $normalizedData['specificCategory'],
                $normalizedData['isVisible'],
                $normalizedData['sysidPrefix'],
                $normalizedData['defaultTemplate'],
                $normalizedData['isPositionableInRack'],
                $normalizedData['color']
            );

            if (!is_numeric($objectTypeId)) {
                throw new Exception('Object type could not be created, please try again.');
            }

            $sql = "UPDATE isys_obj_type SET
                isys_obj_type__overview = {$this->dao->convert_sql_boolean($normalizedData['hasOverviewPage'])},
                isys_obj_type__relation_master = {$this->dao->convert_sql_boolean($normalizedData['isRelationMaster'])},
                isys_obj_type__description = {$this->dao->convert_sql_text($normalizedData['description'])}
                WHERE isys_obj_type__id = {$objectTypeId}
                LIMIT 1;";

            $this->dao->update($sql);
            $this->dao->apply_update();

            \isys_event_manager::getInstance()
                ->triggerCMDBEvent('C__LOGBOOK_EVENT__OBJECTTYPE_CREATED', '', null, $objectTypeId);

            // Assign default categories to the newly created object type.
            CiTypeCategoryAssigner::instance($this->dao->get_database_component())
                ->setDefaultCategories()
                ->setCiTypes([$objectTypeId])
                ->assign();

            $this->clearCaches();

            return new CreateResponse($objectTypeId);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\isys_exception_auth $exception) {
            throw new AuthorizationException($exception->getMessage(), 0, $exception);
        } catch (Throwable $exception) {
            throw new InternalSystemException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * @param UpdateRequest $update
     *
     * @return UpdateResponse
     * @throws AuthorizationException
     * @throws InternalSystemException
     * @throws ValidationException
     */
    public function update(UpdateRequest $update): UpdateResponse
    {
        try {
            $errors = Validation::validate($update);

            if (count($errors)) {
                throw (new ValidationException('Validation errors:' . PHP_EOL . implode(
                    PHP_EOL,
                    array_map(fn ($error) => ($error instanceof ValidationMessage) ? implode(', ', $error->getPath()) . ': ' . $error->getMessage() : $error, $errors)
                )))->setErrors($errors);
            }

            // Check if the object type exists.
            $objectType = $this->readById($update->id);

            if ($objectType->total() === 0) {
                throw new Exception("Object type #{$update->id} does not exist.");
            }

            // Check, if the user is allowed to create objects of the given type.
            $this->auth->check(isys_auth::EDIT, 'OBJ_TYPE/' . $objectType->first()->constant);

            Context::instance()
                ->setContextTechnical(Context::CONTEXT_OBJECT_TYPE_SAVE)
                ->setGroup(Context::CONTEXT_GROUP_DAO)
                ->setContextCustomer(Context::CONTEXT_OBJECT_TYPE_SAVE);

            // Normalize data, in case a user passed string values or constants instead of IDs.
            $normalizedData = $this->normalize($update);

            $sqlData = [];

            // This is necessary to prevent the user from (accidentally) overwriting language constants.
            if ($normalizedData['title'] !== null) {
                $sqlData[] = 'isys_obj_type__title = ' . $this->dao->convert_sql_text(isys_helper::sanitize_text($normalizedData['title']));
            }

            if ($normalizedData['objectTypeGroup'] !== null) {
                $sqlData[] = 'isys_obj_type__isys_obj_type_group__id = ' . $this->dao->convert_sql_id($normalizedData['objectTypeGroup']);
            }

            if ($normalizedData['description'] !== null) {
                $sqlData[] = 'isys_obj_type__description = ' . $this->dao->convert_sql_text($normalizedData['description']);
            }

            if ($normalizedData['image'] !== null) {
                $sqlData[] = 'isys_obj_type__obj_img_name = ' . $this->dao->convert_sql_text($normalizedData['image']);
            }

            if ($normalizedData['icon'] !== null) {
                $sqlData[] = 'isys_obj_type__icon = ' . $this->dao->convert_sql_text($normalizedData['icon']);
            }

            if ($normalizedData['specificCategory'] !== null) {
                $sqlData[] = 'isys_obj_type__isysgui_cats__id = ' . $this->dao->convert_sql_id($normalizedData['specificCategory']);
            }

            if ($normalizedData['isContainer'] !== null) {
                $sqlData[] = 'isys_obj_type__container = ' . $this->dao->convert_sql_boolean($normalizedData['isContainer']);
            }

            if ($normalizedData['isPositionableInRack'] !== null) {
                $sqlData[] = 'isys_obj_type__show_in_rack = ' . $this->dao->convert_sql_boolean($normalizedData['isPositionableInRack']);
            }

            if ($normalizedData['isVisible'] !== null) {
                $sqlData[] = 'isys_obj_type__show_in_tree = ' . $this->dao->convert_sql_boolean($normalizedData['isVisible']);
            }

            if ($normalizedData['isRelationMaster'] !== null) {
                $sqlData[] = 'isys_obj_type__relation_master = ' . $this->dao->convert_sql_boolean($normalizedData['isRelationMaster']);
            }

            if ($normalizedData['hasOverviewPage'] !== null) {
                $sqlData[] = 'isys_obj_type__overview = ' . $this->dao->convert_sql_boolean($normalizedData['hasOverviewPage']);
            }

            if ($normalizedData['sort'] !== null) {
                $sqlData[] = 'isys_obj_type__sort = ' . $this->dao->convert_sql_int($normalizedData['sort']);
            }

            if ($normalizedData['color'] !== null) {
                $sqlData[] = 'isys_obj_type__color = ' . $this->dao->convert_sql_text($normalizedData['color']);
            }

            if ($normalizedData['defaultTemplate'] !== null) {
                $sqlData[] = 'isys_obj_type__default_template = ' . $this->dao->convert_sql_id($normalizedData['defaultTemplate']);
            }

            if ($normalizedData['sysidPrefix'] !== null) {
                $sqlData[] = 'isys_obj_type__sysid_prefix = ' . $this->dao->convert_sql_text($normalizedData['sysidPrefix']);
            }

            if (count($sqlData) === 0) {
                return new UpdateResponse($update->id);
            }

            /** @var isys_component_signalcollection $signal */
            $signal = isys_application::instance()->container->get('signals');

            $signalPostData = $this->preparePostDataStructure($update->id, $normalizedData);

            $signal->emit("mod.cmdb.beforeObjectTypeSave", $update->id, $signalPostData);

            $sql = "UPDATE isys_obj_type
                SET " . implode(', ', $sqlData) . "
                WHERE isys_obj_type__id = {$update->id}
                LIMIT 1;";

            \isys_event_manager::getInstance()
                ->triggerCMDBEvent('C__LOGBOOK_EVENT__OBJECTTYPE_CHANGED', '', null, $update->id);

            $signal->emit("mod.cmdb.afterObjectTypeSave", $update->id, $signalPostData, true);

            $this->dao->update($sql);
            $this->dao->apply_update();

            $this->clearCaches();

            return new UpdateResponse($update->id);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\isys_exception_auth $exception) {
            throw new AuthorizationException($exception->getMessage(), 0, $exception);
        } catch (Throwable $exception) {
            throw new InternalSystemException($exception->getMessage(), 0, $exception);
        }
    }

    private function preparePostDataStructure(int $id, array $data): array
    {
        $objectTypeData = $this->readById($id)->first();

        $postData = [
            'navMode'                              => C__NAVMODE__SAVE,
            'id'                                   => $id,
            'C__OBJTYPE__TITLE'                    => $data['title'] ?? $objectTypeData->title,
            'C__OBJTYPE__SYSID_PREFIX'             => $data['sysidPrefix'] ?? $objectTypeData->sysidPrefix,
            // 'C__OBJTYPE__AUTOMATED_INVENTORY_NO'   => '',
            'C__OBJTYPE__POSITION_IN_TREE'         => $data['objectTypeGroup'] ?? $objectTypeData->sort,
            'C__OBJTYPE__COLOR'                    => isys_helper_color::unifyHexColor($data['color'] ?? $objectTypeData->color),
            'C__OBJTYPE__GROUP_ID'                 => $data['objectTypeGroup'] ?? $objectTypeData->objectTypeGroup->id,
            'C__OBJTYPE__CATS_ID'                  => $data['specificCategory'] ?? $objectTypeData?->specificCategory->id,
            'C__OBJTYPE__SELF_DEFINED'             => $objectTypeData->isSelfDefined, // Can not be changed
            'C__OBJTYPE__IS_CONTAINER'             => $data['isContainer'] ?? $objectTypeData->isContainer,
            'C__OBJTYPE__RELATION_MASTER'          => $data['isRelationMaster'] ?? $objectTypeData->isRelationMaster,
            'C__OBJTYPE__INSERTION_OBJECT'         => $data['isPositionableInRack'] ?? $objectTypeData->isPositionableInRack,
            'C__OBJTYPE__SHOW_IN_TREE'             => $data['isVisible'] ?? $objectTypeData->isVisible,
            'C__OBJTYPE__IMG_NAME'                 => $data['image'] ?? $objectTypeData->image,
            'C__OBJTYPE__ICON'                     => $data['icon'] ?? $objectTypeData->icon,
            'C__OBJTYPE__CONST'                    => $objectTypeData->constant, // Can not be changed
            'C__CMDB__OBJTYPE__DEFAULT_TEMPLATE'   => $data['defaultTemplate'] ?? $objectTypeData?->defaultTemplate->id,
            // 'C__CMDB__OBJTYPE__USE_TEMPLATE_TITLE' => '0',
            'C__CMDB__OVERVIEW__ENTRY_POINT'       => $objectTypeData->hasOverviewPage,
            // 'assigned_categories'                  => ['C__CATG__ACCESS', 'C__CATG__VIRTUAL_AUTH', ...],
            // 'assigned_cat_overview'                => ['C__CATG__ACCESS', 'C__CATG__FORMFACTOR', ...],
            'C__OBJTYPE__DESCRIPTION'              => $objectTypeData->description
        ];

        // Use array_filter to remove unset data.
        return array_filter($postData);
    }

    private function normalize(CreateRequest|UpdateRequest $dto): array
    {
        $objectTypeGroup = null;
        $specificCategory = null;
        $defaultTemplate = null;

        if (is_int($dto->objectTypeGroup) || is_string($dto->objectTypeGroup)) {
            $objectTypeGroup = $this->dao->getValueFromTable($dto->objectTypeGroup, 'isys_obj_type_group');

            if ($objectTypeGroup === null) {
                throw new Exception('The parameter "objectTypeGroup" needs to contain a valid object type group constant (string) or ID (int)');
            }
        }

        if (is_int($dto->specificCategory) || is_string($dto->specificCategory)) {
            // Passing a 'nullish' value (like "-1") is fine in this context to remove the assignment.
            $specificCategory = $this->dao->getValueFromTable($dto->specificCategory, 'isysgui_cats', true);

            if ($specificCategory === null) {
                throw new Exception('The parameter "specificCategory" needs to contain a valid specific category constant (string) or ID (int)');
            }
        }

        if (is_int($dto->defaultTemplate) || is_string($dto->defaultTemplate)) {
            // Passing a 'nullish' value (like "-1") is fine in this context to remove the assignment.
            $defaultTemplate = $this->dao->getValueFromTable($dto->defaultTemplate, 'isys_obj', true);

            if ($defaultTemplate === null) {
                throw new Exception('The provided default template object "' . $dto->defaultTemplate . '" does not exist.');
            }

            // Verify that we got a 'template' object.
            if ($defaultTemplate > 0) {
                $templateStatus = $this->dao->convert_sql_int(C__RECORD_STATUS__TEMPLATE);

                $query = "SELECT isys_obj__id AS id
                    FROM isys_obj
                    WHERE isys_obj__id = {$defaultTemplate}
                    AND isys_obj__status = {$templateStatus}
                    LIMIT 1;";

                if (!$this->dao->retrieve($query)->get_row_value('id')) {
                    throw new Exception('The provided default template object "' . $dto->defaultTemplate . '" is no template.');
                }
            }
        }

        $title = $dto->title === null ? null : trim($dto->title);

        return [
            'title'                => $title,
            'objectTypeGroup'      => $objectTypeGroup,
            'constant'             => $dto->constant ?? 'C__OBJTYPE__SD_' . $title,
            'description'          => $dto->description,
            'image'                => $dto->image,
            'icon'                 => $dto->icon,
            'specificCategory'     => $specificCategory,
            'isContainer'          => $dto->isContainer,
            'isPositionableInRack' => $dto->isPositionableInRack,
            'isVisible'            => $dto->isVisible,
            'isRelationMaster'     => $dto->isRelationMaster,
            'hasOverviewPage'      => $dto->hasOverviewPage,
            'sort'                 => $dto->sort,
            'color'                => $dto->color,
            'defaultTemplate'      => $defaultTemplate,
            'sysidPrefix'          => $dto->sysidPrefix,
        ];
    }

    private function clearCaches(): void
    {
        // Removing isys_cache values.
        isys_cache::keyvalue()
            ->flush();

        // Clear contents of the 'temp' directory.
        (new Filesystem())->remove(glob(rtrim(isys_glob_get_temp_dir(), '/') . '/*'));

        // Trigger the 'after flush system cache' signal.
        /** @var isys_component_signalcollection $signal */
        isys_application::instance()->container->get('signals')->emit('system.afterFlushSystemCache');
    }
}
