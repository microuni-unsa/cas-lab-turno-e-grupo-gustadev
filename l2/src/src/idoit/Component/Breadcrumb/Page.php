<?php

namespace idoit\Component\Breadcrumb;

use isys_application;

/**
 * i-doit Breadcrumb 'Page'.
 *
 * @see       ID-9725
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Page
{
    public function __construct(private string $name, private string $url)
    {
    }

    public function getName(): string
    {
        $name = html_entity_decode(stripslashes($this->name), ENT_COMPAT, BASE_ENCODING);

        return isys_application::instance()->container->get('language')->get($name);
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
