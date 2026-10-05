# Chroma Utilities

Miscellaneous utility functions and standardizations for Chroma Sites managed WordPress websites.

Current version: **2.0**

Requires WordPress 5.8 or newer and PHP 5.6.20 or newer.

## Features

- Disables WordPress core, plugin, and theme automatic update notification emails.
- Prefills the lost-password form from an `email` query parameter when the field is empty.
- Sets Public Post Preview's nonce lifetime to 28 days. Its nonce windows mean actual link validity is approximately 14–28 days.
- Uses Postmark's configured sender for Gravity Forms notifications, preserving the original sender if settings are missing or invalid.
- Enables Gravity Forms scrolling to validation errors and confirmations.
- Sets Hello Elementor's viewport to `width=device-width, initial-scale=1.0, maximum-scale=1.0`.
- Checks published GitHub releases for updates using bundled Plugin Update Checker 5.7.

## Installation

Upload `chroma-utilities.zip` through **Plugins > Add New > Upload Plugin**, then activate Chroma Utilities. For an existing installation, replace the installed plugin with the updated ZIP.

The plugin is intended for new websites. When migrating an existing website, remove the equivalent theme snippets before activating version 2.0. These utilities register directly without checking for theme copies.

The Public Post Preview, Gravity Forms, Postmark, and Hello Elementor integrations take effect when their corresponding plugins or theme use the filters. They are optional; this plugin does not require all of them to be installed.

## Updates

Updates come from [chromasites/chroma-utilities](https://github.com/chromasites/chroma-utilities). No GitHub token or separate updater plugin is required while the repository is public.

On each new website, enable **Plugins > Installed Plugins > Chroma Utilities > Enable auto-updates** if automatic installation is desired. Otherwise, updates can be installed using WordPress's usual **Update now** link. The updater follows the site's auto-update setting; it does not force auto-updates on.

The default scheduled check runs about every 12 hours, subject to WordPress cron. The Plugins screen also provides a **Check for updates** link. Automatic update notification emails remain disabled by this plugin.

Only published, non-prerelease GitHub releases with an asset named exactly `chroma-utilities.zip` are eligible. Drafts, prereleases, standalone tags, branch commits, and releases without that ZIP are excluded.

Version 1.1 has no updater; install 2.0 manually once on any existing 1.1 site that should receive future releases.

## Releasing an update

1. Update the version in `chroma-utilities.php`, `readme.txt`, this README, and `CHANGELOG.md`.
2. Run PHP syntax checks and `php tests/smoke.php`, then commit the release changes.
3. Create and push a matching tag, such as `v2.1`.
4. Build the installable ZIP from the tag, from the repository root:

   ```sh
   git archive --format=zip --prefix=chroma-utilities/ --output=../chroma-utilities.zip v2.1
   ```

5. Publish a GitHub release for that tag and attach `chroma-utilities.zip`. For example:

   ```sh
   gh release create v2.1 ../chroma-utilities.zip --repo chromasites/chroma-utilities --verify-tag --title "Chroma Utilities 2.1" --notes-file /path/to/release-notes.md
   ```

Keep the tag, PHP plugin version, readme stable tag, and ZIP contents consistent. Mark test releases as prereleases. The updater library and its runtime dependencies must remain in every ZIP.

## Lost-password links

Use the site's login URL with `action=lostpassword` and a URL-encoded email address:

```text
https://yoursite.com/wp-login.php?action=lostpassword&email=person%40example.com
```

Replace the domain and email address for the recipient. The plugin preserves an existing field value. The visitor still submits the form to request the password-reset email.

## Versioning

- Feature releases use versions such as `2.1` and `2.2`.
- Maintenance fixes use versions such as `2.0.1`.
- Major changes increment the major version, such as `3.0`.
- Update the PHP plugin header and changelog together, then tag the release as `v<VERSION>`.
- The initial plugin header used `1.0.0`; version `1.1` adds the email field feature.

## License

Chroma Utilities: GPL-2.0-or-later. Bundled Plugin Update Checker and its dependencies retain their upstream license notices in `lib/plugin-update-checker/`.

## Integration references

- [Plugin Update Checker GitHub integration](https://github.com/YahnisElsts/plugin-update-checker#github-integration), bundled from [v5.7](https://github.com/YahnisElsts/plugin-update-checker/releases/tag/v5.7).
- [Gravity Forms notification filter](https://docs.gravityforms.com/gform_notification/) and [confirmation anchor](https://docs.gravityforms.com/gform_confirmation_anchor/).
- [Official Postmark settings implementation](https://github.com/ActiveCampaign/postmark-wordpress/blob/master/postmark.php).
- [Hello Elementor viewport filter](https://github.com/elementor/hello-theme/blob/main/header.php).
- [Public Post Preview nonce implementation](https://github.com/ocean90/public-post-preview/blob/master/public-post-preview.php).
- [WordPress Update URI header](https://developer.wordpress.org/plugins/plugin-basics/header-requirements/).
