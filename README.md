# Chroma Utilities

Miscellaneous utility functions and standardizations for Chroma Sites managed WordPress websites.

Current version: **2.2**

Requires WordPress 5.8 or newer and PHP 5.6.20 or newer.

## Features

- Disables WordPress core, plugin, and theme automatic update notification emails.
- Prefills the lost-password form from an `email` query parameter when the field is empty.
- Sets Public Post Preview's nonce lifetime to 28 days. Its nonce windows mean actual link validity is approximately 14–28 days.
- Uses Postmark's configured sender for Gravity Forms notifications, preserving the original sender if settings are missing or invalid.
- Enables Gravity Forms scrolling to validation errors and confirmations.
- Sets Hello Elementor's viewport to `width=device-width, initial-scale=1.0, maximum-scale=1.0`.
- Adds optional site redirects exclusively on `.chroma.site` subdomains, with settings under **Settings > Site Redirect**.
- Automatically installs published stable GitHub releases using WordPress and bundled Plugin Update Checker 5.7.

## Installation

Upload the current `chroma-utilities-VERSION.zip` through **Plugins > Add New > Upload Plugin**, then activate Chroma Utilities. The `chroma-utilities.zip` asset from GitHub works too. For an existing installation, replace the installed plugin with the updated ZIP.

The plugin is intended for new websites. When migrating an existing website, remove the equivalent theme snippets before activating version 2.0. These utilities register directly without checking for theme copies.

The Public Post Preview, Gravity Forms, Postmark, and Hello Elementor integrations take effect when their corresponding plugins or theme use the filters. They are optional; this plugin does not require all of them to be installed.

## Updates

Updates come from [chromasites/chroma-utilities](https://github.com/chromasites/chroma-utilities). No GitHub token or separate updater plugin is required while the repository is public.

While Chroma Utilities is active, auto-updates are enabled automatically for this plugin on every domain. No per-site toggle is needed. The Plugins screen shows that auto-updates are enabled, and WordPress installs eligible releases through its normal background updater. Other plugins' auto-update preferences are preserved. WordPress's global automatic-update restrictions, filesystem permissions, compatibility checks, and per-update safety flags still apply.

The default scheduled check runs about every 12 hours, subject to WordPress cron. The Plugins screen also provides a **Check for updates** link. Automatic update notification emails remain disabled by this plugin.

Only published, non-prerelease GitHub releases with an asset named exactly `chroma-utilities.zip` are eligible. Drafts, prereleases, standalone tags, branch commits, and releases without that ZIP are excluded.

Version 1.1 has no updater; install 2.0 manually once on any existing 1.1 site that should receive future releases.

Install version 2.2 or newer once to enable this automatic-installation policy. Earlier versions still require their existing WordPress auto-update opt-in to receive it automatically. New code must be published as a GitHub release with the required ZIP before installed sites can receive it; local edits alone do not create an update.

## Releasing an update

1. Check the [latest stable Plugin Update Checker release](https://github.com/YahnisElsts/plugin-update-checker/releases/latest) and update the bundled library if appropriate. Review release notes and compatibility, update version-specific references and documentation, and record any upgrade in the changelog. If retaining the existing version, explain why in the change summary.
2. Update the version in `chroma-utilities.php`, `readme.txt`, this README, and `CHANGELOG.md`.
3. Run PHP syntax checks, `php tests/smoke.php`, and `php tests/redirect.php` (also with `rest`, `cli`, and `success` arguments), then commit the release changes.
4. Create and push a matching tag, such as `v2.2`.
5. Build the installable ZIP from the tag, from the repository root:

   ```sh
   git archive --format=zip --prefix=chroma-utilities/ --output=../chroma-utilities-2.2.zip v2.2
   mkdir -p ../archive
   if [ -e ../archive/chroma-utilities-2.2.zip ]; then
     cmp ../chroma-utilities-2.2.zip ../archive/chroma-utilities-2.2.zip
   else
     cp ../chroma-utilities-2.2.zip ../archive/chroma-utilities-2.2.zip
   fi
   ```

6. Publish a GitHub release for that tag and attach both ZIPs. The versioned ZIP is for manual downloads; `chroma-utilities.zip` is required by the updater. Create the unversioned asset in a temporary folder for publishing:

   ```sh
   release_assets_dir="$(mktemp -d)"
   cp ../chroma-utilities-2.2.zip "$release_assets_dir/chroma-utilities.zip"
   gh release create v2.2 "$release_assets_dir/chroma-utilities.zip" ../chroma-utilities-2.2.zip --repo chromasites/chroma-utilities --verify-tag --title "Chroma Utilities 2.2" --notes-file /path/to/release-notes.md
   ```

Keep the tag, PHP plugin version, readme stable tag, and ZIP contents consistent. Mark test releases as prereleases. The updater library and its runtime dependencies must remain in every ZIP.

Keep only the current `../chroma-utilities-VERSION.zip` installer beside the source folder. The local `../archive/` folder stores all versioned ZIPs, including a copy of the current version. Preserve older versions and never replace an archived published version with different contents. If the comparison above fails, stop and resolve the version mismatch before publishing. Remove older installers from `../` only after verifying identical archived copies, and remove the temporary publishing folder after a successful upload. Archive files are outside the repository and are not included in plugin packages.

## Lost-password links

Use the site's login URL with `action=lostpassword` and a URL-encoded email address:

```text
https://yoursite.com/wp-login.php?action=lostpassword&email=person%40example.com
```

Replace the domain and email address for the recipient. The plugin preserves an existing field value. The visitor still submits the form to request the password-reset email.

## Site redirects

On sites whose WordPress **Site Address (URL)** uses a `.chroma.site` subdomain, open **Settings > Site Redirect** and enter a destination URL on another domain. Leave it blank to turn redirects off. All front-end paths go to that exact URL using a temporary (302) redirect; paths and query strings are not forwarded. Admin, login, AJAX, REST, cron, and WP-CLI remain accessible.

Redirects and their settings are disabled on other domains, including the bare `chroma.site` domain. Changing the Site Address to a live custom domain automatically disables the feature even when a destination is saved. Requests must also use the configured `.chroma.site` hostname; custom-domain aliases cannot trigger a redirect. The other Utilities features continue to work on all domains.

When replacing **Simple Site Redirect**, deactivate that standalone plugin. Utilities reuses its saved `chroma_ssr_url` destination automatically, so no re-entry is needed. The standalone plugin has no domain restriction and must be deactivated for the new restriction to apply. No activation-time redirect to the settings screen is added to Utilities.

If full-page caching serves pages before WordPress runs, clear the site/CDN cache when changing the destination or launching on the live domain.

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
