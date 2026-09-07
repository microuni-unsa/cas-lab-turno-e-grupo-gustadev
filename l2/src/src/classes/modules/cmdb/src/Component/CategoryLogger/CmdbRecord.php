<?php

namespace idoit\Module\Cmdb\Component\CategoryLogger;

class CmdbRecord
{
    /**
     * @var string
     */
    private string $eventConstant = '';

    /**
     * @var string|null
     */
    private ?string $eventDescription = null;

    /**
     * @var int|null
     */
    private ?int $objectId = null;

    /**
     * @var int|null
     */
    private ?int $objectTypeId = null;

    /**
     * @var string|null
     */
    private ?string $categoryTitle = null;

    /**
     * @var array|null
     */
    private ?array $changes = null;

    /**
     * @var string|null
     */
    private ?string $comment = null;

    /**
     * @var int|null
     */
    private ?int $reason = null;

    /**
     * @var string|null
     */
    private ?string $objectTitle = null;

    /**
     * @var string|null
     */
    private ?string $entryIdentifier = null;

    /**
     * @var int
     */
    private int $countChanges = 0;

    /**
     * @param string $eventConstant
     * @param string|null $eventDescription
     * @param int|null $objectId
     * @param int|null $objectTypeId
     * @param string|null $categoryTitle
     * @param array|null $changes
     * @param string|null $comment
     * @param int|null $reason
     * @param string|null $objectTitle
     * @param string|null $entryIdentifier
     * @param int|null $countChanges
     */
    public function __construct(
        string $eventConstant,
        ?string $eventDescription,
        ?int $objectId,
        ?int $objectTypeId,
        ?string $categoryTitle,
        ?array $changes = [],
        ?string $comment = '',
        ?int $reason = null,
        ?string $objectTitle = '',
        ?string $entryIdentifier = '',
        ?int $countChanges = 0
    ) {
        $this->eventConstant = $eventConstant;
        $this->eventDescription = $eventDescription;
        $this->objectId = $objectId;
        $this->objectTypeId = $objectTypeId;
        $this->categoryTitle = $categoryTitle;
        $this->changes = $changes;
        $this->comment = $comment;
        $this->reason = $reason;
        $this->objectTitle = $objectTitle;
        $this->entryIdentifier = $entryIdentifier;
        $this->countChanges = $countChanges;
    }

    /**
     * @return string
     */
    public function getEventConstant(): string
    {
        return $this->eventConstant;
    }

    /**
     * @return string|null
     */
    public function getEventDescription(): ?string
    {
        return $this->eventDescription;
    }

    /**
     * @return int|null
     */
    public function getObjectId(): ?int
    {
        return $this->objectId;
    }

    /**
     * @return int|null
     */
    public function getObjectTypeId(): ?int
    {
        return $this->objectTypeId;
    }

    /**
     * @return string|null
     */
    public function getCategoryTitle(): ?string
    {
        return $this->categoryTitle;
    }

    /**
     * @return array|null
     */
    public function getChanges(): ?array
    {
        return $this->changes;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @return int|null
     */
    public function getReason(): ?int
    {
        return $this->reason;
    }

    /**
     * @return string|null
     */
    public function getObjectTitle(): ?string
    {
        return $this->objectTitle;
    }

    /**
     * @return string|null
     */
    public function getEntryIdentifier(): ?string
    {
        return $this->entryIdentifier;
    }

    /**
     * @return int
     */
    public function getCountChanges(): int
    {
        return $this->countChanges;
    }

    public function apply()
    {

    }

    /**
     * @param int    $objectId
     * @param int    $objectTypeId
     * @param string $objectTitle
     *
     * @return CmdbRecord
     */
    public static function objectCreated(int $objectId, int $objectTypeId, string $objectTitle): CmdbRecord
    {
        return new CmdbRecord(
            'C__LOGBOOK_EVENT__OBJECT_CREATED',
            'object created',
            $objectId,
            $objectTypeId,
            null,
            null,
            null,
            null,
            $objectTitle,
            null,
            0
        );
    }

    /**
     * @param int      $objectId
     * @param int      $objectTypeId
     * @param string   $categoryTitle
     * @param array    $changes
     * @param string   $commentary
     * @param int|null $reason
     *
     * @return CmdbRecord
     */
    public static function categoryEntryCreated(
        int $objectId,
        int $objectTypeId,
        string $categoryTitle,
        array $changes,
        string $commentary = '',
        ?int $reason = null
    ): CmdbRecord {
        return new CmdbRecord(
            'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
            'category entry created',
            $objectId,
            $objectTypeId,
            $categoryTitle,
            $changes,
            $commentary,
            $reason,
        );
    }

    /**
     * @param int    $objectId
     * @param int    $objectTypeId
     * @param string $categoryTitle
     * @param array  $changes
     * @param string $commentary
     * @param int|null $reason
     *
     * @return CmdbRecord
     */
    public static function categoryEntryUpdated(
        int $objectId,
        int $objectTypeId,
        string $categoryTitle,
        array $changes,
        string $commentary = '',
        ?int $reason = null
    ): CmdbRecord {
        return new CmdbRecord(
            'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
            'category entry updated',
            $objectId,
            $objectTypeId,
            $categoryTitle,
            $changes,
            $commentary,
            $reason,
        );
    }
}
