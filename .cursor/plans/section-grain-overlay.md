# Plan: Section / hero grain overlay

Re-implement a static, neutral noise film on section shells and the main hero. Content (text, images, logos, titles, buttons) must stay above the grain.

Decisions locked in a prior grill session; do not re-litigate unless the product ask changes.

## Decisions (settled)

| Topic | Choice |
| --- | --- |
| Technique | Noise **film overlay** (not SVG displacement on the gradient) |
| Scope | Every `.section` + main hero (`hm-main-hero`) |
| CMS | Always on — no ACF toggle |
| Intensity | Medium — default opacity **0.1** |
| Compositing | Neutral grayscale film + opacity only (no soft-light / multiply) |
| Nested surfaces | Section/hero **shell only** — not card backgrounds |
| Motion | Static |
| Noise source | SVG `feTurbulence` (fractalNoise), desaturated |
| Vignets | Grain **under** vignets |
| Tunability | CSS vars `--ts-grain-opacity`, `--ts-grain-size` in `StylingComponent` |

Reference look: [Grainy Gradients](https://blog.master.dev/grainy-gradients/) — use the **noise primitive**, not the displacement-filter “final” technique.

## Stacking model

```
.section-background / hero bands     z-index: 0
.section-grain                       z-index: 1
vignets (::before / ::after)         z-index: 2
.container / hero meta content       z-index: 3+ (hero meta already uses ~30)
```

- Grain: `position: absolute; inset: 0; pointer-events: none; aria-hidden="true"`
- `.section` and `.hm-main-hero` need `position: relative`
- Do **not** put grain on `.section::before` / `::after` — vignets already use those

## Implementation steps

1. **Tokens** — in `inc/app/editor/StylingComponent.php` add:
   - `grain-opacity` → `0.1`
   - `grain-size` → `12.5rem` (tile size; higher `baseFrequency` in SVG = finer grit)

2. **SCSS** — add `inc/features/section-reference/scss/section-grain.scss` and `@use` it from `scss/technosapiens-frontend.scss` next to `section-background`.
   - Prefer a **data-URI SVG tile** (`feTurbulence` + `feColorMatrix` saturate `0`) as `background-image` on `.section-grain`, with `opacity: var(--ts-grain-opacity)` and `background-size: var(--ts-grain-size)`.
   - Avoid `filter: url(#…)` on every section if a repeating tile is enough (better paint cost).

3. **Z-index bumps** (required when grain is at 1):
   - `section-vignet.scss`: vignets `z-index: 2`
   - `atomic-container.scss`: `.container` `z-index: 3`
   - `section-background.scss`: `[data-bg="bands"] > .container` `z-index: 3`

4. **PHP — `SectionGrain`** — `inc/features/section-reference/SectionGrain.php` (autoload via existing `section-reference` scan):
   - Extend `Singleton`
   - `render()` echoes `<div class="section-grain" aria-hidden="true"></div>`
   - `render_block` filter: for `acf/section_*` and `acf/section-*` blocks, inject grain as the **first child** of the root `.section` div (skip if already present)
   - Boot with `SectionGrain::getInstance()` in `Theme::initComponents()` (`functions.php`)

5. **Hero** — in `hm-main-hero.php`, call `SectionGrain::render()` as the first child inside `.hm-main-hero` (hero is not an ACF section block).

6. **Verify**
   - Solid, bands, and no-background sections all show grit on the shell only
   - `section-usps` included (no `section-background` markup)
   - Logos / images / titles / buttons readable and above the film
   - Vignet fades still wash the edges (grain underneath)
   - Hard-refresh after webpack rebuild of `technosapiens-frontend`

## Out of scope

- Per-section ACF on/off
- Animated / drifting grain
- Grain on nested card backgrounds (`section-cards`, `section-content-alt`, etc.)
- Fixed viewport film on `body` / `html`
- Displacement-map filter on gradient bands (article’s “better” gradient technique)

## Prior art (reverted)

A first pass matching this plan was implemented then removed on request. Restore from the same shape; do not invent a body-level or pseudo-only approach without revisiting vignets.
