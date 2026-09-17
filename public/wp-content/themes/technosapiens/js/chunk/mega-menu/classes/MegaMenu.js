/**
 * Class MegaMenu
 * Handles open/close behaviour and accessibility for mega menu navigation panels.
 */
class MegaMenu {

    ACTIVE_CLASS = 'mega-menu-navigation--active'
    CLOSE_BUTTON_CLASS = 'mega-menu-navigation__close-button'

    /**
     * MegaMenu constructor
     * @param {Element|null} megaMenuElement
     */
    constructor(megaMenuElement = null) {
        if (!(megaMenuElement && megaMenuElement instanceof Element)) {
            throw new Error('Missing or invalid `megaMenuElement` given in MegaMenu class constructor.')
        }

        this.state = {
            $megaMenuElement: megaMenuElement,
            $controlButton: null,
            $closeButton: megaMenuElement.querySelector(`.${this.CLOSE_BUTTON_CLASS}`),
            $overlay: document.getElementById(`overlay-${megaMenuElement.id}`),
            megaMenuId: megaMenuElement.id,
            presetName: megaMenuElement.dataset.mmPreset || null,
            mutationObserver: null,
            openEvent: new CustomEvent('ts-mega-menu:open', {
                detail: {
                    megaMenuId: megaMenuElement.id,
                    preset: megaMenuElement.dataset.mmPreset || null,
                    element: megaMenuElement
                }
            }),
            closeEvent: new CustomEvent('ts-mega-menu:close', {
                detail: {
                    megaMenuId: megaMenuElement.id,
                    preset: megaMenuElement.dataset.mmPreset || null,
                    element: megaMenuElement
                }
            })
        }

        this.initControlButton()
        this.addCloseListeners()
        this.addFocusOutListener()
        document.addEventListener('keydown', this.handleEscapeKeyDown.bind(this))
    }

    /**
     * Find and bind the control button for this mega menu
     */
    initControlButton() {
        const megaMenuId = this.state.$megaMenuElement.id
        this.state.$controlButton = document.querySelector(`[aria-controls="${megaMenuId}"]`)

        if (this.state.$controlButton) {
            this.addControlButtonListeners()
            this.addMutationObserver()
            return
        }

        console.warn(`No control button found for mega menu with id: ${megaMenuId}`)
        this.state.$controlButton = document.querySelector(`[aria-controls="${megaMenuId}"]`)

        if (this.state.$controlButton) {
            this.addControlButtonListeners()
            this.addMutationObserver()
        }
    }

    /**
     * Add control button event listeners
     */
    addControlButtonListeners() {
        this.state.$controlButton.addEventListener('click', this.handleControlButtonClick.bind(this))
        this.state.$controlButton.addEventListener('keydown', this.handleControlButtonKeyDown.bind(this))
    }

    /**
     * Add close button and overlay listeners
     */
    addCloseListeners() {
        if (this.state.$closeButton) {
            this.state.$closeButton.addEventListener('click', this.handleCloseClick.bind(this))
        }

        if (this.state.$overlay) {
            this.state.$overlay.addEventListener('click', this.handleCloseClick.bind(this))
        }
    }

    /**
     * Close mega menu when focus leaves the panel
     * @param {FocusEvent} event
     */
    addFocusOutListener() {
        this.state.$megaMenuElement.addEventListener('focusout', (event) => {
            if (event.relatedTarget && !this.state.$megaMenuElement.contains(event.relatedTarget)) {
                this.closeMegaMenu(event)
            }
        })
    }

    /**
     * Handle control button click
     * @param {Event} event
     */
    handleControlButtonClick(event) {
        event.preventDefault()
        event.stopPropagation()
        this.toggleMegaMenu()
    }

    /**
     * Handle control button keyboard activation
     * @param {KeyboardEvent} event
     */
    handleControlButtonKeyDown(event) {
        if (event.key !== 'Enter' && event.key !== ' ') return

        event.preventDefault()
        event.stopPropagation()
        this.toggleMegaMenu(true)
    }

    /**
     * Handle close button or overlay click
     * @param {Event} event
     */
    handleCloseClick(event) {
        event.preventDefault()
        this.closeMegaMenu(event)
    }

    /**
     * Handle escape key when focus is inside the mega menu
     * @param {KeyboardEvent} event
     */
    handleEscapeKeyDown(event) {
        const activeElement = document.activeElement

        if (event.key === 'Escape' && this.state.$megaMenuElement.contains(activeElement)) {
            this.closeMegaMenu(event)
        }
    }

    /**
     * Toggle mega menu open state
     * @param {boolean} focusCloseButton
     */
    toggleMegaMenu(focusCloseButton = false) {
        if (this.state.$controlButton?.getAttribute('aria-expanded') === 'true') {
            this.closeMegaMenu()
            return
        }

        this.openMegaMenu(focusCloseButton)
    }

    /**
     * Open mega menu
     * @param {boolean} focusCloseButton
     */
    openMegaMenu(focusCloseButton = false) {
        if (this.state.$controlButton) {
            this.state.$controlButton.setAttribute('aria-expanded', 'true')
        }

        this.state.$megaMenuElement.classList.add(this.ACTIVE_CLASS)

        if (focusCloseButton && this.state.$closeButton) {
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    if (this.state.$closeButton) {
                        this.state.$closeButton.focus()
                    }
                })
            })
        }
    }

    /**
     * Close mega menu
     * @param {Event|null} event
     */
    closeMegaMenu(event = null) {
        if (this.state.$controlButton) {
            this.state.$controlButton.setAttribute('aria-expanded', 'false')
        }

        this.state.$megaMenuElement.classList.remove(this.ACTIVE_CLASS)

        if (!event || (event.type !== 'click' && event.type !== 'keydown')) return

        if (this.state.$controlButton) {
            this.state.$controlButton.focus()
        }
    }

    /**
     * Dispatch open event for preset scripts
     */
    dispatchMegaMenuOpenEvent() {
        window.dispatchEvent(this.state.openEvent)
    }

    /**
     * Dispatch close event for preset scripts
     */
    dispatchMegaMenuCloseEvent() {
        window.dispatchEvent(this.state.closeEvent)
    }

    /**
     * Observe aria-expanded changes on the control button
     */
    addMutationObserver() {
        if (!this.state.$controlButton) return

        let previousExpandedState = this.state.$controlButton.getAttribute('aria-expanded')

        this.state.mutationObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type !== 'attributes' || mutation.attributeName !== 'aria-expanded') return

                const expandedState = this.state.$controlButton.getAttribute('aria-expanded')

                if (expandedState === previousExpandedState) return

                previousExpandedState = expandedState

                if (expandedState === 'true') {
                    this.dispatchMegaMenuOpenEvent()
                    return
                }

                this.dispatchMegaMenuCloseEvent()
            })
        })

        this.state.mutationObserver.observe(this.state.$controlButton, {
            attributes: true,
            attributeFilter: ['aria-expanded']
        })
    }
}

export default MegaMenu
