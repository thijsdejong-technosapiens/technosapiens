---
name: Gradient bands GSAP
overview: Animate gradient bands with GSAP core only (dynamically imported), IntersectionObserver for in-view including pageload, PHP-driven layout axis and stagger mode, and CSS-hidden bands until JS — without shipping ScrollTrigger.
todos:
  - id: npm-gsap
    content: Add gsap dependency; named import { gsap } only inside js/lib/GradientBands.js
    status: completed
  - id: php-args
    content: Add axis + staggerMode to GradientBandsComponent and data/BEM on the partial
    status: completed
  - id: scss
    content: Horizontal/vertical layout, opacity 0, reduced-motion visible
    status: completed
  - id: frontend-chunk
    content: initGradientBands in technosapiens-frontend.js with webpackChunkName + IO play-once
    status: completed
isProject: false
---

# Gradient bands stagger (GSAP core + IO)

Shared understanding from grilling: **no ScrollTrigger now**; **GSAP core** loaded only when `.gradient-bands` exists and motion is allowed; **opacity-only** stagger; **play once**; **CSS opacity 0** until JS (no-js stays hidden); **`prefers-reduced-motion`** shows bands and **skips the GSAP import**; layout **axis** + **staggerMode** as PHP args on `data-*`; **existing hero stays a vertical stack**.

## Load path (match current enqueue)

[`FrontendScripts.php`](public/wp-content/themes/technosapiens/inc/app/scripts/FrontendScripts.php) already enqueues the webpack `frontend` entry in the footer. [`technosapiens-frontend.js`](public/wp-content/themes/technosapiens/js/technosapiens-frontend.js) already lazy-loads features with `import(/* webpackChunkName: "…" */)` after a DOM query.

Add `initGradientBands()` in that constructor:

1. If `matchMedia('(prefers-reduced-motion: reduce)').matches`, return (CSS already shows bands).
2. `querySelectorAll('.gradient-bands')` — if none, return (GSAP never downloaded).
3. `import(/* webpackChunkName: "gradient-bands" */ "./lib/GradientBands")` then construct once with the NodeList.

Do **not** add `gsap` as a static import on the frontend entry. Webpack `chunkFilename` will emit `technosapiens-chunk-gradient-bands-….min.js`.

## GSAP + tree-shaking

- Add npm `gsap` (v3 ESM).
- In [`js/lib/GradientBands.js`](public/wp-content/themes/technosapiens/js/lib/GradientBands.js) use `import { gsap } from 'gsap'` only. **Do not** import `ScrollTrigger` or `gsap/all`.
- Tree-shaking cannot strip GSAP core; it **can** keep ScrollTrigger out of the graph. Comment a one-line seam: future `import('gsap/ScrollTrigger')` only if a later feature actually scrubs/pins.
- Register nothing globally on `window`.

## PHP / markup

Extend [`GradientBandsComponent::render()`](public/wp-content/themes/technosapiens/inc/core/components/GradientBandsComponent.php):

- `axis`: `vertical` | `horizontal`. Default **`vertical`** so the current hero does not change layout.
- `staggerMode`: `start` | `end` | `center`. Default **`start`**.
  - vertical + start = top-down, + end = bottom-up, + center = mirrored from middle.
  - horizontal + start = left-right, + end = right-left, + center = mirrored outwards.

Pass through the partial. On the container:

- BEM modifier `gradient-bands--horizontal` or `--vertical` for CSS.
- `data-stagger-mode="start|end|center"` for JS (`esc_attr`).

Hero call site in [`hm-main-hero.php`](public/wp-content/themes/technosapiens/inc/features/header-module/presets/main-hero/partials/hm-main-hero.php) can omit new args (defaults) or set them explicitly to vertical/start — same look.

## CSS

[`component-gradient-bands.scss`](public/wp-content/themes/technosapiens/scss/component/component-gradient-bands.scss):

- Default column (today). `--horizontal`: `flex-direction: row`; children `flex: 1`; use `--gradient-band-height` as the **row height**.
- `.gradient-band { opacity: 0 }` for the animated state.
- `@media (prefers-reduced-motion: reduce)`: `.gradient-band { opacity: 1 }`.
- Opacity-only: no `will-change` unless a later profile shows a paint issue.

## JS behavior

`GradientBands` class:

- One `IntersectionObserver` (threshold `0`, `rootMargin: '0px'`). IO fires for elements **already intersecting** on observe — covers pageload hero without ScrollTrigger.
- On first intersect: unobserve that container, tween its `.gradient-band` children with `gsap.to(..., { opacity: 1, stagger, duration })`.
- Stagger array: `start` = DOM order; `end` = reversed; `center` = indices ordered by distance to midpoint (ties: lower index first).
- Timing defaults (not CMS-configurable unless you ask later): duration `0.4`, stagger `0.06`.
- After tween, leave inline opacity at 1 (or `gsap.set` clearProps if you prefer class-driven; prefer leaving final opacity 1 so a style recalc does not hide them).

## Performance constraints

- No GSAP on pages without `.gradient-bands`.
- No GSAP when reduced-motion.
- No scroll listeners; IO only; play once.
- Opacity only (no layout/transform).
- Accept empty bands until the async chunk runs (agreed FOUC). Footer script + dynamic import is the delay; do not inline GSAP to chase it.

## Out of scope

ScrollTrigger, reverse-on-leave, ACF UI for these args, changing the hero to horizontal.
