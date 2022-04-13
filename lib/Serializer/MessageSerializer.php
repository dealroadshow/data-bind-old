<?php

declare(strict_types=1);

namespace Granule\DataBind\Serializer;

use Google\Protobuf\Internal\Message;
use Granule\DataBind\Serializer;
use Granule\DataBind\Type;

class MessageSerializer extends Serializer
{
    public function matches(Type $type): bool
    {
        return $type->is(Message::class);
    }

    /**
     * @param Message $data
     *
     * @return string
     */
    public function serialize(mixed $data)
    {
        return json_decode($data->serializeToJsonString(), false, 512, JSON_THROW_ON_ERROR);
    }

    public function unserializeItem($data, Type $type)
    {
        /** @var Message $message */
        $class = $type->getName();
        $message = new $class();

        $message->mergeFromJsonString(json_encode($data, JSON_UNESCAPED_UNICODE));

        return $message;
    }
}
