# Changelog

## 2.0.1 — 2026-10-05

- Made a comment-only wording change to test delivery of updates from version 2.0.
- Checked Plugin Update Checker; bundled version 5.7 is still the latest stable release, so no library upgrade was needed.

## 2.0 — 2026-10-05

- Added bundled Plugin Update Checker 5.7, connected to `chromasites/chroma-utilities`.
- Limited updates to published stable GitHub releases containing `chroma-utilities.zip`.
- Added the Update URI header to identify the plugin's external update source.
- Added a four-week Public Post Preview nonce lifetime.
- Set Gravity Forms notification senders from valid Postmark settings, supporting JSON and legacy array settings.
- Enabled Gravity Forms scrolling to validation errors and confirmations.
- Added the requested Hello Elementor viewport content.
- Registered integrations directly without theme-duplicate checks; equivalent theme snippets should be removed during migration.
- Documented installation, automatic updates, and release packaging.

## 1.1 — 2026-10-05

- Added lost-password email field prefilling from the URL's `email` query parameter.
- Preserved existing values in the lost-password form.
- Documented the URL format in the code and README.

## 1.0.0

- Initial utilities plugin.
- Disabled automatic update notification emails for WordPress core, plugins, and themes.
