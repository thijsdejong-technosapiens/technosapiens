/**
 * class MobileNavigation
 * Reveals the bottom mobile nav when scrolling down, hides it when scrolling up.
 * Stays visible near the document bottom and while a child has keyboard focus.
 */
class MobileNavigation {

    HIDDEN_CLASS = 'mobile-navigation--hidden'
    SCROLL_DELTA = 8
    EDGE_THRESHOLD = 48

    /**
     * @param {HTMLElement} $element
     */
    constructor($element) {
        if (!$element) return

        this.state = {
            $element,
            lastScrollY: window.scrollY || 0,
            isHidden: false,
            ticking: false,
        }

        this.bindEvents()
        this.updateVisibility()
    }

    /**
     * Bind scroll and focus listeners
     */
    bindEvents() {
        window.addEventListener('scroll', this.onScroll, {passive: true})
        this.state.$element.addEventListener('focusin', this.onFocusIn)
    }

    /**
     * Schedule a visibility update on the next animation frame
     */
    onScroll = () => {
        if (this.state.ticking) return

        this.state.ticking = true
        window.requestAnimationFrame(() => {
            this.updateVisibility()
            this.state.ticking = false
        })
    }

    /**
     * Keep the bar visible while keyboard focus is inside it
     */
    onFocusIn = () => {
        this.show()
    }

    /**
     * Toggle visibility from scroll direction and page edges
     */
    updateVisibility() {
        const scrollY = window.scrollY || 0
        const delta = scrollY - this.state.lastScrollY
        const maxScroll = Math.max(0, document.documentElement.scrollHeight - window.innerHeight)

        if (scrollY <= this.EDGE_THRESHOLD) {
            this.hide()
        } else if (scrollY >= maxScroll - this.EDGE_THRESHOLD) {
            this.show()
        } else if (delta > this.SCROLL_DELTA) {
            this.show()
        } else if (delta < -this.SCROLL_DELTA) {
            this.hide()
        }

        this.state.lastScrollY = scrollY
    }

    /**
     * Hide the bar
     */
    hide() {
        if (this.state.isHidden) return
        if (this.state.$element.contains(document.activeElement)) return

        this.state.isHidden = true
        this.state.$element.classList.add(this.HIDDEN_CLASS)
    }

    /**
     * Show the bar
     */
    show() {
        if (!this.state.isHidden) return

        this.state.isHidden = false
        this.state.$element.classList.remove(this.HIDDEN_CLASS)
    }
}

export default MobileNavigation
