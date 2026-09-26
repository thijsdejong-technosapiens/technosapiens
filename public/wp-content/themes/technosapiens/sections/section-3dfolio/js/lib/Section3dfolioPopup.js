/**
 * Section 3D Folio popup
 * Face-anchored dialog: content, screen position, focus trap and close handling.
 */
export default class Section3dfolioPopup {
    DIALOG_SELECTOR = '[data-section-3dfolio-popup-dialog]'
    CLOSE_SELECTOR = '[data-section-3dfolio-popup-close]'
    TITLE_SELECTOR = '[data-section-3dfolio-popup-title]'
    BODY_SELECTOR = '[data-section-3dfolio-popup-body]'
    FOCUSABLE_SELECTOR = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
    EDGE_OFFSET_PX = 16

    /**
     * @param {HTMLElement} $root
     * @param {Function} onClose
     */
    constructor($root = null, onClose = () => {}) {
        if (!($root instanceof HTMLElement)) {
            throw new Error('Missing required parameter `$root`')
        }

        this.$root = $root
        this.$dialog = $root.querySelector(this.DIALOG_SELECTOR)
        this.$close = $root.querySelector(this.CLOSE_SELECTOR)
        this.$title = $root.querySelector(this.TITLE_SELECTOR)
        this.$body = $root.querySelector(this.BODY_SELECTOR)
        this.onClose = onClose

        if (!(this.$dialog instanceof HTMLElement) || !this.$title || !this.$body) {
            throw new Error('Missing popup markup in `$root`')
        }

        this.state = {
            isOpen: false,
            faceIndex: null,
            $returnFocus: null,
            rootWidth: 0,
            rootHeight: 0,
            width: 0,
            height: 0
        }

        this.bindEvents()
    }

    get isOpen() {
        return this.state.isOpen
    }

    get faceIndex() {
        return this.state.faceIndex
    }

    bindEvents() {
        this.$dialog.addEventListener('keydown', (event) => this.handleKeyDown(event))

        if (this.$close) {
            this.$close.addEventListener('click', () => this.close())
        }
    }

    /**
     * Fill and show the dialog; switching faces keeps the original return-focus target
     * @param {{title: string, body: string}} caseData
     * @param {number} faceIndex
     */
    open(caseData, faceIndex) {
        if (!this.state.isOpen && document.activeElement instanceof HTMLElement) {
            this.state.$returnFocus = document.activeElement
        }

        this.$title.textContent = caseData.title || ''
        this.$title.hidden = !caseData.title
        this.$body.innerHTML = caseData.body || ''
        this.$root.hidden = false

        this.state.isOpen = true
        this.state.faceIndex = faceIndex

        this.updateSize()
        this.$dialog.focus({preventScroll: true})
    }

    close() {
        if (!this.state.isOpen) {
            return
        }

        const $returnFocus = this.state.$returnFocus

        this.$root.hidden = true
        this.state.isOpen = false
        this.state.faceIndex = null
        this.state.$returnFocus = null

        this.onClose()

        if ($returnFocus && $returnFocus.isConnected) {
            $returnFocus.focus({preventScroll: true})
        }
    }

    /**
     * Cache root and dialog size so per-frame positioning does not force layout
     */
    updateSize() {
        if (!this.state.isOpen) {
            return
        }

        this.state.rootWidth = this.$root.clientWidth
        this.state.rootHeight = this.$root.clientHeight
        this.state.width = this.$dialog.offsetWidth
        this.state.height = this.$dialog.offsetHeight
    }

    /**
     * Center the dialog on a point in root pixels, clamped inside the stage
     * @param {number} x
     * @param {number} y
     */
    setPosition(x, y) {
        const {rootWidth, rootHeight, width, height} = this.state
        const left = this.clamp(x - width / 2, this.EDGE_OFFSET_PX, rootWidth - width - this.EDGE_OFFSET_PX)
        const top = this.clamp(y - height / 2, this.EDGE_OFFSET_PX, rootHeight - height - this.EDGE_OFFSET_PX)

        this.$dialog.style.transform = `translate3d(${left}px, ${top}px, 0)`
    }

    /**
     * @param {number} value
     * @param {number} min
     * @param {number} max
     * @returns {number}
     */
    clamp(value, min, max) {
        return Math.min(Math.max(value, min), Math.max(min, max))
    }

    /**
     * @param {KeyboardEvent} event
     */
    handleKeyDown(event) {
        if (event.key === 'Escape') {
            event.preventDefault()
            this.close()
            return
        }

        if (event.key === 'Tab') {
            this.trapFocus(event)
        }
    }

    /**
     * @param {KeyboardEvent} event
     */
    trapFocus(event) {
        const $focusables = Array.from(this.$dialog.querySelectorAll(this.FOCUSABLE_SELECTOR))
            .filter(($element) => $element instanceof HTMLElement && !$element.hidden)

        if ($focusables.length === 0) {
            event.preventDefault()
            this.$dialog.focus({preventScroll: true})
            return
        }

        const $first = $focusables[0]
        const $last = $focusables[$focusables.length - 1]
        const $active = document.activeElement

        if (event.shiftKey && ($active === $first || $active === this.$dialog)) {
            event.preventDefault()
            $last.focus()
        } else if (!event.shiftKey && $active === $last) {
            event.preventDefault()
            $first.focus()
        }
    }
}
