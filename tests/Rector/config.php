<?php

declare(strict_types=1);

use Makinuk\ICalendar\Rector\Set\ICalendarSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withSets([ICalendarSetList::UPGRADE_30])
    ->withImportNames(removeUnusedImports: true);
