# PRD — NHR Core Contributions, slug `nhrrob-core-contributions`

Status: **1.4.1 released** · **2.0.0 built and verified locally 2026-10-10** (uncommitted; Robin reviews, commits and tags) · Owner: Nazmul Hasan Robin (nhrrob)
Last revised: 2026-10-10 (rewritten after the 2.0.0 build; the plan became one release)

> Dev-only document. Excluded from distribution (`.distignore` + `.gitattributes export-ignore`).
> UI/visual spec: **[DESIGN.md](./DESIGN.md)**. Architecture and commands: `CLAUDE.md`.
> What already shipped is recorded in `readme.txt` (changelog + feature list), not here.
> Reference repo for tooling and conventions: `nhrrob-options-table-manager` ("NHROTM").

## 1. Principles (non-negotiable)

### 1.1 Small and front-end light

This plugin renders on public pages, so front-end weight matters more than admin weight.

- A page with blocks loads **one stylesheet and no script**. No editor bundle, no CSS framework, no React on the front end.
- No runtime PHP dependencies. `vendor/` is the autoloader only.
- No chart or UI library. The chart is HTML with CSS heights.
- Measure the dist zip before each release and record it in §4.

### 1.2 A page view never does the heavy work

Every remote call goes through `Remote::get()`: answers are cached, a failure is cached for five minutes, and a block with no data renders nothing for visitors. On a cold cache a block makes at most one short request per source it shows (a list page, the profile). Anything needing more than that (per-year totals, components, release credits) is gathered by the background sync and only read at render time.

### 1.3 Official sources first

Prefer documented JSON APIs. Reading an HTML page is allowed only where no API exists, after a test proves it works from a server, and it must degrade to "not shown" (Core Trac search went behind a browser check, which forced the 1.4.1 move to the GitHub mirror). The one HTML source today is the WordPress.org profile page.

### 1.4 Blocks follow the theme

Blocks inherit the theme's colours, typography and spacing; the plugin adds one accent colour. Variations are block styles, not hardcoded palettes. Details in DESIGN.md.

### 1.5 Read-only

The plugin shows public data about a username an editor entered. It never writes to WordPress.org or GitHub, and it never looks up a username supplied by an anonymous visitor.

## 2. Product (as built in 2.0.0)

**Positioning:** the plugin a WordPress contributor installs to show their contribution record on their own site: core, Gutenberg, release credits, team and event badges, and published plugins and themes.

| Block | Shows | Source |
|---|---|---|
| **NHR Core Contributions** | Paginated list for core or Gutenberg; styles Default, Minimal, Timeline | GitHub commit search |
| **Contributor Card** | Name, avatar, headline numbers, first and latest contribution, top components | profile page + sync |
| **Contribution Stats** | Headline numbers | sync |
| **Contribution Activity** | Core contributions per year | sync |
| **Release Badges** | WordPress releases that credit the username | sync (credits API) |
| **WordPress.org Badges** | Profile badges: Core, Docs, Polyglots, Meta, WordCamp speaker/organizer… | profile page |
| **Plugins and Themes** | Published plugins or themes with installs and rating | WordPress.org API |

**Admin:** Tools → Core Contributions (General · Data · Preview).
**AI:** five read-only abilities, one per kind of data (`Abilities::definitions()`).

## 3. Tooling (parity with NHROTM, done 2026-10-10)

`composer.json` dev tools and scripts · `phpcs.xml` (full WordPress ruleset + PHPCompatibilityWP) · `phpunit.xml` + `tests/` (WP_Mock) · `.husky/pre-commit` · npm `lint`/`prepare` · workflows `php.yml` (PHPCS, PHP 7.4–8.6 matrix with unit tests and runtime smoke), `plugin-check.yml`, `security.yml` (Semgrep, PHPStan, endpoint authorization probe) · `.github/security/*` · repo `CLAUDE.md` · `.ai/DESIGN.md`, `.ai/design/` · `uninstall.php` · `.distignore`/`.gitattributes` cover dev files · the deploy workflow installs the production autoloader.

Not ported: `PRD-PRO.md` (no paid tier is planned).

## 4. Release 2.0.0

### 4.1 Defects fixed (found in the 1.4.1 audit)

