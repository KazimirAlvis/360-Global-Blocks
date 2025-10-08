=== 360 Global Blocks ===
Contributors: kazalvis
Tags: gutenberg, blocks, healthcare, patientreach360
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.3.7
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
2. Build a ZIP whose root folder is `360-global-blocks` (matching the plugin slug).
3. Publish a tagged GitHub release (e.g. `v1.3.6`) and attach the ZIP as `360-global-blocks.zip`.
4. Point `plugin-manifest.json`'s `download_url` at the release asset and push the manifest to the `main` branch.
5. In any WordPress install, visit Tools → 360 Blocks Updates and click "Force Update Check Now" to confirm the site sees the new version.

== Frequently Asked Questions ==

= Does this plugin require a GitHub access token? =
No. The updater fetches a JSON manifest over HTTPS. Keep the repository public or host the manifest on an accessible URL. If you make it private, proxy the manifest and ZIP download through an authenticated endpoint.

== Changelog ==

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
