# Contributing

Thank you for helping! Bug reports, documentation fixes, new properties and new components are all
welcome. By participating you agree to follow the [code of conduct](CODE_OF_CONDUCT.md).

## Where to start

- **Found a bug?** [Open an issue](https://github.com/makinuk/icalendar/issues/new/choose) with the
  code you ran, the `.ics` output and the calendar client that misbehaved.
- **Want a feature?** Check the [roadmap](docs/rfc-compliance.md#roadmap). Items marked
  *good first issue* are small and self-contained. For larger items, open an issue first so we can
  agree on the API before you write code.
- **Security issue?** Do not open a public issue; see [SECURITY.md](SECURITY.md).

## Development setup

```bash
git clone https://github.com/makinuk/icalendar.git
cd icalendar
composer install
composer check
```

| Command | What it does |
|---|---|
| `composer test` | PHPUnit tests |
| `composer analyse` | PHPStan at level `max` |
| `composer cs` | coding style check (PER Coding Style 2.0) |
| `composer cs-fix` | fix the coding style |
| `composer check` | all of the above, as in CI |

## Guidelines

**Follow the RFC.** Link the section of [RFC 5545](https://www.rfc-editor.org/rfc/rfc5545) (or
RFC 5546 / 7986) you implement in the docblock, like the existing classes do.

**Keep the API consistent.**

- setters are fluent, accept `null` to remove a value and validate their input with
  `InvalidArgumentException`
- checks that need several properties go into `validate()` and throw `ValidationException`
- dates are accepted as `DateTimeInterface|string|int` through `Support\DateTimeNormalizer`
- use a backed enum for any property with a fixed list of values
- text values go through `Property::text()`; never concatenate unescaped user input into a content line

**Test the output.** Every new property needs a test asserting the exact rendered line. Tests of
full components should also call `assertRfc5545ContentLines()`. If you can, check your output with a
real client or with `vendor/bin/vobject validate` from [sabre/vobject](https://github.com/sabre-io/vobject).

**Document it.** Update the relevant page in `docs/`, the support matrix in
`docs/rfc-compliance.md` and the `Unreleased` section of `CHANGELOG.md`.

**Rector rules** for upgrades live in `rector/`, with fixtures in `tests/Rector/Fixture`: each
`.php.inc` file holds the code before and after `-----`.

**Mind backwards compatibility.** Public classes and methods follow semantic versioning. Classes in
`Support\` are internal. Breaking changes are only possible in a major version.

## Pull requests

1. Fork the repository and create a branch from `main`.
2. Make your change with tests and documentation.
3. Run `composer check`.
4. Open a pull request and fill in the template.

Keep pull requests focused: one feature or fix per pull request is easier to review and release.

## Releasing (maintainers)

1. Move the `Unreleased` entries in `CHANGELOG.md` to a new version section.
2. Tag the release: `git tag -a v3.1.0 -m "v3.1.0" && git push --tags`.
3. Packagist updates automatically; publish the release notes on GitHub.
