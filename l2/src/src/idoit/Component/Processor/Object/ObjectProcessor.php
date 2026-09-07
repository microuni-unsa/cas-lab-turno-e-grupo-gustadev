<?php

namespace idoit\Component\Processor\Object;

use DateTime;
use DateTimeInterface;
use idoit\Component\Processor\Dto\Object\CreateRequest;
use idoit\Component\Processor\Dto\Object\CreateResponse;
use idoit\Component\Processor\Dto\Object\Dto as CiObject;
use idoit\Component\Processor\Dto\Object\RankResponse;
use idoit\Component\Processor\Dto\Object\ReadRequest;
use idoit\Component\Processor\Dto\Object\ReadResponse;
use idoit\Component\Processor\Dto\Object\UpdateRequest;
use idoit\Component\Processor\Dto\Object\UpdateResponse;
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
use isys_component_dao_lock;
use isys_component_template_language_manager;
use isys_helper_link;
use isys_module_cmdb;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Throwable;

/**
 * Object processor.
 */
class ObjectProcessor
{
    private static ObjectProcessor $instance;

    private isys_auth_cmdb $auth;

    private isys_cmdb_dao $dao;

    private isys_component_dao_lock $daoLock;

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
        $this->daoLock = isys_component_dao_lock::instance($this->dao->get_database_component());
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
            $objectCondition = 'WHERE isys_obj__id ' . $this->dao->prepare_in_condition($read->ids);
            $statusCondition = count($read->status) === 0 ? '' : 'AND isys_obj__status ' . $this->dao->prepare_in_condition($read->status);

            $query = "SELECT
                isys_obj__id AS id,
                isys_obj__title AS title,
                isys_obj__const AS constant,
                isys_obj__description AS description,
                isys_obj__id AS imageUrl,
                isys_obj__created AS created,
                isys_obj__created_by AS createdBy,
                isys_obj__updated AS updated,
                isys_obj__updated_by AS updatedBy,
                isys_obj__status AS status,
                isys_obj__sysid AS sysid,
                isys_obj__undeletable AS undeletable,
                isys_obj__owner_id AS ownerId,
                isys_obj__isys_cmdb_status__id AS cmdbStatus,
                isys_catg_global_list__isys_catg_global_category__id AS category,
                isys_catg_global_list__isys_purpose__id AS purpose,
                isys_obj__isys_obj_type__id AS objectType
                FROM isys_obj
                INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
                INNER JOIN isys_catg_global_list ON isys_catg_global_list__isys_obj__id = isys_obj__id
                {$objectCondition}
                {$statusCondition}";

            $objects = [];
            $queryResult = $this->dao->retrieve($query);

            while ($row = $queryResult->get_row()) {
                $objectId = (int)$row['isys_obj__id'];

                $this->auth->obj_id(isys_auth::VIEW, $objectId);

                $row['imageUrl'] = isys_helper_link::get_base() . '/' . $this->routeGenerator->generate('cmdb.object.image', ['objectId' => $row['imageUrl']]);
                $row['created'] = (new DateTime($row['created']))->format(DateTimeInterface::ATOM);
                $row['updated'] = (new DateTime($row['updated']))->format(DateTimeInterface::ATOM);
                $row['status'] = (int)$row['status'];
                $row['ownerId'] = is_numeric($row['ownerId']) ? (int)$row['ownerId'] : null;
                $row['cmdbStatus'] = (int)$row['cmdbStatus'];
                $row['category'] = is_numeric($row['category']) ? (int)$row['category'] : null;
                $row['purpose'] = is_numeric($row['purpose']) ? (int)$row['purpose'] : null;
                $row['objectType'] = (int)$row['objectType'];

                $objects[] = Serializer::fromJson(CiObject::class, $row);
            }

