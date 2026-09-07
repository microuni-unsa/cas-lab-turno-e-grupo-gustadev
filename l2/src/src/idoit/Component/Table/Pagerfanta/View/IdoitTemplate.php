<?php

namespace idoit\Component\Table\Pagerfanta\View;

use Pagerfanta\View\Template\DefaultTemplate;

/**
 * Pagerfanta Template
 *
 * @package     idoit\Component
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class IdoitTemplate extends DefaultTemplate
{
    /**
     * @return string[]
     */
    protected function getDefaultOptions(): array
    {
        return array_merge(
            parent::getDefaultOptions(),
            [
                'css_active_class' => 'pressed',
                'css_container_class' => 'pagination',
                'css_disabled_class' => 'disabled',
                'css_dots_class' => 'dots',
                'css_item_class' => '',
                'css_prev_class' => '',
                'css_next_class' => '',
            ]
        );
    }
}
