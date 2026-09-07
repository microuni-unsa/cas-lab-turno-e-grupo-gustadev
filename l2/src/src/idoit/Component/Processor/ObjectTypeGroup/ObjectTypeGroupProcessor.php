<?php

namespace idoit\Component\Processor\ObjectTypeGroup;

use idoit\Component\Processor\Dto\ObjectTypeGroup\CreateRequest;
use idoit\Component\Processor\Dto\ObjectTypeGroup\CreateResponse;
use idoit\Component\Processor\Dto\ObjectTypeGroup\Dto as ObjectTypeGroup;
use idoit\Component\Processor\Dto\ObjectTypeGroup\RankResponse;
use idoit\Component\Processor\Dto\ObjectTypeGroup\ReadRequest;
use idoit\Component\Processor\Dto\ObjectTypeGroup\ReadResponse;
use idoit\Component\Processor\Dto\ObjectTypeGroup\UpdateRequest;
use idoit\Component\Processor\Dto\ObjectTypeGroup\UpdateResponse;
use idoit\Component\Processor\Exception\AuthorizationException;
use idoit\Component\Processor\Exception\InternalSystemException;
use idoit\Component\Processor\Exception\ValidationException;
use Idoit\Dto\Serialization\Serializer;
use Idoit\Dto\Validation\Validation;
use Idoit\Dto\Validation\ValidationMessage;
use idoit\Exception\Exception;
use isys_auth;
use isys_auth_cmdb;
use isys_cmdb_dao;
use isys_component_template_language_manager;
use isys_helper;
use isys_module_cmdb;
use isys_module_system;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Throwable;

/**
 * Object type processor.
 */
