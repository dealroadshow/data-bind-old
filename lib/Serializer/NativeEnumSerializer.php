<?php

declare(strict_types=1);

namespace Granule\DataBind\Serializer;

use Granule\DataBind\InvalidDataException;
use Granule\DataBind\Serializer;
use Granule\DataBind\Type;
use ReflectionEnum;
use ReflectionException;
use UnitEnum;

class NativeEnumSerializer extends Serializer
{
    public function matches(Type $type): bool
    {
        return $type->is(UnitEnum::class);
    }

    /** @param UnitEnum $data */
    public function serialize($data): array
    {
        return ['name' => $data->name];
    }

    /**
     * @throws ReflectionException
     */
    protected function unserializeItem($data, Type $type)
    {
        $class = new ReflectionEnum($type->getName());

        if ($class->hasCase($data['name'])) {
            return constant(
                sprintf(
                    '%s::%s',
                    $type->getName(),
                    $data['name']
                )
            );
        } else {
            throw InvalidDataException::fromTypeAndData($type, $data);
        }
    }
}
