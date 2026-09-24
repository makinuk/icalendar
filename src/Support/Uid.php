<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Support;

/**
 * Generates unique identifiers for the UID property (RFC 7986 §5.3 recommends UUIDs).
 *
 * @internal
 */
final class Uid
{
    /**
     * Returns a random RFC 4122 version 4 UUID.
     */
    public static function generate(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
