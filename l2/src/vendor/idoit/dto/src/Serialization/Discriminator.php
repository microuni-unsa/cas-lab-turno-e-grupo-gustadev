<?php declare(strict_types = 1);

namespace Idoit\Dto\Serialization;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::IS_REPEATABLE)]
class Discriminator
{
    public function __construct(private array $classmap, private string $typeKey = 'type')
    {
    }

    public function getClassmap(): array
    {
        return $this->classmap;
    }

    public function getType(object $object): ?string
    {
        foreach ($this->classmap as $type => $class) {
            if (is_a($object, $class, true)) {
                return $type;
            }
        }
        return null;
    }

    public function getClass(string $type): ?string
    {
        return $this->classmap[$type] ?? null;
    }

    public function getTypeKey(): string
    {
        return $this->typeKey;
    }
}
