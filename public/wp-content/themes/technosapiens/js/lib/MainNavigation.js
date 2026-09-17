/**
 * class MainNavigation
 * Handles the main navigation dropdown functionality with click/touch accessibility.
 * Manages sub-menu toggle actions, keyboard interaction, and focus management.
 */
class MainNavigation {

    /**
     * CSS class constants
     * These constants are used to query dom elements for sub-menu toggle buttons and lists.
     */
    NAVIGATION_CLASS = 'main-navigation'
    LIST_CLASS = 'main-navigation__list'
    TOGGLE_BUTTON_CLASS = 'main-navigation__sub-toggle'
    ITEM_LINK_CLASS = 'main-navigation__item-link'
    ITEM_HAS_SUB_CLASS = 'main-navigation__item--has-sub'
    ITEM_HAS_MEGA_MENU_CLASS = 'main-navigation__item--has-mega-menu'
    SUB_MENU_CLASS = 'main-navigation__sub-menu'
    HEADER_INNER_CLASS = 'ts-header__inner'
    FOCUSABLE_SELECTOR = 'a[href], button, input, select, textarea, [tabindex]'
    SUB_MENU_HEIGHT_VARIABLE = '--ts-main-navigation-submenu-height'
    SUB_MENU_SHIFT_X_VARIABLE = '--ts-main-navigation-submenu-shift-x'
    SUB_MENU_TRANSITION_DURATION_MS = 220

    /**
     * MainNavigation constructor
     * @param {Element} $mainNavigationElement
     */
    constructor($mainNavigationElement) {
        if ($mainNavigationElement === null || !($mainNavigationElement instanceof Element)) {
            throw new Error('Missing required parameter `$mainNavigationElement` or given parameter was not a valid Element.')
        }

        this.state = {
            $navigationElement: $mainNavigationElement,
            $headerInnerElement: $mainNavigationElement.closest(`.${this.HEADER_INNER_CLASS}`),
            $toggleButtons: Array.from($mainNavigationElement.querySelectorAll(`.${this.TOGGLE_BUTTON_CLASS}:not([aria-controls^="mega-menu-"])`)),
            $itemLinks: Array.from($mainNavigationElement.querySelectorAll(`.${this.ITEM_HAS_SUB_CLASS}:not(.${this.ITEM_HAS_MEGA_MENU_CLASS}) > .${this.ITEM_LINK_CLASS}`)),
            $subMenus: Array.from($mainNavigationElement.querySelectorAll(`.${this.LIST_CLASS}`)),
            $topLevelSubMenus: Array.from($mainNavigationElement.querySelectorAll(`.${this.SUB_MENU_CLASS}`)),
            subMenuTransitionTimeouts: new Map()
        }

        this.state.$toggleButtons.forEach($toggleButton => this.addToggleButtonListeners($toggleButton))
        this.state.$itemLinks.forEach($itemLink => this.addItemLinkListeners($itemLink))
        this.state.$subMenus.forEach($subMenu => this.addSubMenuFocusOutListener($subMenu))
        this.state.$toggleButtons.forEach($toggleButton => this.syncSubMenuState($toggleButton))
        this.updateHeaderSubMenuHeight()

        document.addEventListener('keydown', this.handleEscapeKeyDown.bind(this))
        document.addEventListener('click', this.handleDocumentClick.bind(this))
        window.addEventListener('resize', this.handleWindowResize.bind(this))
    }

    /**
     * adds event listeners for toggle button actions (click, keydown, and focusout).
     * @param {Element} $toggleButton - The toggle button to attach the event listeners to.
     */
    addToggleButtonListeners($toggleButton) {
        $toggleButton.addEventListener('click', this.handleToggleButtonClick.bind(this, $toggleButton))
        $toggleButton.addEventListener('keydown', this.handleToggleButtonKeyDown.bind(this, $toggleButton))
        $toggleButton.addEventListener('focusout', this.handleToggleButtonFocusOut.bind(this, $toggleButton))
    }

    /**
     * adds event listeners for top-level item links with sub-menus.
     * @param {Element} $itemLink - The item link to attach the event listeners to.
     */
    addItemLinkListeners($itemLink) {
        $itemLink.addEventListener('click', this.handleItemLinkClick.bind(this, $itemLink))
        $itemLink.addEventListener('keydown', this.handleItemLinkKeyDown.bind(this, $itemLink))
    }

