<?php

namespace idoit\Module\Cmdb\Event\Category;

use Symfony\Component\EventDispatcher\GenericEvent;

class AbstractCategoryEvent extends GenericEvent
{
    /**
     * @var int
     */
    private int $id;

    /**
     * @var array
     */
    private array $data;

    /**
     * @param int $id
     * @param array $data
     */
    public function __construct(int $id, array $data)
    {
        $this->id = $id;
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
