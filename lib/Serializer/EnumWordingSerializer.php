<?php

namespace Granule\DataBind\Serializer;

use Granule\DataBind\Serializer;
use Granule\DataBind\Type;
use Granule\Util\EnumWording;

class EnumWordingSerializer extends Serializer
{
    public function matches(Type $type): bool
    {
        return $type->is(EnumWording::class);
    }

    public function serialize(mixed $data): string
    {
        return $data->getWording();
    }

    protected function unserializeItem($data, Type $type)
    {
        return call_user_func([$type->getName(), 'fromWording'], $data);
    }
}