    /**
     * handle the keydown (Enter or Space) event for a top-level item link with a sub-menu.
     * @param {Element} $itemLink - The item link that was focused.
     * @param {KeyboardEvent} event - The keydown event.
     */
    handleItemLinkKeyDown($itemLink, event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault()
            this.handleItemLinkClick($itemLink, event)
        }
    }

    /**
     * Handle a click event on a top-level item link.
     * Opens the sub-menu below the item and grows the header, or closes when clicked again.
     * @param {Element} $itemLink - The item link that was clicked.
     * @param {Event} event - The click event.
     */
    handleItemLinkClick($itemLink, event) {
        const $item = $itemLink.closest(`.${this.ITEM_HAS_SUB_CLASS}`)
        const $toggleButton = $item?.querySelector(`.${this.TOGGLE_BUTTON_CLASS}`)

        if (!$toggleButton) return

        event.preventDefault()

        const isExpanded = $toggleButton.getAttribute('aria-expanded') === 'true'

        this.closeAllSubMenus()

        if (!isExpanded) {
            this.setSubMenuExpanded($toggleButton, true)
        }
    }

    /**
     * Sync aria-expanded state on the toggle button and its parent item link.
     * @param {Element} $toggleButton - The sub-menu toggle button.
     * @param {boolean} isExpanded - Whether the sub-menu is expanded.
     */
    setSubMenuExpanded($toggleButton, isExpanded) {
        const expandedValue = isExpanded ? 'true' : 'false'
        const $subMenu = this.getControlledSubMenu($toggleButton)
        const isCurrentlyExpanded = $toggleButton.getAttribute('aria-expanded') === 'true'

        if (isCurrentlyExpanded === isExpanded) return

        $toggleButton.setAttribute('aria-expanded', expandedValue)

        const $itemLink = $toggleButton.closest(`.${this.ITEM_HAS_SUB_CLASS}`)?.querySelector(`:scope > .${this.ITEM_LINK_CLASS}`)

        if ($itemLink?.hasAttribute('aria-expanded')) {
            $itemLink.setAttribute('aria-expanded', expandedValue)
        }

        this.syncSubMenuState($toggleButton)
        this.markSubMenuTransition($subMenu)
        this.updateHeaderSubMenuHeight()

        if (!$subMenu || !this.isTopLevelSubMenu($subMenu)) return

        if (!isExpanded) {
            $subMenu.style.setProperty(this.SUB_MENU_SHIFT_X_VARIABLE, '0px')
            return
        }

        window.requestAnimationFrame(() => {
            this.updateSubMenuHorizontalAlignment($subMenu)
            this.updateHeaderSubMenuHeight()
        })
    }

    /**
     * Handles window resize events by syncing submenu container height.
     */
    handleWindowResize() {
        const $activeTopLevelSubMenu = this.state.$toggleButtons
            .map($toggleButton => this.getControlledSubMenu($toggleButton))
            .find($subMenu => $subMenu && this.isTopLevelSubMenu($subMenu) && $subMenu.getAttribute('aria-hidden') === 'false')

        if ($activeTopLevelSubMenu) {
            this.updateSubMenuHorizontalAlignment($activeTopLevelSubMenu)
        }

        this.updateHeaderSubMenuHeight()
    }

    /**
     * Gets the submenu controlled by a toggle button.
     * @param {Element} $toggleButton - The submenu toggle button.
     * @returns {HTMLElement|null}
     */
    getControlledSubMenu($toggleButton) {
        const subMenuId = $toggleButton.getAttribute('aria-controls')
        if (!subMenuId) return null
        return document.getElementById(subMenuId)
    }

    /**
     * Checks if a submenu is a top-level submenu.
     * @param {Element|null} $subMenu - The submenu element.
     * @returns {boolean}
     */
    isTopLevelSubMenu($subMenu) {
        if (!$subMenu) return false
        return $subMenu.classList.contains(this.SUB_MENU_CLASS)
    }

    /**
     * Keeps submenu inside header width by shifting it horizontally.
     * @param {Element} $subMenu - The submenu element.
     */
    updateSubMenuHorizontalAlignment($subMenu) {
        const $headerInnerElement = this.state.$headerInnerElement
        if (!$headerInnerElement) return

        $subMenu.style.setProperty(this.SUB_MENU_SHIFT_X_VARIABLE, '0px')

        const headerRect = $headerInnerElement.getBoundingClientRect()
        const headerStyles = window.getComputedStyle($headerInnerElement)
        const headerPaddingRight = Number.parseFloat(headerStyles.paddingRight) || 0
        const subMenuRect = $subMenu.getBoundingClientRect()
        let horizontalShift = 0

        const headerContentRight = headerRect.right - headerPaddingRight
        const overflowRight = subMenuRect.right - headerContentRight
        if (overflowRight > 0) {
            horizontalShift -= overflowRight
        }

        const overflowLeft = headerRect.left - (subMenuRect.left + horizontalShift)
        if (overflowLeft > 0) {
            horizontalShift += overflowLeft
        }

        $subMenu.style.setProperty(this.SUB_MENU_SHIFT_X_VARIABLE, `${horizontalShift}px`)
    }

    /**
     * Marks a top-level submenu as transitioning and clears it after animation.
     * Header collapse waits until no submenu is transitioning anymore.
     * @param {Element|null} $subMenu - The submenu element.
     */
    markSubMenuTransition($subMenu) {
        if (!this.isTopLevelSubMenu($subMenu)) return

        $subMenu.setAttribute('data-transitioning', 'true')

        const existingTimeout = this.state.subMenuTransitionTimeouts.get($subMenu)
        if (existingTimeout) {
            window.clearTimeout(existingTimeout)
        }

        const transitionTimeout = window.setTimeout(() => {
            $subMenu.removeAttribute('data-transitioning')
            this.state.subMenuTransitionTimeouts.delete($subMenu)
            this.updateHeaderSubMenuHeight()
        }, this.SUB_MENU_TRANSITION_DURATION_MS)

        this.state.subMenuTransitionTimeouts.set($subMenu, transitionTimeout)
    }

    /**
     * Syncs aria-hidden and tabbable elements of a submenu based on toggle state.
     * @param {Element} $toggleButton - The submenu toggle button.
     */
    syncSubMenuState($toggleButton) {
        const $subMenu = this.getControlledSubMenu($toggleButton)
        if (!$subMenu) return

        const isExpanded = $toggleButton.getAttribute('aria-expanded') === 'true'
        $subMenu.setAttribute('aria-hidden', isExpanded ? 'false' : 'true')
        this.setSubMenuFocusableState($subMenu, isExpanded)
    }

    /**
     * Toggles focusability of submenu interactive elements.
     * @param {Element} $subMenu - The submenu element.
     * @param {boolean} isFocusable - Whether focus should be allowed.
     */
    setSubMenuFocusableState($subMenu, isFocusable) {
        const $focusableElements = Array.from($subMenu.querySelectorAll(this.FOCUSABLE_SELECTOR))

        $focusableElements.forEach($element => {
            if (isFocusable) {
                if (!$element.hasAttribute('data-navigation-tabindex')) return

                const previousTabIndex = $element.getAttribute('data-navigation-tabindex')
                if (previousTabIndex === '') {
                    $element.removeAttribute('tabindex')
                } else {
                    $element.setAttribute('tabindex', previousTabIndex)
                }

                $element.removeAttribute('data-navigation-tabindex')
                return
            }

            if (!$element.hasAttribute('data-navigation-tabindex')) {
                $element.setAttribute('data-navigation-tabindex', $element.getAttribute('tabindex') ?? '')
            }

            $element.setAttribute('tabindex', '-1')
        })
    }

    /**
     * Updates the header extra space with the currently open submenu height.
     */
    updateHeaderSubMenuHeight() {
        const $headerInnerElement = this.state.$headerInnerElement
        if (!$headerInnerElement) return

        const $activeOrTransitioningSubMenus = this.state.$topLevelSubMenus.filter($subMenu => {
            const isVisible = $subMenu.getAttribute('aria-hidden') === 'false'
            const isTransitioning = $subMenu.getAttribute('data-transitioning') === 'true'
            return isVisible || isTransitioning
        })

        if ($activeOrTransitioningSubMenus.length === 0) {
            $headerInnerElement.style.setProperty(this.SUB_MENU_HEIGHT_VARIABLE, '0rem')
            return
        }

        const maxSubMenuHeight = Math.max(...$activeOrTransitioningSubMenus.map($subMenu => $subMenu.scrollHeight))
        $headerInnerElement.style.setProperty(this.SUB_MENU_HEIGHT_VARIABLE, `${maxSubMenuHeight}px`)
    }

    /**
     * Handle a click event on a toggle button.
     * This toggles the 'aria-expanded' state of the button.
     * @param {Element} $toggleButton - The toggle button that was clicked.
     * @param {Event} event - The click event.
     */
    handleToggleButtonClick($toggleButton, event) {
        event.preventDefault()

        const isExpanded = $toggleButton.getAttribute('aria-expanded') === 'true'

        this.closeAllSubMenus()

        if (!isExpanded) {
            this.setSubMenuExpanded($toggleButton, true)
        }
    }

    /**
     * handle the keydown (Enter or Space) event for a toggle button.
     * @param {Element} $toggleButton - The toggle button that was focused.
     * @param {KeyboardEvent} event - The keydown event.
     */
    handleToggleButtonKeyDown($toggleButton, event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault()
            this.handleToggleButtonClick($toggleButton, event)
        }
    }

    /**
     * Handle clicks outside the header inner container.
     * @param {Event} event - The click event.
     */
    handleDocumentClick(event) {
        const $headerInnerElement = this.state.$headerInnerElement

        if (!$headerInnerElement || $headerInnerElement.contains(event.target)) return

        this.closeAllSubMenus()
    }

    /**
     * Closes all open sub-menus in the main navigation.
     */
    closeAllSubMenus() {
        this.state.$toggleButtons.forEach($toggleButton => {
            this.setSubMenuExpanded($toggleButton, false)
        })
    }

    /**
     * Handle the focusout event for a toggle button.
     * Ensures the 'aria-expanded' attribute is set to 'false' when focus leaves the toggle button.
     * @param {Element} $toggleButton - The toggle button that lost focus.
     * @param {FocusEvent} event - The focusout event.
     */
    handleToggleButtonFocusOut($toggleButton, event) {
        if (!(event instanceof FocusEvent)) return
        const $subMenu = this.getControlledSubMenu($toggleButton)
        if (!$subMenu) return
        if (!event.relatedTarget || $subMenu.contains(event.relatedTarget)) return
        this.setSubMenuExpanded($toggleButton, false)
    }

    /**
     * Add a focusout event listener to a sub-menu list.
     * When focus leaves the list, close the associated toggle button.
     * @param {Element} $subMenu - The sub-menu list to attach the focusout listener to.
     */
    addSubMenuFocusOutListener($subMenu) {
        $subMenu.addEventListener('focusout', event => {
            if (!event.relatedTarget || $subMenu.contains(event.relatedTarget)) return
            this.closeSubMenu($subMenu)
        })
    }

    /**
     * Closes a sub-menu by setting the 'aria-expanded' attribute of the associated toggle button to 'false'.
     * It only closes submenus that are currently open (aria-expanded="true").
     * @param {Element} $subMenu - The sub-menu whose toggle button should be closed.
     */
    closeSubMenu($subMenu) {
        const $toggleButton = $subMenu.parentElement.querySelector(`.${this.TOGGLE_BUTTON_CLASS}[aria-expanded="true"]`)
        if ($toggleButton) this.setSubMenuExpanded($toggleButton, false)
    }

    /**
     * Add a keydown event listener for the Escape key.
     * This allows closing of open sub-menus when the Escape key is pressed.
     */
    handleEscapeKeyDown(event) {
        const focusedElement = document.activeElement
        const navigation = focusedElement?.closest(`.${this.NAVIGATION_CLASS}`)

        if (!navigation || event.key.toLowerCase() !== 'escape') return

        const subMenuList = focusedElement.closest(`.${this.LIST_CLASS}`)
        const $toggleButton = subMenuList?.parentElement?.querySelector(`.${this.TOGGLE_BUTTON_CLASS}[aria-expanded="true"]`)
        if ($toggleButton) {
            this.setSubMenuExpanded($toggleButton, false)
            $toggleButton.focus()
        }
    }
}

export default MainNavigation