            return new ReadResponse($objects);
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
    public function archive(int $id): RankResponse
    {
        try {
            $response = $this->readById($id);

            if ($response->total() === 0) {
                throw new Exception("Object #{$id} does not exist.");
            }

            $object = $response->first();

            $this->auth->obj_id(isys_auth::ARCHIVE, $object->id);
            $this->rank($object, C__RECORD_STATUS__ARCHIVED);

            return new RankResponse($object->id);
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
     * @return RankResponse
     * @throws AuthorizationException
     * @throws InternalSystemException
     * @throws ValidationException
     */
    public function delete(int $id): RankResponse
    {
        try {
            $response = $this->readById($id);

            if ($response->total() === 0) {
                throw new Exception("Object #{$id} does not exist.");
            }

            $object = $response->first();

            $this->auth->obj_id(isys_auth::DELETE, $object->id);
            $this->rank($object, C__RECORD_STATUS__DELETED);

            return new RankResponse($object->id);
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
     * @return RankResponse
     * @throws AuthorizationException
     * @throws InternalSystemException
     * @throws ValidationException
     */
    public function purge(int $id): RankResponse
    {
        try {
            $response = $this->readById($id);

            if ($response->total() === 0) {
                throw new Exception("Object #{$id} does not exist.");
            }

            $object = $response->first();

            $this->auth->obj_id(isys_auth::SUPERVISOR, $object->id);
            $this->rank($object, C__RECORD_STATUS__PURGE);

            return new RankResponse($object->id);
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
     * @return RankResponse
     * @throws AuthorizationException
     * @throws InternalSystemException
     * @throws ValidationException
     */
    public function restore(int $id): RankResponse
    {
        try {
            $response = $this->readById($id);

            if ($response->total() === 0) {
                throw new Exception("Object #{$id} does not exist.");
            }

            $object = $response->first();

            $this->auth->obj_id(isys_auth::EDIT, $object->id);
            $this->rank($object, C__RECORD_STATUS__NORMAL);

            return new RankResponse($object->id);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\isys_exception_auth $exception) {
            throw new AuthorizationException($exception->getMessage(), 0, $exception);
        } catch (Throwable $exception) {
            throw new InternalSystemException($exception->getMessage(), 0, $exception);
        }
    }

    private function rank(CiObject $dto, int $targetStatus): void
    {
        $currentStatus = $dto->status->id;

        $rankIterations = $targetStatus - $currentStatus;
        $directionDelete = $rankIterations > 0;

        $this->lockObject($dto->id);

        for ($i = 0; $i < abs($rankIterations); $i++) {
            if ($directionDelete > 0) {
                $this->dao->rank_record($dto->id, C__CMDB__RANK__DIRECTION_DELETE, 'isys_obj', null, $targetStatus == C__RECORD_STATUS__PURGE);
            } else {
                $this->dao->rank_record($dto->id, C__CMDB__RANK__DIRECTION_RECYCLE, 'isys_obj');
            }
        }

        $this->unlockObject($dto->id);
    }

    private function lockObject(int $id): void
    {
        if ($this->daoLock->is_locked($id)) {
            $lockInformation = $this->daoLock->get_lock_information($id)->get_row();

            $lockedBy = $this->dao->get_obj_name_by_id_as_string($lockInformation['isys_user_session__isys_obj__id']);
            $remainingSeconds = (C__LOCK__TIMEOUT - (time() - strtotime($lockInformation['isys_lock__datetime'])));

            throw new Exception("The object is currently locked by user '{$lockedBy}' for at least {$remainingSeconds} seconds.");
        }

        $this->daoLock->add_lock($id);
    }

    private function unlockObject(int $id): void
    {
        $this->daoLock->delete_by_object_id($id);
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

            // Get necessary object type data.
            [$objectTypeId, $objectTypeConstant] = $this->getObjectTypeData($create->objectType);

            // Check, if the user is allowed to create objects of the given type.
            $this->auth->obj_in_type(isys_auth::CREATE, $objectTypeConstant);

            // Normalize data, in case a user passed string values or constants instead of IDs.
            $normalizedData = $this->normalize($create);

            // Run validation.
            if (is_array($validationIssues = \isys_cmdb_dao_category_g_global::instance($this->dao->get_database_component())->validate($normalizedData))) {
                foreach ($validationIssues as $key => $value) {
                    $errors[] = "{$key} => {$value}";
                }
            }

            if (count($errors)) {
                throw (new ValidationException('Validation errors:' . PHP_EOL . implode(
                    PHP_EOL,
                    array_map(fn ($error) => ($error instanceof ValidationMessage) ? implode(', ', $error->getPath()) . ': ' . $error->getMessage() : $error, $errors)
                )))->setErrors($errors);
            }

            $objectId = $this->dao->insert_new_obj(
                $objectTypeId,
                null, // unused
                $normalizedData['title'],
                $normalizedData['sysid'],
                $normalizedData['status'],
                null,  // Hostname
                null,  // Scan time
                false, // Import date
                null,  // Created
                null,  // Created by
                null,  // Updated
                null,  // Updated by
                $normalizedData['category'],
                $normalizedData['purpose'],
                $normalizedData['cmdb_status'],
                $normalizedData['description']
            );

            // Write logbook entry after creating object.
            \isys_event_manager::getInstance()
                ->triggerCMDBEvent(
                    'C__LOGBOOK_EVENT__OBJECT_CREATED',
                    '',
                    $objectId,
                    $objectTypeId,
                    $this->language->get('LC__CMDB__CATG__GLOBAL')
                );

            return new CreateResponse($objectId);
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

            $this->auth->obj_id(isys_auth::EDIT, $update->id);

            $objectTypeId = null;

            if ($update->objectType) {
                // Get necessary object type data.
                [$objectTypeId,] = $this->getObjectTypeData($update->objectType);
            }

            // Normalize data, in case a user passed string values or constants instead of IDs.
            $normalizedData = $this->normalize($update);

            $this->lockObject($update->id);

            $this->dao->update_object(
                $update->id,
                $objectTypeId,
                $normalizedData['title'],
                $normalizedData['description'],
                $normalizedData['sysid'],
                $normalizedData['status'],
                null, // Hostname
                null, // Scan time
                null, // Created
                null, // Created by
                true, // Updated
                null, // Updated by
                $normalizedData['cmdb_status'],
                null, // RT CF ID
                $normalizedData['category'],
                $normalizedData['purpose']
            );

            // Write logbook entry after creating object.
            \isys_event_manager::getInstance()
                ->triggerCMDBEvent(
                    'C__LOGBOOK_EVENT__OBJECT_CHANGED',
                    '',
                    $update->id,
                    $objectTypeId === null ? $this->dao->get_objTypeID($update->id) : $objectTypeId,
                    $this->language->get('LC__CMDB__CATG__GLOBAL')
                );

            $this->unlockObject($update->id);

            return new UpdateResponse($update->id);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\isys_exception_auth $exception) {
            throw new AuthorizationException($exception->getMessage(), 0, $exception);
        } catch (Throwable $exception) {
            throw new InternalSystemException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * @param int|string $objectType
     *
     * @return array
     * @throws Exception
     * @throws \isys_exception_database
     */
    private function getObjectTypeData(int|string $objectType): array
    {
        if (is_string($objectType)) {
            $objectTypeConstant = $objectType;
            $objectTypeId = constant($objectType);
        } elseif (is_int($objectType)) {
            $objectTypeId = $objectType;
            $objectTypeConstant = $this->dao
                ->retrieve("SELECT isys_obj_type__const AS constant FROM isys_obj_type WHERE isys_obj_type__id = {$objectTypeId} LIMIT 1;")
                ->get_row_value('constant');

            if ($objectTypeConstant === null) {
                throw new Exception('The passed type ID "' . $objectType . '" does not exist.');
            }
        } else {
            throw new Exception('The passed type must be either a objec type constant (string) or ID (int).');
        }

        return [(int)$objectTypeId, $objectTypeConstant];
    }

    /**
     * Method to normalize given string or int values to represent proper (existing) ID values.
     *
     * @param CreateRequest|UpdateRequest $dto
     *
     * @return array
     * @throws \isys_exception_database
     */
    private function normalize(CreateRequest|UpdateRequest $dto): array
    {
        $category = null;
        $purpose = null;
        $cmdbStatus = null;

        if (isset($dto->category)) {
            if (is_int($dto->category)) {
                $category = $dto->category;
            } elseif (is_string($dto->category)) {
                $category = $this->dao->getIdByConstant($dto->category, 'isys_catg_global_category');
            }
        }

        if (isset($dto->purpose)) {
            if (is_int($dto->purpose)) {
                $purpose = $dto->purpose;
            } elseif (is_string($dto->purpose)) {
                $purpose = $this->dao->getIdByConstant($dto->purpose, 'isys_purpose');
            }
        }

        if (isset($dto->cmdbStatus)) {
            if (is_int($dto->cmdbStatus)) {
                $cmdbStatus = $dto->cmdbStatus;
            } elseif (is_string($dto->cmdbStatus)) {
                $cmdbStatus = $this->dao->getIdByConstant($dto->cmdbStatus, 'isys_cmdb_status');
            }
        }

        return [
            'title'       => (is_string($dto->title) ? trim($dto->title) : '') ?: null,
            'sysid'       => $dto->sysid,
            'status'      => $dto->status ?? C__RECORD_STATUS__NORMAL,
            'category'    => $category,
            'purpose'     => $purpose,
            'cmdb_status' => $cmdbStatus,
            'description' => $dto->description,
        ];
    }
}
