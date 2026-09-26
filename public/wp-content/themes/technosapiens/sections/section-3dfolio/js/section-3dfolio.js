/**
 * Section 3D Folio
 * Lazy-loads the WebGL hex prism scene once a section approaches the viewport.
 */
class Section3dfolio {
    SECTION_SELECTOR = '.section-3dfolio'
    CASES_SELECTOR = '[data-section-3dfolio-cases]'
    PRELOAD_ROOT_MARGIN = '200px 0px'

    constructor() {
        this.state = {
            prefersReducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
            supportsHover: window.matchMedia('(hover: hover)').matches && !('ontouchstart' in window),
            scenePromise: null
        }

        this.init()
    }

    /**
     * Observe every section with cases and boot its scene when near the viewport
     */
    init() {
        const sections = Array.from(document.querySelectorAll(this.SECTION_SELECTOR))
            .map(($section) => ({$section, cases: this.parseCases($section)}))
            .filter(({cases}) => cases.length > 0)

        if (sections.length === 0) {
            return
        }

        if (!('IntersectionObserver' in window)) {
            sections.forEach((section) => this.bootSection(section))
            return
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return
                }

                observer.unobserve(entry.target)
                const section = sections.find(({$section}) => $section === entry.target)

                if (section) {
                    this.bootSection(section)
                }
            })
        }, {rootMargin: this.PRELOAD_ROOT_MARGIN})

        sections.forEach(({$section}) => observer.observe($section))
    }

    /**
     * @param {{$section: Element, cases: Array<Object>}} section
     */
    bootSection({$section, cases}) {
        this.loadScene()
            .then((Section3dfolioScene) => {
                new Section3dfolioScene($section, cases, {
                    prefersReducedMotion: this.state.prefersReducedMotion,
                    supportsHover: this.state.supportsHover
                })
            })
            .catch((error) => console.log('Error importing section-3dfolio scene:', error))
    }

    /**
     * Import the scene chunk once, shared by all sections on the page
     * @returns {Promise<Function>}
     */
    loadScene() {
        if (!this.state.scenePromise) {
            this.state.scenePromise = import(/* webpackChunkName: "section-3dfolio-scene" */ './lib/Section3dfolioScene')
                .then(({default: Section3dfolioScene}) => Section3dfolioScene)
        }

        return this.state.scenePromise
    }

    /**
     * @param {Element} $section
     * @returns {Array<Object>}
     */
    parseCases($section) {
        const $data = $section.querySelector(this.CASES_SELECTOR)

        if (!$data) {
            return []
        }

        try {
            const cases = JSON.parse($data.textContent)
            return Array.isArray(cases) ? cases : []
        } catch (error) {
            console.log('Error parsing section-3dfolio cases:', error)
            return []
        }
    }
}

new Section3dfolio()
