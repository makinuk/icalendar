# Security Policy

## Supported versions

| Version | Supported |
|---------|-----------|
| 3.x     | ✅        |
| < 3.0   | ❌        |

## Reporting a vulnerability

Please **do not** report security issues in public GitHub issues.

Report them privately through
[GitHub security advisories](https://github.com/makinuk/icalendar/security/advisories/new) or by
e-mail to **makinuk@gmail.com**. Include the affected version, a description of the issue and, if
possible, a minimal reproduction.

You will receive an answer within a week. Once a fix is available, a new version is released and an
advisory is published, crediting you unless you prefer otherwise.

## Scope

Examples of issues we consider security relevant:

- user input that can inject extra properties or components into the generated file
  (content line or header injection)
- output that makes a calendar client execute or fetch something the developer did not intend