| # | Defect | Fix |
|---|---|---|
| D1 | Pagination past page 100 failed (GitHub returns 1,000 results per search) | `Commits::pages()` caps the page count; a note says "Showing the latest 1,000 of N" |
| D2 | A failed fetch was retried on every page view | `Remote` caches failures for 5 minutes |
| D3 | The count method returned HTML on failure | Sources return an array or `WP_Error` |
| D4 | The front end loaded the editor bundle and Tailwind CSS | No front-end script; one hand-written stylesheet |
| D5 | Editor showed "Pagination is hidden in the editor!" | Real pagination, inert inside `Disabled` |
| D6 | "Posts Per Page" was saved but never used | Applied to the list and the abilities (1–100) |
| D7 | Settings strings were not translatable | Rewritten with `@wordpress/i18n` |
| D8 | Readme documented a disabled shortcode | Shortcode code and text removed; block-only |
| D9 | `get_wporg_display_name()` called a URL that does not exist | Replaced by `Sources\Profile` |
| D10 | Stale versions in `package.json` and `block.json` | All 2.0.0; release skill lists them |
| D11 | No `uninstall.php` | Added |

### 4.2 Behaviour changes users will notice

- Presets became block styles. Minimal and Timeline migrate automatically; Default, Modern and Card all render as the new Default.
- Title, meta and pagination colour attributes and title font settings are gone; one accent colour plus the block's own colour/typography supports replace them.
- The "Show Time" toggle became "Show dates" (date only, default on).
- The `nhrcc/get-core-contributions` ability returns ISO 8601 dates and `component` separately from `description`.
- The pagination query variable changed from `front_paged` to `nhrcc_core_page` / `nhrcc_gutenberg_page`.

### 4.3 Verified locally (2026-10-10)

PHPCS clean · 28 unit tests · JS lint clean · smoke test 0 notices · endpoint probe 16 checks, 0 failures · every block checked on the front end and in the editor (Twenty Twenty-Five) for `nhrrob` (6 contributions) and `audrasjb` (1,387, the over-1,000 path) · sync completed for both · PHPStan level 0 and Plugin Check clean · a block saved by 1.4.1 (preset, old colour attributes) renders · dist zip **75 KB** (`wp dist-archive` of the working tree).

### 4.4 Release gate (still to run, needs a commit)

All three PR workflows green on GitHub · `~/Sites/nhrrob-staging/bin/prerelease-check nhrrob-core-contributions` exit 0 · upgrade from the released 1.4.1 zip on staging with saved blocks in content · a classic theme check.

## 5. Not built, and why

| Idea | Why not |
|---|---|
| Translation string counts | translate.wordpress.org has no per-user API. The Translation Contributor/Editor badges are shown instead. |
| WordCamp talks and organising in detail | No per-user index across camp sites. The WordCamp Speaker/Organizer badges are shown instead. |
| Manual "Highlights" block | Was the fallback if badges could not be fetched. They can, and a core List block covers hand-written items. |
| Per-source on/off toggles in settings | A source is only contacted when its block is on a page; a toggle would add nothing. |
| Live, clickable pagination in the editor preview | Preview-only value; needs its own state and requests. |
| Shortcode | Block-only since 1.x; never worked in recent versions. |
| Paid tier or add-on API | No demand. |

## 6. Open decisions

1. **Version number:** built as 2.0.0 (breaking attribute changes). The release skill's own rule would call it 1.5.0.
2. **Brand art:** icon and banner were drafted as SVG in `.ai/design/brand/`; Robin approves or replaces them before the WP.org assets are synced.
3. **"Requires at least":** still 6.0. Nothing below 6.3 was tested; raising it to 6.3 would match what `apiVersion: 3` blocks expect.

## 7. Decided (don't relitigate)

- 2026-10-10: one release carries everything (tooling, fixes, redesign, new blocks, beyond-core sources).
- 2026-10-10: tooling and AI files match NHROTM, except `PRD-PRO.md`.
- 2026-10-10: block-only; the shortcode is removed.
- 2026-10-10: profile badges come from the profile page, with silent degradation, because no API exists.
- 2026-10-10: full `WordPress` PHPCS ruleset, same as NHROTM.
- 2026-10-10: history is gathered by WP-Cron and stored in one option; no custom tables.
- 1.4.1: core data comes from the `WordPress/wordpress-develop` GitHub mirror, not Core Trac search.

## 8. Sources

- NHROTM repo: `.ai/`, `CLAUDE.md`, `phpcs.xml`, `.husky/`, `.github/workflows/`, `.github/security/`.
- GitHub REST API: commit search (1,000-result cap; 10 searches a minute unauthenticated, verified 2026-10-10; date-range qualifier `committer-date:`).
- Gutenberg commits credit contributors with `Co-authored-by: … <username@git.wordpress.org>` (verified 2026-10-10).
- WordPress.org: `core/stable-check/1.0`, `core/credits/1.1` (from 3.2), `plugins/info/1.2`, `themes/info/1.2`, profile page markup (`.medal.badge-*`, verified 2026-10-10).
- WordPress.org plugin guidelines on External Services disclosure.
