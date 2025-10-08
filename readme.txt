=== 360 Global Blocks ===
Contributors: kazalvis
Tags: gutenberg, blocks, healthcare, patientreach360
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.3.10
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Custom Gutenberg blocks tailored for the 360 network. Includes hero layouts, CTA components, clinic listings, info cards, and PatientReach360 assessment integrations designed for healthcare organizations.

== Installation ==
1. Upload the plugin files to `/wp-content/plugins/360-global-blocks/` or install via the Plugins screen.
2. Activate through the Plugins screen.
3. Find the blocks under the "360 Blocks" category in the block editor.

== Release Workflow ==
1. Update `360-global-blocks.php`, `readme.txt`, and `plugin-manifest.json` with the new version, changelog, and compatibility data.
2. Commit and push to `main`—the manifest points to the `main` branch zip, so no manual release or asset upload is required.
3. In any WordPress install, visit Tools → 360 Blocks Updates and click "Force Update Check Now" to confirm the site sees the new version.
4. From the Plugins screen (or Dashboard → Updates) click "Update now" to install the branch build.

== Updater Notes ==
* Since version 1.3.7 the plugin auto-updater delivers the `main` branch archive from GitHub (`plugin-manifest.json` → `download_url`).
* The function `sb_global_blocks_rename_github_package()` (in `360-global-blocks.php`) renames GitHub's extracted folder to `360-global-blocks/` via the `upgrader_source_selection` filter so WordPress recognizes the plugin after extraction.
* Version 1.3.9 hardens that rename logic to ensure branch archives never leave a `-main` suffix in the plugin directory.
* Diagnostics remain available under Tools → 360 Blocks Updates for checking detected versions, manifest URL, and errors.

== Frequently Asked Questions ==

= Does this plugin require a GitHub access token? =
No. The updater fetches a JSON manifest over HTTPS. Keep the repository public or host the manifest on an accessible URL. If you make it private, proxy the manifest and ZIP download through an authenticated endpoint.

== Changelog ==

= 1.3.10 =
* Routine validation bump to confirm the branch-archive updater keeps working.

= 1.3.9 =
* Hardened the branch-archive updater so the plugin folder always remains `360-global-blocks` after updates.

= 1.3.8 =
* Confirmed the branch-archive updater path works end-to-end and documented the helper filter for future reference.

= 1.3.7 =
* Pulled the most recent FTP edits back into Git and lined up the 1.3.7 release package.

= 1.3.6 =
* Captured the FTP hotfixes into source control and prepped the manifest for the next GitHub release package.

= 1.3.5 =
* Replaced the Plugin Update Checker dependency with a lightweight manifest-driven updater.
* Added a GitHub-hosted `plugin-manifest.json` and refined the update diagnostics page.

= 1.3.4 =
* Switched to the Plugin Update Checker library for reliable GitHub updates.
* Synced the Git repository with the live FTP copy and updated diagnostics tooling.

= 1.3.3 =
* Version bump for live updater smoke test.
* Confirmed admin diagnostics tooling.

= 1.3.2 =
* Added update diagnostics page and slug fixes.

= 1.3.1 =
* Initial GitHub-based auto-update rollout.

= 1.0.0 =
* Initial release with hero, CTA, info cards, clinic listings, and PatientReach360 integration.
