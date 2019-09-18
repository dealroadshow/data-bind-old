<?php

namespace Granule\DataBind\Serializer;

use Granule\DataBind\{Serializer, Type};
use Granule\Util\EnumWording;

class EnumWordingSerializer extends Serializer {
    public function matches(Type $type): bool {
        return $type->is(EnumWording::class);
    }

    /**
     * @param EnumWording $data
     * @return string
     */
    public function serialize($data) {
        return $data->getWording();
    }

    protected function unserializeItem($data, Type $type) {
        return call_user_func([$type->getName(), 'fromWording'], func_get_args());
    }
}