<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventSourcing\MetadataEnrichment;

use Ssmiff\CqrsEs\EventSourcing\EventStreamDecorator;
use Ssmiff\CqrsEs\DomainEventStream;
use Webmozart\Assert\Assert;

final class MetadataEnrichingEventStreamDecorator implements EventStreamDecorator
{
    /**
     * @param MetadataEnricher[] $metadataEnrichers
     */
    public function __construct(private array $metadataEnrichers = [])
    {
        Assert::allImplementsInterface($metadataEnrichers, MetadataEnricher::class);
    }

    public function registerEnricher(MetadataEnricher $enricher): void
    {
        $this->metadataEnrichers[] = $enricher;
    }

    public function decorateForWrite(
        string $aggregateType,
        string $aggregateIdentifier,
        DomainEventStream $eventStream,
    ): DomainEventStream {
        if (empty($this->metadataEnrichers)) {
            return $eventStream;
        }

        $messages = [];

        foreach ($eventStream as $message) {
            $metadata = $message->getMetadata();

            foreach ($this->metadataEnrichers as $metadataEnricher) {
                $metadata = $metadataEnricher->enrich($metadata);
            }

            $messages[] = $message->andMetadata($metadata);
        }

        return new DomainEventStream($messages);
    }
}
