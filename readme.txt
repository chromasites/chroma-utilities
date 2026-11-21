=== Chroma Utilities ===
Contributors: nkruska
Requires at least: 5.8
Requires PHP: 5.6.20
Stable tag: 2.2.1
License: GPL-2.0-or-later

Utility functions and standardizations for Chroma Sites managed WordPress websites.

== Description ==

Chroma Utilities suppresses automatic update notification emails, prefills lost-password email fields, and provides optional Public Post Preview, Gravity Forms, Postmark, and Hello Elementor integrations.

Optional site redirects are available under Settings > Site Redirect only on .chroma.site subdomains. Redirects turn off automatically on custom domains. Deactivate the standalone Simple Site Redirect plugin when upgrading; its saved destination is reused.

Bundled Plugin Update Checker retrieves published stable releases from https://github.com/chromasites/chroma-utilities. WordPress's normal auto-update toggle, default, and saved preference control automatic installation. Auto-updates are not forced or automatically enabled.

== Installation ==

1. Upload chroma-utilities.zip through Plugins > Add New > Upload Plugin.
2. Remove equivalent theme snippets if migrating an existing site.
3. Activate Chroma Utilities.
4. Use the normal Enable auto-updates or Disable auto-updates toggle in WordPress as desired.

== Changelog ==

= 2.2.1 =
* Restored WordPress's normal auto-update toggle, default, and saved preference; removed forced auto-updates.

= 2.2 =
* Enabled automatic installation of stable GitHub releases for Chroma Utilities without a per-site opt-in.
* Preserved other plugins' auto-update preferences and WordPress safety checks.

= 2.1 =
* Merged Simple Site Redirect with automatic domain restrictions to .chroma.site subdomains.
* Reused existing redirect destinations and preserved admin/login access.

= 2.0.1 =
* Comment-only wording change to test update delivery from version 2.0.

= 2.0 =
* Added GitHub release updates with bundled Plugin Update Checker 5.7.
* Added a 28-day Public Post Preview nonce lifetime.
* Added Gravity Forms notification senders from Postmark settings.
* Enabled Gravity Forms scrolling to validation errors and confirmations.
* Added the Hello Elementor viewport filter.

= 1.1 =
* Added lost-password email field prefilling from the URL's email query parameter.

= 1.0.0 =
* Initial plugin with automatic update notification email suppression.
