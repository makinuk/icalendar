<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Rector\Set;

/**
 * Rector sets shipped with makinuk/icalendar.
 *
 *     return RectorConfig::configure()
 *         ->withPaths([__DIR__ . '/src'])
 *         ->withSets([ICalendarSetList::UPGRADE_30])
 *         ->withImportNames(removeUnusedImports: true);
 */
final class ICalendarSetList
{
    /** Migrates code written for makinuk/icalendar 2.x to the 3.0 API. */
    public const UPGRADE_30 = __DIR__ . '/../../config/upgrade-3.0.php';
}
