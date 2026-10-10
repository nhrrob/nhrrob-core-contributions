=== NHR Core Contributions ===
Contributors: nhrrob
Tags: contributions, core, gutenberg, contributor, profile
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Show your WordPress contributions on your own site: core props, Gutenberg, release credits, profile badges, plugins and themes.

== Description ==
- 🚀 [GitHub Repository](https://github.com/nhrrob/nhrrob-core-contributions): Found a bug or have a feature request? Let us know!
- 💬 [Slack Community](https://join.slack.com/t/nhrrob/shared_invite/zt-2m3nyrl1f-eKv7wwJzsiALcg0nY6~e0Q): Got questions or just want to chat? Come hang out with us on Slack!

You contribute to WordPress. NHR Core Contributions puts that record on your own website: enter a WordPress.org username and add the blocks you want. Everything is fetched for you and kept up to date.

`<?php echo 'Shine a Light on Your WordPress Core Work!'; ?>`

### ✨ Blocks
- **NHR Core Contributions**: a paginated list of every core changeset that gives you props, with component, ticket, changeset and date. Switch it to Gutenberg to list the Gutenberg commits that credit you. Three styles: Default, Minimal and Timeline.
- **Contributor Card**: your name and avatar, headline numbers, first and latest contribution and your top core components.
- **Contribution Stats**: the headline numbers on their own.
- **Contribution Activity**: your core contributions per year as a bar chart.
- **Release Badges**: every WordPress release whose credits include you.
- **WordPress.org Badges**: your profile badges, such as Core, Documentation, Polyglots (translation), Meta, Accessibility and WordCamp speaker or organizer.
- **Plugins and Themes**: the plugins or themes you have published on WordPress.org, with installs and rating.

### 🧩 Built to fit your site
- Blocks follow your theme's colors and typography and work in light and dark designs. Pick one accent color per block if you want.
- No JavaScript and one small stylesheet on the front end.
- A default username in Tools → Core Contributions, or a different username per block.
- Data is cached. Per-year totals, top components and release credits are gathered in the background, never while a visitor waits.
- **AI Agent & MCP Ready**: on WordPress 6.9 and later, AI agents and MCP clients (through the WordPress MCP Adapter) can read the same data through five read-only abilities.

### 📦 Block Usage
- Open a post or page in the block editor.
- Add any of the blocks above (search for "contribution").
- Enter a WordPress.org username in the block settings, or leave it empty to use the default one.
- Publish.

## External Services

The plugin fetches public contribution data for the WordPress.org username you enter. That username is the only data sent. Requests are made from your server when a block (or an AI ability) needs data that is not cached yet, and by a background task that gathers contribution history.

**GitHub API** (https://api.github.com/): lists the commits in the official WordPress core mirror (WordPress/wordpress-develop) and in WordPress/gutenberg that credit the username. If you save a GitHub token in the settings, it is sent to GitHub with these requests and to no one else.
- [Terms of Service](https://docs.github.com/en/site-policy/github-terms/github-terms-of-service)
- [Privacy Policy](https://docs.github.com/en/site-policy/privacy-policies/github-general-privacy-statement)

**WordPress.org** (https://api.wordpress.org/ and https://profiles.wordpress.org/): release credits, the plugins and themes published by the username, and the public profile page (display name, member-since date and badges). Plugin icons and theme screenshots are shown from WordPress.org servers (ps.w.org, ts.w.org), so a visitor's browser requests them.
- [Privacy Policy](https://wordpress.org/about/privacy/)

**Gravatar** (https://secure.gravatar.com/): the contributor's profile picture is shown from Gravatar, so a visitor's browser requests it.
- [Terms of Service](https://wordpress.com/tos/)
- [Privacy Policy](https://automattic.com/privacy/)


== Installation ==

1. Upload the plugin files to the /wp-content/plugins/nhrrob-core-contributions directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to Tools → Core Contributions and save your WordPress.org username.
4. Add the blocks to any post or page.


== Frequently Asked Questions ==

= How do I find my WordPress.org username? =
It is the last part of your profile address. For https://profiles.wordpress.org/nhrrob/ the username is `nhrrob`.

= Does it show all of my contributions? =
The total is always complete. The list pages through your latest 1,000 contributions, which is the most GitHub's search returns. The Contribution Activity chart covers every year.

= Why are some blocks empty right after I add them? =
Contribution Activity, Release Badges and some numbers on the card need your full history. It is gathered in the background and usually arrives within a few minutes. Visitors see nothing until it is ready; you see a short note.

= Do I need a GitHub token? =
No. Without one GitHub allows a few requests a minute, which is enough because results are cached. If you show many usernames, add a token under Tools → Core Contributions → Data. A token with no scopes is enough.

= Is there a shortcode? =
No. The plugin is block-only.

= I used the Modern or Card preset. What happened to it? =
The five presets became three block styles. Minimal and Timeline carry over automatically; Default, Modern and Card now use the new Default style. Use the block's color, typography and border settings and the accent color to adjust it.

= Can an AI agent or MCP client use this plugin? =
Yes, on WordPress 6.9 or later. The plugin registers five read-only abilities with the WordPress Abilities API: `nhrcc/get-core-contributions`, `nhrcc/get-gutenberg-contributions`, `nhrcc/get-contribution-summary`, `nhrcc/get-profile` and `nhrcc/get-plugins-and-themes`. Each takes a WordPress.org username, needs a signed-in user who can edit posts, and contacts the same services as the blocks. To use them over MCP, install the WordPress MCP Adapter plugin.

= Which PHP versions are supported? =
PHP 7.4 and every later version. Each change is tested on PHP 7.4 through 8.5, and on the upcoming 8.6.

= How does the plugin handle my data? =
It sends only the WordPress.org username you enter to the services listed under External Services, and stores the public data it gets back on your site. Deleting the plugin removes its settings and everything it stored.


== Screenshots ==

1. Contributor Card, Contribution Stats and Contribution Activity
2. Release Badges and WordPress.org Badges
3. NHR Core Contributions list with pagination
4. Plugins and Themes
5. The blocks in the editor, with their settings
6. Tools → Core Contributions settings

== Changelog ==

= 2.0.0 - 10/10/2026 =
- New: Contributor Card, Contribution Stats, Contribution Activity, Release Badges, WordPress.org Badges and Plugins and Themes blocks.
- New: The NHR Core Contributions block can list Gutenberg contributions.
- New: Redesigned list with contributor header, component, ticket and changeset. It follows the theme's colors and typography.
- New: Optional GitHub token, a "Refresh all data" button and a live preview on the settings screen.
- New: Four more read-only AI abilities (Gutenberg contributions, summary, profile, plugins and themes).
- Changed: Presets are now block styles (Default, Minimal, Timeline). Modern and Card use the new Default style. The separate title, meta and pagination color settings are replaced by one accent color and the block's own color settings.
- Changed: The `nhrcc/get-core-contributions` ability returns ISO 8601 dates and the component separately from the description.
- Fixed: Pagination failing beyond page 100 for contributors with more than 1,000 contributions.
- Fixed: Pagination is now shown in the editor instead of a "hidden" notice.
- Fixed: The contributions-per-page setting is now applied.
- Fixed: A failed request to GitHub is no longer repeated on every page view.
- Improved: The front end loads no JavaScript and one small stylesheet. Tailwind CSS and the jQuery scripts are removed.
- Improved: The settings screen is translatable. Deleting the plugin removes its data.
- Developer: coding standards (WPCS), unit tests, Plugin Check and a security review run on every pull request.

= 1.4.1 - 10/10/2026 =
- Fixed: Contributions showing as 0 for every username. WordPress Core Trac now blocks automated requests, so the data is fetched from the official WordPress core mirror on GitHub instead.
- Fixed: The AI ability returning an error when the contribution count was served from cache.

= 1.4.0 - 10/10/2026 =
- New: AI agent and MCP support. On WordPress 6.9+ the read-only `nhrcc/get-core-contributions` ability returns a username's core contributions.
- Compatibility: verified on PHP 7.4 through 8.5 and on PHP 8.6 release candidates.
- Developer: every pull request now runs the PHPCompatibilityWP ruleset, a syntax check and a runtime smoke test on each supported PHP version.
- WordPress tested up to version is updated to 7.1

= 1.3.2 - 09/05/2026 =
- WordPress tested up to version is updated to 7.0
- Few minor bug fixes & improvements

= 1.3.1 - 07/02/2026 =
- Fixed: Fatal error on plugin activation

= 1.3.0 - 28/01/2026 =
- Added: Two new presets (Card & Timeline) added
- Added: Show Time toggle added
- Few minor bug fixing & improvements

= 1.2.0 - 12/01/2026 =
- Added: New preset "Modern" added
- Added: Style controls added for the presets
- Few minor bug fixing & improvements

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

= 2.0.0 =
Six new blocks and a redesigned list. Presets become block styles: check pages that used the Modern or Card preset or custom title colors.
