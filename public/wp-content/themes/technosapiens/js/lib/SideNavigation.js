/**
 * class SideNavigation
 * Handles the side navigation functionality with nested menus and keyboard interaction.
 * Manages sub-menu toggle actions, 'Escape' and 'Tab' key behavior, and focus management.
 */
class SideNavigation {

    //CSS class constants
    NAVIGATION_CLASS = 'side-navigation__navigation'
    NAVIGATION_ROOT_CLASS = 'side-navigation__navigation--root'
    NAVIGATION_SECOND_CLASS = 'side-navigation__navigation--second'
    LINK_WITH_CHILDREN_CLASS = 'side-navigation__link--children'
    LINK_CLASS = 'side-navigation__link'
    MENU_BACK_CLASS = 'side-navigation__link--menu-back'
    MENU_TOGGLE_BUTTON_CLASS = 'menu-toggle-button'
    NAVIGATION_WRAPPER_CLASS = 'side-navigation__navigation-wrapper'
    NAVIGATION_ITEM_CLASS = 'side-navigation__navigation-item'

    /**
     * SideNavigation constructor
     * @param {Element} $sideNavigationElement
     */
    constructor($sideNavigationElement) {

        //validate the given $sideNavigationElement parameter
        if (!$sideNavigationElement || !($sideNavigationElement instanceof Element)) {
            throw new Error('Missing required parameter `$sideNavigationElement` or given parameter was not a valid Element')
        }

        //store DOM references in state
        this.state = {
            $sideNavigationElement,
            $linksWithChildren: Array.from($sideNavigationElement.querySelectorAll(`.${this.LINK_WITH_CHILDREN_CLASS}`)),
            $menuBackLinks: Array.from($sideNavigationElement.querySelectorAll(`.${this.MENU_BACK_CLASS}`))
        }

        //handle click on layer trigger
        this.state.$linksWithChildren.forEach($link => $link.addEventListener('click', this.handleLinkWithChildrenClick.bind(this, $link)))

        //handle back link clicks
        this.state.$menuBackLinks.forEach($backLink => $backLink.addEventListener('click', this.handleMenuBackClick.bind(this, $backLink)))

        //handle keydown events for 'Escape' and 'Tab' keys
        this.state.$sideNavigationElement.addEventListener('keydown', this.handleKeyDown.bind(this))
    }

    /**
     * Helper to find the currently open sub-menu or fallback to the root
     * @returns {Element}
     */
    getOpenSubMenuOrRoot() {
        return document.querySelector(`.${this.NAVIGATION_SECOND_CLASS} .${this.LINK_WITH_CHILDREN_CLASS}[aria-expanded="true"] + .${this.NAVIGATION_CLASS}`)
            || document.querySelector(`.${this.LINK_WITH_CHILDREN_CLASS}[aria-expanded="true"] + .${this.NAVIGATION_CLASS}`)
            || document.querySelector(`.${this.NAVIGATION_ROOT_CLASS}`)
    }

    /**
     * Handle a click event on a link that reveals a sub-menu
     * @param {Element} $link
     * @param {Event} event
     */
    handleLinkWithChildrenClick($link, event) {
        event.preventDefault()
        this.openSubMenu($link)
    }

    /**
     * Reveal the targeted sub-menu (remove inert and set aria-expanded="true")
     * @param {Element} $link
     */
    openSubMenu($link) {
        const navId = $link.getAttribute('aria-controls')
        $link.setAttribute('aria-expanded', 'true')
        const $subMenu = this.state.$sideNavigationElement.querySelector(`#${navId}`)
        if ($subMenu) {
            $subMenu.removeAttribute('inert')
            const $autoFocus = $subMenu.querySelector('[autofocus]')
            if ($autoFocus) $autoFocus.focus()
        }
    }

    /**
     * Handle a click event on a "back" link to navigate to the parent menu
     * @param {Element} $backLink
     * @param {Event} event
     */
    handleMenuBackClick($backLink, event) {
        event.preventDefault()
        this.navigateToParentMenu($backLink)
    }

