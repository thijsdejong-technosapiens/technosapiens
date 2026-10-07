/**
 * Section Process Cards
 * Stagger fade-in-up once when the card grid enters the viewport.
 */
class SectionProcessCards {
    SECTION_SELECTOR = '.section-process-cards'
    GRID_SELECTOR = '.section-process-cards__grid'
    CARD_SELECTOR = '.section-process-cards__card'
    READY_CLASS = 'is-ready'

    DURATION = 0.5
    STAGGER = 0.12
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
            $sections.forEach(($section) => this.markReady($section))
            return
        }

        this.loadGsap()
            .then(() => {
                this.initObserver($sections)
            })
            .catch((error) => {
                console.log('Error importing gsap for section-process-cards:', error)
                $sections.forEach(($section) => this.markReady($section))
            })
    }

    /**
     * @returns {Promise<void>}
     */
    loadGsap() {
        return import(/* webpackChunkName: "section-process-cards-gsap" */ 'gsap').then((gsapModule) => {
            this.state.gsap = gsapModule.gsap
        })
    }

    /**
     * @param {Element} $section
     */
    markReady($section) {
        $section.classList.add(this.READY_CLASS)
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
                this.animateSection(entry.target)
            })
        }, {
            root: null,
            rootMargin: '0px',
            threshold: this.OBSERVER_THRESHOLD,
        })

        $sections.forEach(($section) => {
            const $grid = $section.querySelector(this.GRID_SELECTOR)

            if (!$grid) {
                this.markReady($section)
                return
            }

            this.state.observer.observe($grid)
        })
    }

    /**
     * @param {Element} $grid
     */
    animateSection($grid) {
        const $section = $grid.closest(this.SECTION_SELECTOR)
        const $cards = Array.from($grid.querySelectorAll(this.CARD_SELECTOR))

        if (!$section || $cards.length === 0) {
            if ($section) {
                this.markReady($section)
            }
            return
        }

        const {gsap} = this.state

        gsap.set($cards, {
            opacity: 0,
            y: this.Y_OFFSET,
        })

        this.markReady($section)

        gsap.to($cards, {
            opacity: 1,
            y: 0,
            duration: this.DURATION,
            stagger: this.STAGGER,
            ease: 'power2.out',
        })
    }
}

new SectionProcessCards()
