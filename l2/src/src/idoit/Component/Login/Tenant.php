<?php

namespace idoit\Component\Login;

class Tenant
{
    /**
     * @var int
     */
    protected int $id;

    /**
     * @var string
     */
    protected string $title = '';

    /**
     * @var bool
     */
    protected bool $isLdapLogin = false;

    /**
     * @var int
     */
    protected int $userId;

    /**
     * @param int    $id
     * @param string $title
     */
    public function __construct(int $id, string $title)
    {
        $this->id = $id;
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return bool
     */
    public function isLdapLogin(): bool
    {
        return $this->isLdapLogin;
    }

    /**
     * @param bool $isLdapLogin
     *
     * @return $this
     */
    public function setIsLdapLogin(bool $isLdapLogin): static
    {
        $this->isLdapLogin = $isLdapLogin;
        return $this;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }
}
