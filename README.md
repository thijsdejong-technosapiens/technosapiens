# Techno Sapiens WordPress starter

Section-based WordPress starter theme using ACF Pro and custom Gutenberg blocks. Clone this repository to start a new project, then rename the local site, database, and environment values to match that project.

The theme lives at `public/wp-content/themes/technosapiens`.

## Requirements

Use these versions to avoid incompatibility issues:

- PHP 8.4
- Node 24.16
- MySQL 8

## Local setup (Herd)

1. Open Herd and add the project root as a site: ```cd public``` & ```herd link technosapiens```
2. Enable PHP 8.4 for this site: ```herd isolate 8.4 --site='technosapiens'```
3. Enable HTTPS (secure) for this site: ```herd secure```

Create a local database for the project (name it after the site, for example `technosapiens_loc`).

The site should then be available at `https://technosapiens.test` (Herd uses the folder name by default).

## Install Composer packages

```bash
composer i
```

## Environment

Copy `.env.example` to `.env` and set database credentials, site flags, and license keys:

```bash
cp .env.example .env
```

`public/wp-config.php` loads this file from the project root.

## Install Node modules

```bash
nvm use 24.16.0
npm i
```

## JS / SCSS development

Start the Webpack watcher to compile assets on file changes:

```bash
nvm use 24.16.0
npm run dev
```

For live reload during development:

```bash
npm run dev:reload
```

Webpack writes compiled assets to `public/wp-content/themes/technosapiens/dist/`.

## Production build

Compile and minify assets:

```bash
nvm use 24.16.0
npm run build
```

## Theme structure

| Path | Purpose |
| --- | --- |
| `functions.php` | Theme bootstrap: menus, components, plugins, section blocks |
| `sections/` | ACF Gutenberg sections (PHP class, partial, SCSS, ACF JSON) |
| `blocks/` | Additional custom blocks |
| `inc/app/` | Project-specific PHP (settings, editor, integrations) |
| `inc/core/` | Shared theme core |
| `scss/` | Global styles |
| `js/` | Global scripts |
| `icons/` | SVG icons compiled into a sprite |

Pages are built from reusable **sections**. Each section is a Gutenberg block with ACF fields. Included starters: accordion, content, CTA banner, divider, page buttons, stats, and USPs.

To add a section, copy an existing folder under `sections/`, register the class in `Theme::initBlocks()`, and keep `scss/` and `js/` files in that folder so Webpack picks them up.

## Agent skills (Matt Pocock)

This repo is configured for [Matt Pocock's engineering skills](https://github.com/mattpocock/skills) (triage, to-tickets, wayfinder, and related flows).

- `AGENTS.md` — index of tracker, triage labels, and domain docs
- `docs/agents/` — issue tracker (GitHub), triage label vocabulary, and domain doc layout

Run `/setup-matt-pocock-skills` in Cursor if you need to reconfigure (e.g. switch trackers). Edit `docs/agents/*.md` for day-to-day changes.

## WordPress

After Herd is serving the site:

1. Complete the WordPress install if the database is empty.
2. Activate the **Techno Sapiens** theme if not already set as default.
3. Enable ACF Pro (required for sections and several theme features).
4. Assign menus to the registered locations (primary, secondary, footer, mobile top, mobile bottom).
