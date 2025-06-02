=== NHR Core Contributions ===
Contributors: nhrrob
Tags: contributions, core, community, open source, profile
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.1.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display Core Contributions stat in your own website.

== Description ==
- 🚀 [GitHub Repository](https://github.com/nhrrob/nhrrob-core-contributions): Found a bug or have a feature request? Let us know!
- 💬 [Slack Community](https://join.slack.com/t/nhrrob/shared_invite/zt-2m3nyrl1f-eKv7wwJzsiALcg0nY6~e0Q): Got questions or just want to chat? Come hang out with us on Slack!

In the vast tapestry of the WordPress universe, let your contributions shine like stars. The NHR Core Contributions Plugin elegantly weaves your efforts into your website, displaying your dedication with real-time stats. Let the world witness the story of your commitment to the WordPress community.

`<?php echo 'Shine a Light on Your WordPress Core Work!'; ?>`

### ✨ Features
- Real-Time Stats: Your latest WordPress core contributions are automatically displayed.
- Customizable Display: Display core contributions based on username.
- Simple Integration: Easily add contribution stats using the Gutenberg block – no shortcode needed.
- Pagination: Fetch all of your contributions with pagination.

### 📦 Block Usage
Easily add contribution stats using the Gutenberg editor:

- Open the post or page in the block editor.
- Add the “NHR Core Contributions” block.
- Enter your WordPress.org username in the block settings.
- Select a design style and publish.

## External Services

This plugin uses the WordPress Core Trac API to retrieve core contributions data. By using this plugin, data is sent to the following service:

- **WordPress Core Trac**: https://core.trac.wordpress.org/
- [Privacy Policy](https://wordpress.org/about/privacy/)

No personal or sensitive data beyond the specified username is shared. This data is used solely to fetch and display the user's contributions to the WordPress core project.


== Installation ==

1. Upload the plugin files to the /wp-content/plugins/nhrrob-core-contributions directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the shortcode [nhrcc_core_contributions username="your_wp_username"] to display your contributions.


== Frequently Asked Questions ==

= How do I find my WordPress username? =
Your WordPress username is the same as the one you use on WordPress.org.
E.x. https://profiles.wordpress.org/nhrrob/ (here username is: nhrrob)

= Does it shows all of my contributions =
Absolutely. It fetches all of your contributions with pagination. 

= Can I see total contributions count? Also link to the details for each ticket? =
Yes. Total count is shown at the top. Also all tickets are linked to the changesheet url.

= How does the plugin handle my data? =
The NHR Core Contributions plugin only transmits the WordPress.org username specified by the user to the WordPress Core Trac API. This is done solely to fetch and display your core contributions. No personal or sensitive data beyond the username is shared or stored.


== Screenshots ==

1. Demonstration of the plugin
2. NHR Core Contributions block with default preset
3. NHR Core Contributions block with minimal preset
4. Frontend view of the block with pagination
5. Dashboard => Tools => Core Contributions page => Settings page

== Changelog ==

= 1.1.6 - 02/06/2025 =
- Assets updated
- Few minor bug fixing & improvements

= 1.1.5 - 29/03/2025 =
- WordPress tested up to version is updated to 6.8
- Few minor bug fixing & improvements

= 1.1.4 - 23/03/2025 =
- Fixed: Undefined constant name causes fatal error
- Few minor bug fixing & improvements

= 1.1.3 - 13/02/2025 =
- Added: React powered Settings page added
- Few minor bug fixing & improvements

= 1.1.2 - 15/01/2025 =
- Added: Two new presets (default & minimal) added. Props @faguni22
- Few minor bug fixing & improvements

= 1.1.1 - 12/01/2025 =
- Fixed: Cant select block or delete block. Props @faisalahammad.
- Fixed: Page heading style got changed due to conflict. Props @faisalahammad
- Updated: Plugin shortcode UI updated to display username along with total contribution count. Props @faisalahammad
- Few minor bug fixing & improvements

= 1.1.0 - 05/01/2025 =
- Added: Core Contributions Block
- Few minor bug fixing & improvements
- Happy New Year 2025!

= 1.0.6 - 18/10/2024 =
- WordPress tested up to version is updated to 6.7
- Few minor bug fixing & improvements

= 1.0.5 - 29/07/2024 =
- Updated: Unused tags are removed
- Few minor bug fixing & improvements

= 1.0.4 - 29/07/2024 =
- Fixed: Core contributions count related error
- Added: Related tags are added
- Few minor bug fixing & improvements

= 1.0.3 - 29/07/2024 =
- Added: 3rd party services in plugin readme 
- Improved: Nonce verification mechanism
- Updated: WordPress tested up to version bumped
- Few minor bug fixing & improvements

= 1.0.2 - 11/07/2024 =
- Few minor bug fixing & improvements

= 1.0.1 - 26/06/2024 =
- Textdomain issue fixed

= 1.0.0 - 16/06/2024 =
- Initial beta release. Cheers!


== Upgrade Notice ==

= 1.0.0 =
- This is the initial release. Feel free to share any feature request at the plugin support forum page.