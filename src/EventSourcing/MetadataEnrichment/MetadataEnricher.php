<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventSourcing\MetadataEnrichment;

use Ssmiff\CqrsEs\Header;

interface MetadataEnricher
{
    public function enrich(Header $metadata): Header;
}
