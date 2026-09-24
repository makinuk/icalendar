# RFC 5545 compliance

This library **generates** iCalendar data. It does not parse `.ics` files; for that, use
[sabre/vobject](https://github.com/sabre-io/vobject).

## Format (RFC 5545 §3.1–3.3)

| Rule | Status |
|---|---|
| CRLF line endings | ✅ |
| Line folding at 75 octets, UTF-8 safe | ✅ |
| TEXT escaping of `\` `;` `,` and line breaks | ✅ |
| Parameter quoting and RFC 6868 encoding (`^^`, `^n`, `^'`) | ✅ |
| UTF-8 validation, control character removal | ✅ |
| DATE-TIME in UTC, DATE for all-day values | ✅ |
| Local DATE-TIME with `TZID` | ❌ roadmap |

## Components

| Component | Status |
|---|---|
| `VCALENDAR` | ✅ `PRODID`, `VERSION`, `CALSCALE`, `METHOD`, plus `NAME`, `DESCRIPTION`, `REFRESH-INTERVAL` (RFC 7986) and their `X-WR-*` / `X-PUBLISHED-TTL` equivalents |
| `VEVENT` | ✅ |
| `VTODO` | ✅ |
| `VALARM` | ✅ `DISPLAY`, `AUDIO`, `EMAIL`; relative, end-relative and absolute triggers; repeat |
| `VJOURNAL` | ❌ roadmap (possible today via [extending](extending.md#adding-a-component)) |
| `VFREEBUSY` | ❌ roadmap |
| `VTIMEZONE` | ❌ roadmap |

## VEVENT / VTODO properties

| Property | Status | Property | Status |
|---|---|---|---|
| `UID` | ✅ auto-generated | `ORGANIZER` | ✅ |
| `DTSTAMP` | ✅ auto-generated | `ATTENDEE` (CN, ROLE, PARTSTAT, RSVP) | ✅ |
| `DTSTART`, `DTEND`, `DUE`, `DURATION` | ✅ | `CATEGORIES` | ✅ |
| `CREATED`, `LAST-MODIFIED`, `COMPLETED` | ✅ | `CLASS` | ✅ |
| `SUMMARY`, `DESCRIPTION`, `LOCATION` | ✅ | `PRIORITY`, `SEQUENCE` | ✅ |
| `GEO`, `URL` | ✅ | `STATUS`, `TRANSP`, `PERCENT-COMPLETE` | ✅ |
| `RRULE` (all parts) | ✅ | `ATTACH` (URI) | ✅ |
| `EXDATE` | ✅ | `COLOR` (RFC 7986) | ✅ |
| `RDATE` | ❌ via `addProperty()` | `RECURRENCE-ID` | ❌ via `addProperty()` |
| `COMMENT`, `CONTACT`, `RESOURCES` | ❌ via `addProperty()` | `RELATED-TO` | ❌ via `addProperty()` |
| `ATTENDEE` (CUTYPE, DELEGATED-*, SENT-BY) | ❌ roadmap | `ATTACH` (inline binary) | ❌ roadmap |
| `CONFERENCE`, `IMAGE` (RFC 7986) | ❌ via `addProperty()` | `REQUEST-STATUS` | ❌ via `addProperty()` |

## Validation on render

- a calendar contains at least one component
- an event has a start; its end is not before its start; its duration is not negative
- a to-do with a duration has a start; its due date is not before its start
- components with a recurrence rule or exception dates have a start
- alarms have the properties their action requires

## Roadmap

Contributions are welcome. Each item is a self-contained piece of work; open an issue to discuss the
design before starting on a large one.

| Item | Size | Notes |
|---|---|---|
| `VTIMEZONE` generation and `TZID` date-times | L | build the component from `DateTimeZone::getTransitions()` |
| `RDATE` and `RECURRENCE-ID` setters | S | overriding single occurrences of a series |
| `COMMENT`, `CONTACT`, `RESOURCES`, `RELATED-TO` setters | S | good first issue |
| `CONFERENCE` and `IMAGE` setters (RFC 7986) | S | good first issue |
| Attendee `CUTYPE`, `DELEGATED-TO/FROM`, `SENT-BY`, `MEMBER` | S | good first issue |
| `VJOURNAL` component | M | |
| `VFREEBUSY` component | M | |
| Inline binary `ATTACH` (base64) | S | |
| Parser / reader | XL | discuss first; sabre/vobject already covers it |
