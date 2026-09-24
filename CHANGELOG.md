# Changelog

All notable changes to this project are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project adheres
to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [3.0.0] - 2026-09-24

A rewrite with RFC 5545 compliant output and a typed API. See [UPGRADE-3.0.md](UPGRADE-3.0.md).

### Added

- `VTODO` support (`Component\Todo`)
- Recurring events: `RRULE` builder (`Value\RecurrenceRule`) and `EXDATE`
- All-day and multi-day events
- `DISPLAY`, `AUDIO` and `EMAIL` alarms with relative, end-relative and absolute triggers and repeats
- Attendee role, participation status and RSVP; `Method` enum for invitations, updates and cancellations
- Event and to-do properties: `DURATION`, `CREATED`, `LAST-MODIFIED`, `GEO`, `URL`, `CATEGORIES`,
  `CLASS`, `PRIORITY`, `SEQUENCE`, `STATUS`, `TRANSP`, `COLOR`, `ATTACH`
- Calendar properties: `NAME`, `DESCRIPTION`, `REFRESH-INTERVAL` and their `X-WR-*` equivalents
- `addProperty()` for custom and X- properties on every component
- Automatic UUID `UID` and `DTSTAMP`
- Dates accepted as `DateTimeInterface`, date strings or Unix timestamps
- Validation on render with `ValidationException`; `ICalendarException` marker interface
- `Calendar::getContentType()`; `Calendar::send()` with attachment or inline disposition
- Documentation in `docs/`, runnable examples, contribution guidelines, CI

### Changed

- Requires PHP 8.2
- Namespace `makinuk\ICalendar` renamed to `Makinuk\ICalendar`; classes renamed and moved
- Output uses CRLF line endings, TEXT escaping and 75-octet line folding
- `DTSTAMP` is the creation time instead of the start date
- `PRODID` identifies this library instead of Microsoft Outlook
- `METHOD` is only written when set

### Removed

- Hard-coded `CLASS`, `PRIORITY`, `SEQUENCE`, `TRANSP` and `LANGUAGE=tr` values
- `ICalendar::convertUTC()` and public properties

### Fixed

- Commas, semicolons, backslashes and line breaks in text produced invalid files
- Long lines were not folded
- Empty `ORGANIZER` and `LOCATION` lines produced invalid files
- Time zone conversion depended on the server's default time zone

## [2.1.0] - 2022-08-02

- Requires PHP 7.4

## [1.0.1] - 2015-03-09

## [1.0] - 2015-03-09

- First release

[Unreleased]: https://github.com/makinuk/icalendar/compare/v3.0.0...HEAD
[3.0.0]: https://github.com/makinuk/icalendar/compare/v2.1.0...v3.0.0
[2.1.0]: https://github.com/makinuk/icalendar/compare/v1.0.1...v2.1.0
[1.0.1]: https://github.com/makinuk/icalendar/compare/v1.0...v1.0.1
[1.0]: https://github.com/makinuk/icalendar/releases/tag/v1.0
