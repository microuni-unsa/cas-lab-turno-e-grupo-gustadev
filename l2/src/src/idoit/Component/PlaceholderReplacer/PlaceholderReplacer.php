<?php

namespace idoit\Component\PlaceholderReplacer;

use idoit\Component\PlaceholderReplacer\Placeholder\ApplyOnceInterface;
use idoit\Component\PlaceholderReplacer\Placeholder\Counter\AbstractCounter;
use idoit\Component\PlaceholderReplacer\Placeholder\Counter\CustomCounter;
use idoit\Component\PlaceholderReplacer\Placeholder\PlaceholderInterface;

class PlaceholderReplacer
{
    /**
     * @var iterable|PlaceholderInterface[] $placeholders
     */
    private iterable $placeholders = [];

    /**
     * CollectionCategoryConfigurationSource constructor.
     *
     * @param iterable|PlaceholderInterface[] $placeholders
     */
    public function __construct(iterable $placeholders = [])
    {
        $this->placeholders = $placeholders;
    }

    /**
     * @param PlaceholderInterface $placeholder
     *
     * @return PlaceholderReplacer
     */
    public function addPlaceholder(PlaceholderInterface $placeholder): PlaceholderReplacer
    {
        $this->placeholders[] = $placeholder;
        return $this;
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public function hasPlaceholders(string $value): bool
    {
        foreach ($this->placeholders as $placeholder) {
            if ($placeholder->isApplicable($value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $value
     * @param Config $config
     *
     * @return string
     */
    public function replacePlaceholder(string $value, Config $config): string
    {
        if (trim($value) === '') {
            return $value;
        }
        $alreadyRun = [];
        foreach ($this->placeholders as $placeholder) {
            if ($placeholder instanceof AbstractCounter) {
                $placeholder::setCounters($value, $config);
            }

            while ($placeholder->isApplicable($value) && !in_array(get_class($placeholder), $alreadyRun)) {
                $value = $placeholder->replacePlaceholder($value, $config);
                if ($placeholder instanceof ApplyOnceInterface) {
                    $alreadyRun[] = get_class($placeholder);
                }
            }
        }
        return $value;
    }

    /**
     * Decrease all affected custom counters with minus 1 if they are > 0
     *
     * @param string $customCounterPattern
     * @param string $table
     *
     * @return void
     */
    public function revertLastCustomCountersUpdate(string $customCounterPattern, string $table): void
    {
        $customCounterService = new CustomCounter();
        if ($customCounterService->isApplicable($customCounterPattern)) {
            $customCounterService::setCounters($customCounterPattern, new Config(null, null, null, null, $table));
            $customCounterService::decreaseCounter();
        }
    }
}
