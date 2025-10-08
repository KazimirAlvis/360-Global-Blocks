=== 360 Global Blocks ===
Contributors: kazalvis
Tags: gutenberg, blocks, healthcare, patientreach360
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.3.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Custom Gutenberg blocks tailored for the 360 network. Includes hero layouts, CTA components, clinic listings, info cards, and PatientReach360 assessment integrations designed for healthcare organizations.

== Installation ==
1. Upload the plugin files to `/wp-content/plugins/360-global-blocks/` or install via the Plugins screen.
2. Activate through the Plugins screen.
3. Find the blocks under the "360 Blocks" category in the block editor.

== Frequently Asked Questions ==

= Does this plugin require a GitHub access token? =
No. The update checker works anonymously against the public GitHub repository. If you ever make the repository private, add `$sb_global_blocks_update_checker->setAuthentication( 'ghp_your_token' );` after the update checker is instantiated.

== Changelog ==

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
