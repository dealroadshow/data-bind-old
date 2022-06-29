<?php

declare(strict_types=1);

namespace Granule\DataBind\Serializer;

use Exception;
use Google\Protobuf\Internal\Message;
use Granule\DataBind\Serializer;
use Granule\DataBind\Type;
use JsonException;

class MessageSerializer extends Serializer
{
    public function matches(Type $type): bool
    {
        return $type->is(Message::class);
    }

    /**
     * @throws JsonException
     */
    public function serialize($data): ?object
    {
        /** @var Message $datax */
        return json_decode($data->serializeToJsonString(), false, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws Exception
     */
    public function unserializeItem($data, Type $type)
    {
        /** @var Message $message */
        $class = $type->getName();
        $message = new $class();

        $message->mergeFromJsonString(json_encode($data, JSON_UNESCAPED_UNICODE));

        return $message;
    }
}
