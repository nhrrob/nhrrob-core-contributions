# CLAUDE.md

## Commands

```bash
# JS (assets/blocks/src → assets/blocks/build, assets/dashboard/src → assets/dashboard/build)
npm run build        # both bundles
npm run dev          # watch mode, both bundles
npm run lint         # wp-scripts lint-js over both src directories
npx wp-scripts lint-js assets/blocks/src assets/dashboard/src --fix

# PHP
composer install            # dev toolchain (phpcs + phpunit)
composer install --no-dev   # production autoload (ships in the zip)
composer run phpcs          # WordPress Coding Standards + PHPCompatibilityWP
composer run phpcbf         # auto-fix what PHPCS can
composer run test:unit      # PHPUnit (WP_Mock, no DB, no network)

# Inside a WordPress install
wp eval-file .github/ci/smoke.php                       # read-only; fails on any PHP notice from the plugin
python3 .github/security/probe.py --wp "wp --path=…" --script "$PWD/.github/security/endpoint-probe.php"
```

**PHP support: 7.4 and every later version, tested through 8.6.** `composer.json` pins `config.platform.php` to 7.4.33 so dev dependencies resolve to versions that run on every supported PHP.

`vendor/autoload.php` + `vendor/composer/*` are committed as the **production (`--no-dev`) autoloader**: the zip, GitHub downloads and CI all load it. A local `composer install` rewrites them to reference dev packages that `.gitignore` excludes, so run `composer install --no-dev` before committing anything under `vendor/`.

Always run `npm run lint` **and** `npm run build` after any change under `assets/*/src/`. The compiled `build/` output is committed and is what ships; editing `src/` alone changes nothing live.

## Pre-commit gate

`.husky/pre-commit` (active through `git config core.hooksPath .husky`, set by `npm install`) blocks the commit on any failure of `npm run lint`, `composer run phpcs` and `composer run test:unit`. The last two are skipped when `vendor/bin/` is absent.

The codebase is PHPCS-clean, so any new violation is yours; fix it rather than widening `phpcs.xml`.

## Architecture

- Main class `Nhrcc_Core_Contributions` in `nhrrob-core-contributions.php`; namespace `Nhrcc\CoreContributions\`, PSR-4 → `includes/`. Each class has an `init()` that registers its hooks.
- **`Remote::get( $url, $ttl, $parse )`** is the only place that makes an HTTP request. It caches what `$parse` returns (never the raw body), caches a failure for `FAILURE_TTL` (5 minutes) so a rate-limited service is not hit on every page view, and sends the GitHub token to `api.github.com` only. Cache keys include `cacheVersion` from the settings; "Refresh all data" raises it.
- **Sources** (`includes/Sources/`), all static and all returning an array or `WP_Error`:
  - `Commits`: GitHub commit search. `core` searches `WordPress/wordpress-develop` for `props <username>`; `gutenberg` searches `WordPress/gutenberg` for the `<username>@git.wordpress.org` co-author trailer. GitHub returns at most 1,000 results per search (`MAX_RESULTS`); `pages()` caps pagination there.
  - `Credits`: `api.wordpress.org/core/stable-check` for the list of majors, `core/credits/1.1` per major (available from 3.2).
  - `Directory`: `api.wordpress.org/{plugins,themes}/info/1.2` by author.
  - `Profile`: parses `profiles.wordpress.org/<username>/` (there is no profile API) for name, avatar, member-since and badges. Anything the page stops providing is left out; a page that does not parse is a failure.
- **`Sync`**: per-year totals, top components, first/latest dates, Gutenberg total and credited releases need many requests, so WP-Cron (`nhrcc_sync`, one single event per username) gathers them at `GITHUB_PER_RUN` = 5 GitHub and `WPORG_PER_RUN` = 20 WordPress.org requests per run and stores them in the non-autoloaded option `nhrcc_summaries` (at most 20 usernames). `Sync::summary()` only reads the option and schedules a run when the summary is missing or older than a day. Above 1,000 commits the years are counted with one date-ranged search per year. **A page view never performs the sync.**
- **Blocks** (`Blocks::BLOCKS`, seven dynamic blocks): `block.json` files live in `assets/blocks/src/<dir>/`, are copied to `build/` and registered from there with a `render_callback` in `Blocks`. One editor script (`nhrcc-blocks-editor`, one shared `Edit` using `ServerSideRender` inside `Disabled`) and one stylesheet (`nhrcc-blocks`). No view script. The block name `nhrcc-core-contributions/core-contributions-block` predates 2.0 and must not change; its legacy `preset` attribute is mapped to a block style in both `Blocks::render_list()` and the editor.
- A block with nothing to show returns a `.nhrcc-notice` for users who can `edit_posts` and an empty string for visitors.
- **Settings**: `nhrcc_settings` (`username`, `postsPerPage`, `cacheDuration`, `cacheVersion`), always read through `Settings::get()`, which sanitizes and clamps. The GitHub token is the separate non-autoloaded option `nhrcc_github_token` and is never returned by REST (`hasToken` only).
- REST namespace `nhrcc-core-contributions/v1`: `GET|POST /settings`, `POST /refresh`. The editor preview uses core's block renderer, not a route of ours.
- **Abilities** (WordPress 6.9+): five read-only `nhrcc/*` abilities from `Abilities::definitions()`; `tests/AbilitiesTest.php` pins the list.
- Admin screen: Tools → Core Contributions (`Admin`), a `@wordpress/components` app in `assets/dashboard/src/index.js`.
- Options written: `nhrcc_settings`, `nhrcc_github_token`, `nhrcc_summaries`, plus `nhrcc_*` transients. `uninstall.php` removes all of them and the cron hook.

## Documentation sync

In the same change as any fix or feature, update what describes it: `readme.txt` (feature list, FAQ, **External Services** for any new host, changelog), `.ai/PRD.md`, `.ai/DESIGN.md` and the Architecture section above.

## Skills

- `.ai/skills/release_plugin.md`: version bump, PR, tag, publish procedure. Versions live in the plugin header, `Nhrcc_Core_Contributions::VERSION`, `readme.txt` (Stable tag), `package.json` and every `block.json`.

## WP.org art

`.wordpress-org/banner-*.png` and `icon-*.png` are rendered from `.ai/design/brand/banner.svg` and `icon.svg`; edit the SVG, never the PNG. Screenshots must match the captions under `== Screenshots ==` in `readme.txt`.

## Security invariants

- Every REST route: `permission_callback => [ $this, 'can_manage' ]` (`manage_options`). Every ability: `[ $this, 'can_read' ]` (`edit_posts`) and read-only. The plugin registers no `wp_ajax_*` handlers.
- A username reaches a URL only after `Settings::username()` (lowercase `a-z0-9._-`, 60 characters). Hosts are fixed strings; never build a request URL from user input beyond that username.
- Everything from a remote service is untrusted: sources strip tags, and `Blocks` escapes every value on output. The accent colour reaches a `style` attribute only after the pattern check in `Blocks::wrap()`.
- `.github/workflows/security.yml` runs Semgrep, PHPStan and the endpoint authorization probe on every pull request.