class ObjectTypeGroupProcessor
{
    private static ObjectTypeGroupProcessor $instance;

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
     * @param ReadRequest $dto
     *
     * @return ReadResponse
     * @throws AuthorizationException
     * @throws InternalSystemException
     * @throws ValidationException
     * @throws \isys_exception_database
     */
    public function read(ReadRequest $dto): ReadResponse
    {
        try {
            $errors = Validation::validate($dto);

            if (count($errors)) {
                throw (new ValidationException('Validation errors:' . PHP_EOL . implode(
                    PHP_EOL,
                    array_map(fn ($error) => ($error instanceof ValidationMessage) ? implode(', ', $error->getPath()) . ': ' . $error->getMessage() : $error, $errors)
                )))->setErrors($errors);
            }

            // Object type groups currently have no own rights, signals or create logbook entries.

            $idCondition = $this->dao->prepare_in_condition($dto->ids);
            $statusCondition = count($dto->status) === 0
                ? ''
                : ' AND isys_obj_type_group__status' . $this->dao->prepare_in_condition($dto->status);

            $query = "SELECT
                isys_obj_type_group__id AS id,
                isys_obj_type_group__title AS titleRaw,
                isys_obj_type_group__const AS constant,
                isys_obj_type_group__description AS description,
                isys_obj_type_group__sort AS sort,
                isys_obj_type_group__status AS status
                FROM isys_obj_type_group
                WHERE isys_obj_type_group__id {$idCondition}
                {$statusCondition}";

            $objectTypeGroups = [];
            $queryResult = $this->dao->retrieve($query);

            while ($row = $queryResult->get_row()) {
                $row['title'] = $this->language->get($row['titleRaw']);
                $row['status'] = (int)$row['status'];

                $objectTypeGroups[] = Serializer::fromJson(ObjectTypeGroup::class, $row);
            }

            return new ReadResponse($objectTypeGroups);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\isys_exception_auth $exception) {
            throw new AuthorizationException($exception->getMessage(), 0, $exception);
        } catch (Throwable $exception) {
            throw new InternalSystemException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * @param int $id
     *
     * @return ReadResponse
     * @throws AuthorizationException
     * @throws InternalSystemException
     * @throws ValidationException
     * @throws \isys_exception_database
     */
    public function readById(int $id): ReadResponse
    {
        return $this->read(new ReadRequest([$id]));
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
        // Object type groups currently have no own rights, signals or create logbook entries.

        try {
            isys_module_system::getAuth()->check(isys_auth::DELETE, 'globalsettings/qcw');

            $response = $this->readById($id);

            if ($response->total() === 0) {
                throw new Exception("Object type group #{$id} does not exist.");
            }

            $objectTypeGroup = $response->first();

            $orphanedObjectTypeGroup = $this->dao->convert_sql_id(defined_or_default('C__OBJTYPE_GROUP__ORPHANED', null));

            // Move all object types to orphaned group!
            $sql = "UPDATE isys_obj_type
                SET isys_obj_type__isys_obj_type_group__id = {$orphanedObjectTypeGroup}
                WHERE isys_obj_type__isys_obj_type_group__id = {$objectTypeGroup->id}";

            $this->dao->update($sql);

            $sql = "DELETE FROM isys_obj_type_group
                WHERE isys_obj_type_group__id = {$objectTypeGroup->id}
                LIMIT 1;";

            $this->dao->update($sql);
            $this->dao->apply_update();

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
     * @param CreateRequest $dto
     *
     * @return CreateResponse
     * @throws AuthorizationException
     * @throws InternalSystemException
     * @throws ValidationException
     */
    public function create(CreateRequest $dto): CreateResponse
    {
        // Object type groups currently have no own rights, signals or create logbook entries.

        try {
            isys_module_system::getAuth()->check(isys_auth::CREATE, 'globalsettings/qcw');

            $errors = Validation::validate($dto);

            if (count($errors)) {
                throw (new ValidationException('Validation errors:' . PHP_EOL . implode(
                    PHP_EOL,
                    array_map(fn ($error) => ($error instanceof ValidationMessage) ? implode(', ', $error->getPath()) . ': ' . $error->getMessage() : $error, $errors)
                )))->setErrors($errors);
            }

            $objectTypeGroupId = $this->dao->insert_new_objtype_group(
                $dto->title,
                $dto->constant,
                $dto->sort,
                $dto->status ?? C__RECORD_STATUS__NORMAL
            );

            if (!is_numeric($objectTypeGroupId)) {
                throw new Exception('Object type could not be created, please try again.');
            }

            return new CreateResponse($objectTypeGroupId);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\isys_exception_auth $exception) {
            throw new AuthorizationException($exception->getMessage(), 0, $exception);
        } catch (Throwable $exception) {
            throw new InternalSystemException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * @param UpdateRequest $dto
     *
     * @return UpdateResponse
     * @throws AuthorizationException
     * @throws InternalSystemException
     * @throws ValidationException
     */
    public function update(UpdateRequest $dto): UpdateResponse
    {
        // Object type groups currently have no own rights, signals or create logbook entries.

        try {
            isys_module_system::getAuth()->check(isys_auth::EDIT, 'globalsettings/qcw');

            $errors = Validation::validate($dto);

            if (count($errors)) {
                throw (new ValidationException('Validation errors:' . PHP_EOL . implode(
                    PHP_EOL,
                    array_map(fn ($error) => ($error instanceof ValidationMessage) ? implode(', ', $error->getPath()) . ': ' . $error->getMessage() : $error, $errors)
                )))->setErrors($errors);
            }

            // Check if the object type exists.
            if ($this->readById($dto->id)->total() === 0) {
                throw new Exception("Object type group #{$dto->id} does not exist.");
            }

            $sqlData = [];

            // This is necessary to prevent the user from (accidentally) overwriting language constants.
            if ($dto->title !== null) {
                $sqlData[] = 'isys_obj_type_group__title = ' . $this->dao->convert_sql_text(isys_helper::sanitize_text($dto->title));
            }

            if ($dto->sort !== null) {
                $sqlData[] = "isys_obj_type_group__sort = {$dto->sort}";
            }

            if ($dto->status !== null) {
                $sqlData[] = "isys_obj_type_group__status = {$dto->status}";
            }

            if (count($sqlData) === 0) {
                return new UpdateResponse($dto->id);
            }

            $sql = "UPDATE isys_obj_type_group
                SET " . implode(', ', $sqlData) . "
                WHERE isys_obj_type_group__id = {$dto->id}
                LIMIT 1;";

            $this->dao->update($sql);
            $this->dao->apply_update();

            return new UpdateResponse($dto->id);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\isys_exception_auth $exception) {
            throw new AuthorizationException($exception->getMessage(), 0, $exception);
        } catch (Throwable $exception) {
            throw new InternalSystemException($exception->getMessage(), 0, $exception);
        }
    }
}
