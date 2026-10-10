# Design Spec — NHR Core Contributions blocks

> Dev-only document. Product scope is in [PRD.md](./PRD.md). The implementation is `assets/blocks/src/style.css` (blocks) and `assets/dashboard/src/` (settings screen). `design/mockup.html` shows every block with sample data, using the built stylesheet.

## 0. Principles

1. **The theme owns the look.** Text colour, link colour, font family and font size are inherited. Surfaces, borders and muted text are derived from `currentColor`, so a block is readable on any background, light or dark, without a dark-mode stylesheet.
2. **One accent.** `--nhrcc-accent` (default `#3858e9`) colours numbers, chart bars, the current page and the timeline dots. Each block has one "Accent color" setting; nothing else is the plugin's own colour.
3. **No wrapper chrome.** The block wrapper (`.nhrcc`) has no padding, border or background of its own; the user's block-support settings apply to it cleanly. Only the Contributor Card draws a frame.
4. **HTML first.** No JavaScript on the front end. The chart is a list with CSS heights; pagination is plain links.
5. **Quiet failure.** Visitors never see an error or a placeholder. Editors see one dashed-border note (`.nhrcc-notice`).

## 1. Tokens (all on `.nhrcc`)

| Token | Value | Used for |
|---|---|---|
| `--nhrcc-accent` | `#3858e9`, or the block's accent setting | numbers, bars, current page, timeline dot |
| `--nhrcc-line` | `currentColor` 16% | borders, dividers |
| `--nhrcc-soft` | `currentColor` 5% | item, tile and entry surfaces |
| `--nhrcc-muted` | `currentColor` 68% | meta text, labels |
| `--nhrcc-tint` | accent 13% | chips |
| `--nhrcc-radius` | `10px` | items, tiles, entries (card: +4px) |

Browsers without `color-mix()` get fixed grey fallbacks declared first. Sizes are in `em`, so the theme's or the block's font size scales everything. Vertical rhythm inside a block is one `gap: 1.25em` on the wrapper grid.

## 2. Shared parts

- **Header** (`.nhrcc-head`): 56px round avatar, name (bold, links to the profile), `@username` muted, one muted line under it. Without a readable profile: username only, no avatar.
- **Chip** (`.nhrcc-chip`): pill on the accent tint, 600 weight; an optional muted count or date after the label.
- **Tile** (`.nhrcc-tile`): large accent number above a muted label. Markup is `dt` then `dd`, reversed visually. A tile with a zero value is not rendered.
- **Title** (`.nhrcc-title`): one 600-weight line above chips or the chart. Not a heading element, so the page outline stays the author's.

## 3. Blocks

| Block | Layout |
|---|---|
| NHR Core Contributions | header (optional) → list → pagination → "Showing the latest 1,000 of N" when capped. Each item: component chip, title link, muted meta (`#ticket · [changeset] · date`). |
| Contributor Card | framed: header → tiles → "First contribution … · Latest …" → top five component chips |
| Contribution Stats | tiles only: core, Gutenberg, releases, years contributing |
| Contribution Activity | title → one column per year from the first year to the current one; value above the bar, year below; scrolls sideways when narrow |
| Release Badges | title with the count → one chip per release, newest first, linking to the version page |
| WordPress.org Badges | title → one chip per badge with its "since" date |
| Plugins and Themes | grid (`minmax(17em, 1fr)`); plugin: 56px icon beside the text; theme: 4:3 screenshot above it |

List styles: **Default** (bordered rows on the soft surface), **Minimal** (divider lines only), **Timeline** (left rule with accent dots). Style selectors are written as `.is-style-x .nhrcc-item` so they match both on the front end and in the editor, where the style class sits on the outer wrapper.

Pagination uses core `paginate_links()` markup (`.page-numbers`): bordered boxes, the current page filled with the accent.

## 4. Editor

Each block renders through `ServerSideRender` inside `Disabled`, so the preview is the real output and its links, pagination included, cannot navigate away. Sidebar: a Settings panel (username, plus the block's own select and toggles) and an Accent panel. The username is applied when the field loses focus.

## 5. Settings screen

`@wordpress/components` only, no custom design system: a `TabPanel` with General (default username, contributions per page), Data (cache lifetime, GitHub token, Refresh all data, last sync time) and Preview (card, activity and list for the default username). Feedback is one dismissible `Notice` above the tabs; success wording ends in "successfully."

## 6. Brand

Icon and banner sources are `design/brand/icon.svg` and `banner.svg`. Motif: ascending contribution bars on the accent blue, the same shape the Activity block draws. The banner shows the plugin name, the one-line promise and a simplified Contributor Card.
