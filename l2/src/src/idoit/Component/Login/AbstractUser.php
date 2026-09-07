<?php

namespace idoit\Component\Login;

abstract class AbstractUser
{
    /**
     * @var string
     */
    protected string $username = '';

    /**
     * @var Tenant[]
     */
    protected array $availableTenants = [];

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     *
     * @return $this
     */
    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return Tenant[]
     */
    public function getAvailableTenants(): array
    {
        return $this->availableTenants;
    }

    /**
     * @param array $availableTenants
     *
     * @return $this
     */
    public function setAvailableTenants(array $availableTenants): static
    {
        $this->availableTenants = $availableTenants;
        return $this;
    }

    /**
     * @param Tenant $tenant
     *
     * @return $this
     */
    public function addToAvailableTenants(Tenant $tenant): static
    {
        $this->availableTenants[$tenant->getId()] = $tenant;
        return $this;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function removeFromAvailableTenants(int $id): static
    {
        unset($this->availableTenants[$id]);
        return $this;
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function hasTenant(int $id): bool
    {
        return isset($this->availableTenants[$id]);
    }
}
