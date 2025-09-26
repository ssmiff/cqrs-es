<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Serializer;

use ReflectionClass;
use ReflectionException;

use Ssmiff\CqrsEs\Encryption\Encrypter;

readonly class EncryptedFieldSerializer implements Serializer
{
    public function __construct(
        private Serializer $serializer,
        private Encrypter $encrypter,
    ) {
    }

    public function serialize(object $object): array
    {
        $serialisedPayload = $this->serializer->serialize($object);

        $secureFields = $object instanceof HasSecureFields
            ? array_flip($object::getSecureFields()) :
            [];

        foreach ($serialisedPayload as $key => $value) {
            if (!isset($secureFields[$key])) {
                continue;
            }

            $serialisedPayload[$key] = is_string($value)
                ? $this->encrypter->encryptString($value)
                : $this->encrypter->encrypt($value);
        }

        return $serialisedPayload;
    }

    /**
     * @param array $payload
     * @param class-string $objectType
     *
     * @return object
     *
     * @throws ReflectionException
     */
    public function deserialize(array $payload, string $objectType): object
    {
        $serializerRefl = new ReflectionClass($objectType);
        $secureFields = $serializerRefl->implementsInterface(HasSecureFields::class)
            ? array_flip($objectType::getSecureFields())
            : [];

        foreach ($payload as $key => $value) {
            if (!isset($secureFields[$key])) {
                continue;
            }

            $payload[$key] = is_string($value)
                ? $this->encrypter->decryptString($value)
                : $this->encrypter->decrypt($value);
        }

        return $this->serializer->deserialize($payload, $objectType);
    }
}
