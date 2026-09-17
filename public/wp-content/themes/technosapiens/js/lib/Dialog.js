/**
 * class Dialog
 * Handles native HTML dialog open/close behavior for `.ts-dialog` elements.
 */
class Dialog {

    /**
     * Dialog constructor
     * @param {HTMLDialogElement} $dialogElement
     */
    constructor($dialogElement = null) {
        if ($dialogElement === null || !($dialogElement instanceof HTMLDialogElement)) {
            throw new Error('Missing required parameter `$dialogElement` or given parameter was not a valid HTMLDialogElement.')
        }

        this.state = {
            $dialogElement: $dialogElement,
            $controls: Array.from(document.querySelectorAll(`[aria-controls="${$dialogElement.id}"]`)),
            $lastTrigger: null
        }

        this.init()
    }

    /**
     * Initialize dialog behavior
     */
    init() {
        this.state.$controls.forEach($control => this.addControlListeners($control))
        this.state.$dialogElement.addEventListener('click', this.handleDialogClick.bind(this))
        this.state.$dialogElement.addEventListener('close', this.handleDialogClose.bind(this))
    }

    /**
     * Bind open/close listeners to a dialog control
     * @param {Element} $control
     */
    addControlListeners($control) {
        $control.addEventListener('click', event => this.handleControlClick(event, $control))
    }

    /**
     * Toggle dialog open state from a control button
     * @param {Event} event
     * @param {Element} $control
     */
    handleControlClick(event, $control) {
        event.preventDefault()

        if (this.state.$dialogElement.open) {
            this.close()
            return
        }

        this.open($control)
    }

    /**
     * Open the dialog
     * @param {Element|null} $trigger
     */
    open($trigger = null) {
        if (this.state.$dialogElement.open) return

        this.state.$lastTrigger = $trigger
        this.state.$dialogElement.removeAttribute('inert')
        this.state.$dialogElement.showModal()
        this.updateControlState(true)
        this.focusInitialElement()
    }

    /**
     * Close the dialog
     */
    close() {
        if (!this.state.$dialogElement.open) return
        this.state.$dialogElement.close()
    }

    /**
     * Sync aria-expanded and aria-label on all dialog controls
     * @param {boolean} isOpen
     */
    updateControlState(isOpen) {
        this.state.$controls.forEach($control => {
            $control.setAttribute('aria-expanded', isOpen ? 'true' : 'false')

            const openLabel = $control.dataset.labelOpen
            const closeLabel = $control.dataset.labelClose

            if (openLabel && closeLabel) {
                $control.setAttribute('aria-label', isOpen ? closeLabel : openLabel)
            }
        })
    }

    /**
     * Restore dialog inert state and focus after close
     */
    handleDialogClose() {
        this.state.$dialogElement.setAttribute('inert', '')
        this.updateControlState(false)
        this.restoreFocus()
    }

    /**
     * Close dialog when clicking the backdrop
     * @param {MouseEvent} event
     */
    handleDialogClick(event) {
        if (event.target !== this.state.$dialogElement) return
        if (this.state.$dialogElement.dataset.closeOnOverlayClick !== 'true') return
        this.close()
    }

    /**
     * Focus the first autofocus element inside the dialog
     */
    focusInitialElement() {
        const $autofocusElement = this.state.$dialogElement.querySelector('[autofocus]')
        if ($autofocusElement instanceof HTMLElement) {
            $autofocusElement.focus()
        }
    }

    /**
     * Return focus to the element that opened the dialog
     */
    restoreFocus() {
        if (this.state.$lastTrigger instanceof HTMLElement) {
            this.state.$lastTrigger.focus()
            this.state.$lastTrigger = null
        }
    }
}

export default Dialog
