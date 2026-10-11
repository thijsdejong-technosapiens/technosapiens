import {gsap} from 'gsap'

/**
 * class GradientBands
 * Stagger-fades gradient band children once when their container enters the top half of the viewport.
 * Supports optional start delay and waiting on another bands container in the same section.
 * ScrollTrigger is intentionally not imported; add it later only if scrub/pin is required.
 */
class GradientBands {

    CONTAINER_CLASS = 'gradient-bands'
    BAND_CLASS = 'gradient-band'
    STAGGER_MODES = ['start', 'end', 'center']
    COMPLETE_ATTR = 'data-bands-complete'
    COMPLETE_EVENT = 'gradient-bands:complete'
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
     * Observe each container; IO fires when the container intersects the top half of the viewport.
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
            // Shrink root to top half so bands below mid-viewport do not trigger yet.
            rootMargin: '0px 0px -33% 0px',
            threshold: 0,
        })

        this.state.$containers.forEach(($container) => {
            this.state.observer.observe($container)
        })
    }

    /**
     * Wait for a related bands container when needed, then play.
     * @param {Element} $container
     */
    animateContainer($container) {
        const $waitFor = this.getWaitForTarget($container)

        if ($waitFor) {
            if ($waitFor.getAttribute(this.COMPLETE_ATTR) === 'true') {
                this.playAnimation($container)
                return
            }

            $waitFor.addEventListener(this.COMPLETE_EVENT, () => {
                this.playAnimation($container)
            }, {once: true})
            return
        }

        this.playAnimation($container)
    }

    /**
     * @param {Element} $container
     */
    playAnimation($container) {
        const $bands = Array.from($container.querySelectorAll(`.${this.BAND_CLASS}`))
        if ($bands.length === 0) {
            this.markComplete($container)
            return
        }

        const staggerMode = this.getStaggerMode($container)
        const targets = this.orderBands($bands, staggerMode)
        const delay = this.getDelay($container)

        gsap.to(targets, {
            opacity: 1,
            duration: this.DURATION,
            stagger: this.STAGGER,
            delay,
            ease: 'power1.out',
            onComplete: () => this.markComplete($container),
        })
    }

    /**
     * @param {Element} $container
     */
    markComplete($container) {
        $container.setAttribute(this.COMPLETE_ATTR, 'true')
        $container.dispatchEvent(new CustomEvent(this.COMPLETE_EVENT))
    }

    /**
     * Resolve an optional wait-for selector within the nearest section root.
     * @param {Element} $container
     * @returns {Element|null}
     */
    getWaitForTarget($container) {
        const selector = ($container.getAttribute('data-wait-for') || '').trim()
        if (!selector) return null

        const $scope = $container.closest('[data-bg], .section-cards, .section-content-alt') || document
        const $target = $scope.querySelector(selector)

        if (!$target || $target === $container) return null

        return $target
    }

    /**
     * @param {Element} $container
     * @returns {number}
     */
    getDelay($container) {
        const raw = $container.getAttribute('data-delay')
        const delay = raw === null ? 0 : parseFloat(raw)
        return Number.isFinite(delay) && delay > 0 ? delay : 0
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
