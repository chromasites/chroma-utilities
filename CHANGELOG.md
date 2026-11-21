# Changelog

## 2.2.1 — 2026-11-20

- Removed forced auto-updates and restored WordPress's normal toggle and defaults without changing saved preferences.
- Kept stable GitHub release detection and the required `chroma-utilities.zip` asset.
- Checked Plugin Update Checker 6.0; retained bundled 5.7 to preserve PHP 5.6.20 compatibility because 6.0 requires PHP 7.4. No updater library changes were needed.

## 2.2 — 2026-11-20

- Enabled WordPress automatic updates for Chroma Utilities on every domain while the plugin is active, without requiring a per-site opt-in.
- Preserved other plugins' preferences, WordPress's global updater restrictions, and per-update safety flags. Updates still require a published stable GitHub release with `chroma-utilities.zip`.
- Checked the latest stable Plugin Update Checker release (6.0). Retained bundled 5.7 to preserve PHP 5.6.20 compatibility; 6.0 requires PHP 7.4, and this change needs no new updater APIs.

## 2.1 — 2026-11-20

- Merged Simple Site Redirect into Utilities with Settings > Site Redirect on `.chroma.site` subdomains only.
- Restricted redirects to the configured Site Address's `.chroma.site` hostname, disabling them on live custom domains and custom-domain aliases.
- Preserved the existing destination option, temporary (302) behavior, and admin/login access; added request-time destination validation to prevent loops after domain changes.
- Documented deactivating the standalone redirect plugin when migrating.
- Reviewed Plugin Update Checker 6.0. Retained bundled 5.7 to preserve the plugin's PHP 5.6.20 minimum: 6.0 requires PHP 7.4. The release also updates Parsedown for developer-supplied release notes and makes escaping/metadata fixes; no updater API changes are needed for this feature.

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
