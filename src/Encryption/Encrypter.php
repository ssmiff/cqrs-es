<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Encryption;

interface Encrypter
{
    public function encrypt(mixed $value): string;

    public function encryptString(string $value): string;

    public function decrypt(string $value): mixed;

    public function decryptString(string $encryptedValue): string;
}
