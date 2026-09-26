/**
 * Section Cards tilt
 * Vanilla port of the CodePen hover parallax card (rotate + background shift).
 */
class SectionCardsTilt {
    CARD_WRAP_SELECTOR = '.section-cards__card-wrap'
    CARD_SELECTOR = '.section-cards__card'
    CARD_BG_SELECTOR = '.section-cards__card-background'
    CARD_LINE_WHITE_SELECTOR = '.section-cards__card-line--white'
    CARD_LINE_PINK_SELECTOR = '.section-cards__card-line--pink'

    ROTATION_FACTOR = 15
    PARALLAX_FACTOR = 40
    LINE_WHITE_PARALLAX = 22
    LINE_PINK_PARALLAX = 10
    LINE_WHITE_Z_REM = 2.75
    LINE_PINK_Z_REM = 1.25
    MOUSE_LEAVE_DELAY_MS = 500

    constructor() {
        this.state = {
            prefersReducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
            supportsHover: window.matchMedia('(hover: hover)').matches && !('ontouchstart' in window),
            cards: []
        }

        this.init()
    }

    /**
     * Bind tilt behavior to every card wrap
     */
    init() {
        if (this.state.prefersReducedMotion || !this.state.supportsHover) {
            return
        }

        Array.from(document.querySelectorAll(this.CARD_WRAP_SELECTOR)).forEach($wrap => {
            this.initCard($wrap)
        })
    }

    /**
     * Initialize one card wrap
     * @param {Element} $wrap
     */
    initCard($wrap) {
        const $card = $wrap.querySelector(this.CARD_SELECTOR)
        const $cardBg = $wrap.querySelector(this.CARD_BG_SELECTOR)
        const $lineWhite = $wrap.querySelector(this.CARD_LINE_WHITE_SELECTOR)
        const $linePink = $wrap.querySelector(this.CARD_LINE_PINK_SELECTOR)

        if (!($wrap instanceof HTMLElement) || !($card instanceof HTMLElement)) {
            return
        }

        const cardState = {
            $wrap,
            $card,
            $cardBg: $cardBg instanceof HTMLElement ? $cardBg : null,
            $lineWhite: $lineWhite instanceof HTMLElement ? $lineWhite : null,
            $linePink: $linePink instanceof HTMLElement ? $linePink : null,
            width: 0,
            height: 0,
            mouseX: 0,
            mouseY: 0,
            mouseLeaveDelay: null
        }

        cardState.boundHandleMouseMove = (event) => this.handleMouseMove(cardState, event)
        cardState.boundHandleMouseEnter = () => this.handleMouseEnter(cardState)
        cardState.boundHandleMouseLeave = () => this.handleMouseLeave(cardState)

        this.updateDimensions(cardState)

        $wrap.addEventListener('mousemove', cardState.boundHandleMouseMove, {passive: true})
        $wrap.addEventListener('mouseenter', cardState.boundHandleMouseEnter)
        $wrap.addEventListener('mouseleave', cardState.boundHandleMouseLeave)

        this.state.cards.push(cardState)
    }

    /**
     * Cache wrap size for mouse math
     * @param {Object} cardState
     */
    updateDimensions(cardState) {
        cardState.width = cardState.$wrap.offsetWidth
        cardState.height = cardState.$wrap.offsetHeight
    }

    /**
     * Track pointer relative to card center
     * @param {Object} cardState
     * @param {MouseEvent} event
     */
    handleMouseMove(cardState, event) {
        if (!cardState.width || !cardState.height) {
            this.updateDimensions(cardState)
        }

        const rect = cardState.$wrap.getBoundingClientRect()
        cardState.mouseX = event.clientX - rect.left - cardState.width / 2
        cardState.mouseY = event.clientY - rect.top - cardState.height / 2
        this.applyTransforms(cardState)
    }

    /**
     * Cancel leave reset while hovering again
     * @param {Object} cardState
     */
    handleMouseEnter(cardState) {
        clearTimeout(cardState.mouseLeaveDelay)
        this.updateDimensions(cardState)
    }

    /**
     * Ease transforms back to rest after leave
     * @param {Object} cardState
     */
    handleMouseLeave(cardState) {
        cardState.mouseLeaveDelay = setTimeout(() => {
            cardState.mouseX = 0
            cardState.mouseY = 0
            this.applyTransforms(cardState)
        }, this.MOUSE_LEAVE_DELAY_MS)
    }

