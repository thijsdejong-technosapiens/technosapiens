import {gsap} from 'gsap'

/**
 * class GradientBands
 * Stagger-fades gradient band children once when their container enters the viewport.
 * ScrollTrigger is intentionally not imported; add it later only if scrub/pin is required.
 */
class GradientBands {

    CONTAINER_CLASS = 'gradient-bands'
    BAND_CLASS = 'gradient-band'
    STAGGER_MODES = ['start', 'end', 'center']
    DURATION = 0.4
    STAGGER = 0.06

    /**
     * @param {NodeListOf<Element>|Element[]} $containers
     */
    constructor($containers) {
        const containers = Array.from($containers || [])
        if (containers.length === 0) return

        this.state = {
            $containers: containers,
            observer: null,
        }

        this.initObserver()
    }

    /**
     * Observe each container; IO fires immediately for already-visible elements.
     */
    initObserver() {
        this.state.observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return
                this.state.observer.unobserve(entry.target)
                this.animateContainer(entry.target)
            })
        }, {
            root: null,
            rootMargin: '0px',
            threshold: 0,
        })

        this.state.$containers.forEach(($container) => {
            this.state.observer.observe($container)
        })
    }

    /**
     * @param {Element} $container
     */
    animateContainer($container) {
        const $bands = Array.from($container.querySelectorAll(`.${this.BAND_CLASS}`))
        if ($bands.length === 0) return

        const staggerMode = this.getStaggerMode($container)
        const targets = this.orderBands($bands, staggerMode)

        gsap.to(targets, {
            opacity: 1,
            duration: this.DURATION,
            stagger: this.STAGGER,
            ease: 'power1.out',
        })
    }

    /**
     * @param {Element} $container
     * @returns {string}
     */
    getStaggerMode($container) {
        const mode = $container.getAttribute('data-stagger-mode') || 'start'
        return this.STAGGER_MODES.includes(mode) ? mode : 'start'
    }

    /**
     * @param {Element[]} $bands
     * @param {string} staggerMode
     * @returns {Element[]}
     */
    orderBands($bands, staggerMode) {
        if (staggerMode === 'end') return [...$bands].reverse()

        if (staggerMode === 'center') {
            const midpoint = ($bands.length - 1) / 2
            return $bands
                .map(($band, index) => ({$band, index, distance: Math.abs(index - midpoint)}))
                .sort((a, b) => a.distance - b.distance || a.index - b.index)
                .map(({$band}) => $band)
        }

        return $bands
    }
}

export default GradientBands
