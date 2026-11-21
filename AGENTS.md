# Chroma Utilities maintenance

Whenever updating this plugin, check the most recent stable release of [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker/releases/latest) and update the bundled library if appropriate.

- Compare the latest stable release with the version bundled in `lib/plugin-update-checker/`.
- Review upstream release notes, PHP and WordPress compatibility, and API changes before deciding whether to upgrade.
- When upgrading, preserve upstream licenses and include all required runtime dependencies. Update version-specific class references, documentation, and compatibility requirements as needed.
- Run PHP syntax checks and `php tests/smoke.php`. Verify stable-release selection, required ZIP assets, and WordPress update data after any updater change.
- Use WordPress's normal auto-update toggle and defaults. Preserve saved preferences; do not force auto-updates or automatically opt the plugin in.
- Record an updater upgrade in `CHANGELOG.md`. If retaining the bundled version, explain the compatibility or other concrete reason in the change summary.
- Keep a local installer named `chroma-utilities-VERSION.zip` for each release. Publish only the identical `chroma-utilities.zip` asset to GitHub; versioned ZIPs stay local and are not uploaded as release assets. Stage the unversioned asset in a temporary folder only when publishing. Keep the internal plugin folder named `chroma-utilities`.
- After preparing a plugin update, state whether it has been published. If publication is still needed, remind Nate to publish a stable GitHub Release for the matching version tag with only `chroma-utilities.zip` attached. Pushing code or a tag alone does not deliver WordPress updates. If the release is already published, provide its link instead of a publish reminder.
- Save a copy of every versioned release ZIP in the local `../archive/` folder. Preserve older archives when replacing the current downloads; never overwrite an archived published version with different contents. The archive stays outside the Git repository and plugin package.
- Keep the local workspace clean: retain only the current `../chroma-utilities-VERSION.zip` installer beside the source folder for easy version reference. Keep a copy of every version in `../archive/`. Remove older installers from `../` only after verifying that their archived copies are byte-for-byte identical. Do not retain an unversioned ZIP beside the source folder; create that required GitHub asset only in a temporary publishing folder.
