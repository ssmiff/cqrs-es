<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\Serializer;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\Test;
use Ssmiff\CqrsEs\Encryption\Encrypter;
use Ssmiff\CqrsEs\Serializer\EncryptedFieldSerializer;
use Ssmiff\CqrsEs\Serializer\HasSecureFields;
use Ssmiff\CqrsEs\Serializer\Serializer;
use Ssmiff\CqrsEs\Tests\Stubs\DecryptDto;

#[CoversClass(EncryptedFieldSerializer::class)]
final class EncryptedFieldSerializerTest extends MockeryTestCase
{
    #[Test]
    public function serialize_encrypts_secure_fields_and_delegates(): void
    {
        $inner = Mockery::mock(Serializer::class);
        $encrypter = Mockery::mock(Encrypter::class);

        $dto = new class implements HasSecureFields {
            public string $public = 'hello';
            public string $secret = 's3cr3t';

            public static function getSecureFields(): array
            {
                return ['secret'];
            }
        };

        $inner
            ->shouldReceive('serialize')
            ->once()
            ->with($dto)
            ->andReturn(['public' => 'hello', 'secret' => 's3cr3t']);

        $encrypter
            ->shouldReceive('encryptString')
            ->once()
            ->with('s3cr3t')
            ->andReturn('ENCRYPTED');

        $sut = new EncryptedFieldSerializer($inner, $encrypter);

        $out = $sut->serialize($dto);

        $this->assertSame(['public' => 'hello', 'secret' => 'ENCRYPTED'], $out);
    }

    #[Test]
    public function deserialize_decrypts_secure_fields_and_delegates(): void
    {
        $inner = Mockery::mock(Serializer::class);
        $encrypter = Mockery::mock(Encrypter::class);

        $type = DecryptDto::class;

        $payload = ['public' => 'hello', 'secret' => 'ENCRYPTED'];

        $encrypter
            ->shouldReceive('decryptString')
            ->once()
            ->with('ENCRYPTED')
            ->andReturn('s3cr3t');

        $expected = new $type('hello', 's3cr3t');

        $inner
            ->shouldReceive('deserialize')
            ->once()
            ->with(['public' => 'hello', 'secret' => 's3cr3t'], $type)
            ->andReturn($expected);

        $sut = new EncryptedFieldSerializer($inner, $encrypter);

        $obj = $sut->deserialize($payload, $type);

        $this->assertEquals($expected, $obj);
    }

    #[Test]
    public function when_no_secure_fields_nothing_is_encrypted_or_decrypted(): void
    {
        $inner = Mockery::mock(Serializer::class);
        $encrypter = Mockery::mock(Encrypter::class);

        $dto = new class {
            public string $public = 'hello';
        };
        $type = $dto::class;

        $inner->shouldReceive('serialize')->once()->with($dto)->andReturn(['public' => 'hello']);
        $inner->shouldReceive('deserialize')->once()->with(['public' => 'hello'], $type)->andReturn($dto);

        // encrypter must not be called
        $encrypter->shouldNotReceive('encryptString');
        $encrypter->shouldNotReceive('encrypt');
        $encrypter->shouldNotReceive('decryptString');
        $encrypter->shouldNotReceive('decrypt');

        $sut = new EncryptedFieldSerializer($inner, $encrypter);

        $out = $sut->serialize($dto);
        $this->assertSame(['public' => 'hello'], $out);

        $obj = $sut->deserialize(['public' => 'hello'], $type);
        $this->assertSame($dto, $obj);
    }
}
