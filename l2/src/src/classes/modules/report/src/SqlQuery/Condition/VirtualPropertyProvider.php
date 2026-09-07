<?php
namespace idoit\Module\Report\SqlQuery\Condition;

/**
 * Class VirtualPropertyProvider
 */
class VirtualPropertyProvider extends AbstractProvider implements ConditionProviderInterface
{
    public static function factory()
    {
        return (new self());
    }
}
