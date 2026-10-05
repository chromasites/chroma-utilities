# Chroma Utilities

Miscellaneous utility functions and standardizations for Chroma Sites managed WordPress websites.

Current version: **1.1**

## Features

- Disables WordPress core, plugin, and theme automatic update notification emails.
- Prefills the lost-password form from an `email` query parameter when the field is empty.

## Installation

Upload `chroma-utilities.zip` through **Plugins > Add New > Upload Plugin**, then activate Chroma Utilities. For an existing installation, replace the installed plugin with the updated ZIP.

## Lost-password links

Use the site's login URL with `action=lostpassword` and a URL-encoded email address:

```text
https://yoursite.com/wp-login.php?action=lostpassword&email=person%40example.com
```

Replace the domain and email address for the recipient. The plugin preserves an existing field value. The visitor still submits the form to request the password-reset email.

## Versioning

- Feature releases use versions such as `1.1` and `1.2`.
- Maintenance fixes use versions such as `1.1.1`.
- Breaking changes increment the major version, such as `2.0`.
- Update the PHP plugin header and changelog together, then tag the release as `v<VERSION>`.
- The initial plugin header used `1.0.0`; version `1.1` adds the email field feature.

## License

GPL-2.0-or-later.
