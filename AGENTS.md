# Chroma Utilities maintenance

Whenever updating this plugin, check the most recent stable release of [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker/releases/latest) and update the bundled library if appropriate.

- Compare the latest stable release with the version bundled in `lib/plugin-update-checker/`.
- Review upstream release notes, PHP and WordPress compatibility, and API changes before deciding whether to upgrade.
- When upgrading, preserve upstream licenses and include all required runtime dependencies. Update version-specific class references, documentation, and compatibility requirements as needed.
- Run PHP syntax checks and `php tests/smoke.php`. Verify stable-release selection, required ZIP assets, and WordPress update data after any updater change.
- Record an updater upgrade in `CHANGELOG.md`. If retaining the bundled version, explain the compatibility or other concrete reason in the change summary.
- Provide a versioned download named `chroma-utilities-VERSION.zip` for each release, alongside the identical `chroma-utilities.zip` asset required by the updater. Keep the internal plugin folder named `chroma-utilities`.
- Save a copy of every versioned release ZIP in the local `../archive/` folder. Preserve older archives when replacing the current downloads; never overwrite an archived published version with different contents. The archive stays outside the Git repository and plugin package.