    /**
     * Apply card rotation, background parallax, and line depth parallax
     * @param {Object} cardState
     */
    applyTransforms(cardState) {
        const mousePX = cardState.mouseX / cardState.width
        const mousePY = cardState.mouseY / cardState.height
        const rotateY = mousePX * this.ROTATION_FACTOR
        const rotateX = mousePY * -this.ROTATION_FACTOR

        cardState.$card.style.transform = `rotateY(${rotateY}deg) rotateX(${rotateX}deg)`

        if (cardState.$cardBg) {
            const translateX = mousePX * -this.PARALLAX_FACTOR
            const translateY = mousePY * -this.PARALLAX_FACTOR
            cardState.$cardBg.style.transform = `translate(${translateX}px, ${translateY}px)`
        }

        this.applyLineTransform(cardState.$lineWhite, mousePX, mousePY, this.LINE_WHITE_PARALLAX, this.LINE_WHITE_Z_REM)
        this.applyLineTransform(cardState.$linePink, mousePX, mousePY, this.LINE_PINK_PARALLAX, this.LINE_PINK_Z_REM)
    }

    /**
     * Shift a decorative line on a separate depth plane
     * @param {HTMLElement|null} $line
     * @param {number} mousePX
     * @param {number} mousePY
     * @param {number} parallaxFactor
     * @param {number} translateZRem
     */
    applyLineTransform($line, mousePX, mousePY, parallaxFactor, translateZRem) {
        if (!$line) {
            return
        }

        const translateX = mousePX * -parallaxFactor
        const translateY = mousePY * -parallaxFactor
        $line.style.transform = `translate3d(${translateX}px, ${translateY}px, ${translateZRem}rem)`
    }
}

/**
 * Section Cards scroll motion
 * ScrollTrigger Y-ladder scrub when cards share one row.
 */
class SectionCardsMotion {
    SECTION_SELECTOR = '.section-cards'
    GRID_SELECTOR = '.section-cards__grid'
    CARD_WRAP_SELECTOR = '.section-cards__card-wrap'
    READY_CLASS = 'is-ready'

    Y_STEP_REM = 3
    RESIZE_DEBOUNCE_MS = 150
    SCRUB_START = 'top 80%'
    SCRUB_END = 'bottom 80%'

    constructor() {
        this.state = {
            prefersReducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
            gsap: null,
            ScrollTrigger: null,
            instances: [],
            resizeTimer: null,
            boundHandleResize: null
        }

        this.init()
    }

    /**
     * Load GSAP when motion is allowed, otherwise reveal flat cards
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
                $sections.forEach(($section) => this.setupSection($section))
                this.bindResize()
            })
            .catch((error) => {
                console.log('Error importing gsap for section-cards:', error)
                $sections.forEach(($section) => this.markReady($section))
            })
    }

    /**
     * @returns {Promise<void>}
     */
    loadGsap() {
        return Promise.all([
            import(/* webpackChunkName: "section-cards-gsap" */ 'gsap'),
            import(/* webpackChunkName: "section-cards-gsap" */ 'gsap/ScrollTrigger')
        ]).then(([gsapModule, scrollTriggerModule]) => {
            this.state.gsap = gsapModule.gsap
            this.state.ScrollTrigger = scrollTriggerModule.ScrollTrigger
            this.state.gsap.registerPlugin(this.state.ScrollTrigger)
        })
    }

    /**
     * @param {Element} $section
     */
    markReady($section) {
        $section.classList.add(this.READY_CLASS)
    }

    /**
     * @param {Element} $section
     */
    setupSection($section) {
        const $grid = $section.querySelector(this.GRID_SELECTOR)

        if (!($grid instanceof HTMLElement)) {
            this.markReady($section)
            return
        }

        const $wraps = Array.from($grid.querySelectorAll(this.CARD_WRAP_SELECTOR))
            .filter(($wrap) => $wrap instanceof HTMLElement)

        if ($wraps.length === 0) {
            this.markReady($section)
            return
        }

        const instance = {
            $section,
            $grid,
            $wraps,
            scrubTrigger: null,
            usesLadder: false
        }

        this.state.instances.push(instance)
        this.applyLadderState(instance)
    }

