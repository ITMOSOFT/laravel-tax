# Changelog

## [1.0.1] - 2026-11-25
- Reject unsaved tax classes during resolution and skip inactive or missing tax classes.
- Prevent rules from resolving rates belonging to a different tax class.
- Normalize country matching and trim location values.
- Reject non-finite tax rates and invalid rounding scales.
- Add resolver regression coverage for specificity, priority, defaults, and class isolation.

## [1.0.0] - 2026-11-04
- Initial release: tax classes, rates, location rules, inclusive/exclusive and compound calculations.
