/**
 * Section Techstack
 * Stagger fade-in-up once when each logo grid enters the viewport.
 */
class SectionTechstack {
    SECTION_SELECTOR = '.section-techstack'
    GRID_SELECTOR = '.section-techstack__grid'
    ITEM_SELECTOR = '.section-techstack__item'
    READY_CLASS = 'is-ready'

    DURATION = 0.5
    STAGGER = 0.12
    STAGGER_MIN = 0.02
    STAGGER_REF_COUNT = 4
    STAGGER_EXPONENT = 1.5
    Y_OFFSET = 40
    OBSERVER_THRESHOLD = 0.15

    constructor() {
        this.state = {
            prefersReducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
            gsap: null,
            observer: null,
        }

        this.init()
    }

    /**
     * Reveal immediately when motion is reduced; otherwise load GSAP and observe.
     */
    init() {
        const $sections = Array.from(document.querySelectorAll(this.SECTION_SELECTOR))

        if ($sections.length === 0) {
            return
        }

        if (this.state.prefersReducedMotion) {
            $sections.forEach(($section) => this.markGridsReady($section))
            return
        }

        this.loadGsap()
            .then(() => {
                this.initObserver($sections)
            })
            .catch((error) => {
                console.log('Error importing gsap for section-techstack:', error)
                $sections.forEach(($section) => this.markGridsReady($section))
            })
    }

    /**
     * @returns {Promise<void>}
     */
    loadGsap() {
        return import(/* webpackChunkName: "section-techstack-gsap" */ 'gsap').then((gsapModule) => {
            this.state.gsap = gsapModule.gsap
        })
    }

    /**
     * @param {Element} $target
     */
    markReady($target) {
        $target.classList.add(this.READY_CLASS)
    }

    /**
     * @param {Element} $section
     */
    markGridsReady($section) {
        const $grids = Array.from($section.querySelectorAll(this.GRID_SELECTOR))

        if ($grids.length === 0) {
            this.markReady($section)
            return
        }

        $grids.forEach(($grid) => this.markReady($grid))
    }

    /**
     * @param {Element[]} $sections
     */
    initObserver($sections) {
        this.state.observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return
                }

                this.state.observer.unobserve(entry.target)
                this.animateGrid(entry.target)
            })
        }, {
            root: null,
            rootMargin: '0px',
            threshold: this.OBSERVER_THRESHOLD,
        })

        $sections.forEach(($section) => {
            const $grids = Array.from($section.querySelectorAll(this.GRID_SELECTOR))

            if ($grids.length === 0) {
                this.markReady($section)
                return
            }

            $grids.forEach(($grid) => {
                this.state.observer.observe($grid)
            })
        })
    }

    /**
     * Shorter delay between items as the logo count grows.
     *
     * @param {number} count
     * @returns {number}
     */
    getStagger(count) {
        if (count <= 1) {
            return 0
        }

        const scaled = this.STAGGER * Math.pow(this.STAGGER_REF_COUNT / count, this.STAGGER_EXPONENT)
        return Math.max(this.STAGGER_MIN, scaled)
    }

    /**
     * @param {Element} $grid
     */
    animateGrid($grid) {
        const $items = Array.from($grid.querySelectorAll(this.ITEM_SELECTOR))

        if ($items.length === 0) {
            this.markReady($grid)
            return
        }

        const {gsap} = this.state

        gsap.set($items, {
            opacity: 0,
            y: this.Y_OFFSET,
        })

        this.markReady($grid)

        gsap.to($items, {
            opacity: 1,
            y: 0,
            duration: this.DURATION,
            stagger: this.getStagger($items.length),
            ease: 'power2.out',
        })
    }
}

new SectionTechstack()