    /**
     * Set ladder Y positions and attach scrub when cards share one row
     * @param {Object} instance
     */
    applyLadderState(instance) {
        const {gsap, ScrollTrigger} = this.state
        const cardCount = instance.$wraps.length
        const columnCount = this.getColumnCount(instance.$grid)
        const usesLadder = this.isSingleRow(cardCount, columnCount) && cardCount >= 2
        const progress = usesLadder && ScrollTrigger
            ? this.getScrubProgress(instance.$section)
            : 0

        instance.usesLadder = usesLadder
        this.setLadderOffset(instance.$grid, usesLadder ? cardCount : 0)

        instance.$wraps.forEach(($wrap, index) => {
            const ladderY = usesLadder ? this.getLadderYValue(index, cardCount, progress) : 0

            gsap.set($wrap, {
                y: this.formatRem(ladderY)
            })
        })

        this.markReady(instance.$section)

        if (usesLadder) {
            this.createScrub(instance)
        }
    }

    /**
     * @param {Object} instance
     */
    createScrub(instance) {
        const {gsap} = this.state
        const cardCount = instance.$wraps.length

        if (instance.scrubTrigger) {
            instance.scrubTrigger.kill()
            instance.scrubTrigger = null
        }

        const timeline = gsap.timeline({
            scrollTrigger: {
                trigger: instance.$section,
                start: this.SCRUB_START,
                end: this.SCRUB_END,
                scrub: true
            }
        })

        instance.$wraps.forEach(($wrap, index) => {
            timeline.fromTo($wrap, {
                y: this.formatRem(this.getLadderYValue(index, cardCount, 0))
            }, {
                y: this.formatRem(this.getLadderYValue(index, cardCount, 1)),
                ease: 'none',
                duration: 1
            }, 0)
        })

        instance.scrubTrigger = timeline.scrollTrigger
    }

    /**
     * Count columns from the first flex row of card wraps
     * @param {HTMLElement} $grid
     * @returns {number}
     */
    getColumnCount($grid) {
        const $wraps = Array.from($grid.querySelectorAll(this.CARD_WRAP_SELECTOR))
            .filter(($wrap) => $wrap instanceof HTMLElement)

        if ($wraps.length === 0) {
            return 1
        }

        const firstTop = $wraps[0].offsetTop

        return $wraps.filter(($wrap) => $wrap.offsetTop === firstTop).length
    }

    /**
     * @param {number} cardCount
     * @param {number} columnCount
     * @returns {boolean}
     */
    isSingleRow(cardCount, columnCount) {
        return cardCount > 0 && columnCount > 0 && cardCount <= columnCount
    }

    /**
     * progress 0 = right highest, 1 = left highest
     * @param {number} index
     * @param {number} count
     * @param {number} progress
     * @returns {number}
     */
    getLadderYValue(index, count, progress) {
        const startY = -index * this.Y_STEP_REM
        const endY = -(count - 1 - index) * this.Y_STEP_REM

        return startY + (endY - startY) * progress
    }

    /**
     * @param {number} value
     * @returns {string}
     */
    formatRem(value) {
        return `${value}rem`
    }

    /**
     * @param {HTMLElement} $grid
     * @param {number} cardCount
     */
    setLadderOffset($grid, cardCount) {
        const offsetRem = cardCount > 1 ? (cardCount - 1) * this.Y_STEP_REM : 0
        $grid.style.setProperty('--section-cards-ladder-offset', `${offsetRem}rem`)
    }

    /**
     * Rebuild ladder/scrub when columns change on resize
     */
    bindResize() {
        this.state.boundHandleResize = () => {
            clearTimeout(this.state.resizeTimer)
            this.state.resizeTimer = setTimeout(() => this.handleResize(), this.RESIZE_DEBOUNCE_MS)
        }

        window.addEventListener('resize', this.state.boundHandleResize, {passive: true})
    }

    /**
     * Kill motion and re-apply for current layout
     */
    handleResize() {
        const {gsap, ScrollTrigger} = this.state

        this.state.instances.forEach((instance) => {
            if (instance.scrubTrigger) {
                instance.scrubTrigger.kill()
                instance.scrubTrigger = null
            }

            gsap.killTweensOf(instance.$wraps)
            gsap.set(instance.$wraps, {clearProps: 'transform'})

            this.applyLadderState(instance)
        })

        if (ScrollTrigger) {
            ScrollTrigger.refresh()
        }
    }

    /**
     * @param {Element} $section
     * @returns {number}
     */
    getScrubProgress($section) {
        const {ScrollTrigger} = this.state
        const probe = ScrollTrigger.create({
            trigger: $section,
            start: this.SCRUB_START,
            end: this.SCRUB_END
        })
        const progress = probe.progress
        probe.kill()

        return progress
    }
}

new SectionCardsTilt()
new SectionCardsMotion()