    /**
     * Restore focus to the parent menu link and set aria-expanded="false"
     * @param {Element} $backLink
     */
    navigateToParentMenu($backLink) {
        const navId = $backLink.getAttribute('aria-controls')
        if (!navId) return

        const $childrenLink = this.state.$sideNavigationElement.querySelector(`.${this.LINK_WITH_CHILDREN_CLASS}[aria-controls="${navId}"]`)
        if ($childrenLink) {
            $childrenLink.setAttribute('aria-expanded', 'false')
            $childrenLink.focus()
        }

        const $nav = this.state.$sideNavigationElement.querySelector(`#${navId}`)
        if ($nav) $nav.setAttribute('inert', '')
    }

    /**
     * Handle all keydown events (Escape and Tab) within the side navigation
     * @param {KeyboardEvent} event
     */
    handleKeyDown(event) {
        if (event.key === 'Escape') this.handleEscapeKeyDown(event)
        if (event.key === 'Tab') this.handleTabKeyDown(event)
    }

    /**
     * Handle the Escape key behavior (close sub-menu if focus is not in the root navigation)
     * @param {KeyboardEvent} event
     */
    handleEscapeKeyDown(event) {
        const $focusedNav = event.target.closest(`.${this.NAVIGATION_CLASS}`)
        if ($focusedNav) {
            const isRootNav = $focusedNav?.classList.contains(this.NAVIGATION_ROOT_CLASS)
            if (!isRootNav) {
                event.preventDefault()
                this.closeActivePopup()
            }
        }
    }

    /**
     * Close the currently active popup (set inert, reset aria-expanded, refocus triggering link)
     */
    closeActivePopup() {
        const $focusedElement = this.state.$sideNavigationElement.querySelector(':focus')
        if (!$focusedElement) return

        const $focusedNav = $focusedElement.closest(`.${this.NAVIGATION_CLASS}`)
        if (!$focusedNav) return

        //set inert on the active nav
        $focusedNav.setAttribute('inert', '')

        //see if the sibling link is expanded
        const $activePopup = $focusedNav.previousElementSibling?.matches?.('[aria-expanded="true"]')
            ? $focusedNav.previousElementSibling
            : null

        if ($activePopup) {
            $activePopup.setAttribute('aria-expanded', 'false')
            $activePopup.focus()
        }
    }

    /**
     * Handle the Tab key behavior for trapping focus within sub-menus
     * @param {KeyboardEvent} event
     */
    handleTabKeyDown(event) {
        //find the currently open sub-menu or fallback to the root
        const $focusedNav = this.getOpenSubMenuOrRoot()
        const isRootNav = $focusedNav?.classList.contains(this.NAVIGATION_ROOT_CLASS)
        if (!isRootNav) {
            const $menuToggle = this.state.$sideNavigationElement.querySelector(`.${this.MENU_TOGGLE_BUTTON_CLASS}`)

            //gather links within the currently open nav
            const $links = $focusedNav?.querySelectorAll(`:scope > .${this.NAVIGATION_WRAPPER_CLASS} > .${this.NAVIGATION_ITEM_CLASS} > .${this.LINK_CLASS}`)
            const $firstLink = $links?.[0]
            const $lastLink = $links?.[$links.length - 1]

            //shift+Tab on first link => move focus to menu toggle
            if ($firstLink && event.target === $firstLink && event.shiftKey) {
                event.preventDefault()
                if ($menuToggle) $menuToggle.focus()
            }

            //tab on last link => move focus to menu toggle
            if ($lastLink && event.target === $lastLink && !event.shiftKey) {
                event.preventDefault()
                if ($menuToggle) $menuToggle.focus()
            }

            //tabbing from menu toggle => cycle focus between first/last link
            if ($menuToggle && event.target === $menuToggle) {
                if (event.shiftKey && $lastLink) {
                    event.preventDefault()
                    $lastLink.focus()
                } else if (!event.shiftKey && $firstLink) {
                    event.preventDefault()
                    $firstLink.focus()
                }
            }
        }
    }
}

export default SideNavigation
