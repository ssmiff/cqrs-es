#!/usr/bin/env php
<?php

declare(strict_types=1);

use Ssmiff\CqrsEs\Aggregate\AggregateRootId;
use Ssmiff\CqrsEs\Aggregate\UuidAggregateRootId;
use Ssmiff\CqrsEs\Attributes\EventHandler;
use Ssmiff\CqrsEs\EventHandling\SimpleEventBus;
use Ssmiff\CqrsEs\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory;
use Ssmiff\CqrsEs\EventSourcing\EventSourcedAggregateRoot;
use Ssmiff\CqrsEs\EventSourcing\EventSourcingAggregateRootRepository;
use Ssmiff\CqrsEs\EventStore\SerializedMemoryEventStore;
use Ssmiff\CqrsEs\Serializer\Inflector\DotSeparatedSnakeCaseInflector;
use Ssmiff\CqrsEs\Serializer\Serializable;
use Ssmiff\CqrsEs\Serializer\SimpleInterfaceSerializer;

require dirname(__DIR__) . '/vendor/autoload.php';

// Custom Domain Objects
readonly class UserId extends UuidAggregateRootId {}

readonly class UserHasRegistered implements Serializable
{
    public function __construct(
        public UserId $userId,
        public string $name,
    ) {}

    public function serialize(): array
    {
        return [
            'userId' => (string)$this->userId,
            'name' => $this->name,
        ];
    }

    public static function deserialize(array $data): static
    {
        return new self(
            UserId::fromString($data['userId']),
            $data['name'],
        );
    }
}

readonly class UserChangedName implements Serializable
{
    public function __construct(public string $name) {}

    public function serialize(): array
    {
        return ['name' => $this->name];
    }

    public static function deserialize(array $data): static
    {
        return new self($data['name']);
    }
}

class UserAggregateRoot extends EventSourcedAggregateRoot
{
    private UserId $userId;

    private string $name;

    public function __construct() {
        $this->handleMethodInflector = new \Ssmiff\CqrsEs\HandleMethodInflector\InflectHandlerMethodsFromReflection();
    }

    public function getAggregateRootId(): AggregateRootId
    {
        return $this->userId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @throws Exception
     */
    public static function register(
        UserId $userId,
        string $name,
    ): self {
        $user = new static();
        $user->recordThat(new UserHasRegistered($userId, $name));

        return $user;
    }

    public function updateName(string $newName): void
    {
        $this->recordThat(new UserChangedName($newName));
    }

    #[EventHandler(UserHasRegistered::class)]
    public function userHasRegisteredEvent(UserHasRegistered $event): void
    {
        $this->userId = $event->userId;
        $this->name = $event->name;
    }

    #[EventHandler(UserChangedName::class)]
    public function userChangedNameEvent(UserChangedName $event): void
    {
        echo "userChangedNameEvent " . $event->name . "\n";

        $this->name = $event->name;
    }

    public function uchange1(UserChangedName $event): void
    {
        echo "uchange1 " . $event->name . "\n";
    }

    public function uchange2(int|UserChangedName $event, int $id = 1): void
    {
        if ($event instanceof UserChangedName) {
            echo "uchange2 " . $event->name . "\n";
        }
    }
}

$classNameInflector = new DotSeparatedSnakeCaseInflector();

$eventStore = new SerializedMemoryEventStore(
    new SimpleInterfaceSerializer($classNameInflector),
    new SimpleInterfaceSerializer($classNameInflector),
    $classNameInflector,
);

$eventBus = new SimpleEventBus();

$eventSourceRepository = new EventSourcingAggregateRootRepository(
    $eventStore,
    $eventBus,
    UserAggregateRoot::class,
    new PublicConstructorAggregateFactory(),
);

$user = UserAggregateRoot::register(
    UserId::new(),
    'scott',
);

$user->updateName('amelia');

$eventSourceRepository->persist($user);
