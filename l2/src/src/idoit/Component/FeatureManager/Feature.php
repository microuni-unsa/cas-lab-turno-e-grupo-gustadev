<?php

namespace idoit\Component\FeatureManager;

class Feature
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $description;

    /**
     * @var bool
     */
    private bool $cloudAble;

    /**
     * @param string $name
     * @param string $description
     * @param bool   $cloudAble
     */
    public function __construct(string $name, string $description, bool $cloudAble)
    {
        $this->name = $name;
        $this->description = $description;
        $this->cloudAble = $cloudAble;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function isCloudAble(): bool
    {
        return $this->cloudAble;
    }
}
