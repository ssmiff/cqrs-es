<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventSourcing\MetadataEnrichment;

use Ssmiff\CqrsEs\Metadata;

interface MetadataEnricher
{
    public function enrich(Metadata $metadata): Metadata;
}
