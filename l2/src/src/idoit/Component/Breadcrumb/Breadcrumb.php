<?php

namespace idoit\Component\Breadcrumb;

/**
 * i-doit Breadcrumb.
 *
 * @see       ID-9725
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Breadcrumb
{
    private array $pages = [];

    public function setPages(array $pages): self
    {
        $this->pages = [];

        foreach ($pages as $page) {
            $this->addPage($page);
        }

        return $this;
    }

    public function addPage(Page $page): self
    {
        $this->pages[] = $page;

        return $this;
    }

    public function getPages(): array
    {
        return $this->pages;
    }
}
