/**
 * WCAG 2.1 AA - Path-based hover intent for mega menus with velocity-based timeouts
 * Uses smart delays that adapt to mouse movement speed and patterns
 * @type {number}
 */
const LEVEL_1_SWITCH_DELAY = 300  // Base delay before switching when already have active submenu
const BASE_DIAGONAL_TIMEOUT = 250  // Minimum timeout for diagonal navigation
const MAX_DIAGONAL_TIMEOUT = 600   // Maximum timeout for diagonal navigation
const MEGA_MENU_EXIT_DELAY = 800   // Delay when leaving entire mega menu
const VELOCITY_SAMPLE_COUNT = 3    // Number of mouse positions to track for velocity

/**
 * Class TwoColumnsWithCardPreset (Simplified)
 * Handles internal navigation within the mega menu for the two-columns-with-card preset.
 * Simplified version following SideNavigation.js patterns.
 */
class TwoColumnsWithCardPreset {

    //css class constants
    TRIGGER_CLASS = '.tcwc-preset__trigger'
    LEVEL_ONE_ITEM_CLASS = '[data-mm-item]'
    PARENT_TITLE_CLASS = '.tcwc-preset__parent-title'
    SUBMENU_CLASS = '.tcwc-preset__submenu'
    SUBMENU_ACTIVE_CLASS = 'tcwc-preset__submenu--active'
    LEVEL_2_COLUMN_SELECTOR = '.tcwc-preset__col--second'

    /**
     * TwoColumnsWithCardPreset constructor
     * @param {Element} $megaMenuElement - The mega menu container element
     */
    constructor($megaMenuElement = null) {

        //validations
        if (!$megaMenuElement || !($megaMenuElement instanceof Element)) {
            throw new Error("Missing or invalid `$megaMenuElement` given in TwoColumnsWithCardPreset class constructor.")
        }

        //store DOM references and state in state object
        this.state = {

            //elements
            $megaMenuElement: $megaMenuElement,
            $triggers: Array.from($megaMenuElement.querySelectorAll(this.TRIGGER_CLASS)),
            $levelOneItems: Array.from($megaMenuElement.querySelectorAll(this.LEVEL_ONE_ITEM_CLASS)),
            $parentTitles: Array.from($megaMenuElement.querySelectorAll(this.PARENT_TITLE_CLASS)),
            $level2Column: $megaMenuElement.querySelector(this.LEVEL_2_COLUMN_SELECTOR),

            //path-based hover intent state
            activeSubmenuId: null,      //currently visible submenu
            pendingSwitchId: null,      //submenu pending to be switched to
            switchTimeout: null,        //timeout for level 1 switches
            diagonalTimeout: null,      //safety timeout for diagonal navigation
            exitTimeout: null,          //timeout for leaving mega menu
            userInLevel2: false,        //track if user successfully reached level 2

            //velocity-based timeout tracking
            mousePositions: [],         //array to store recent mouse positions with timestamps
            lastMouseTime: 0,           //timestamp of last mouse movement
            currentVelocity: 0,         //current calculated mouse velocity (pixels per ms)
            mouseCleanupTimeout: null,  //timeout for cleaning up stale mouse tracking data

            //device detection
            isTouchDevice: 'ontouchstart' in window,
            supportsHover: window.matchMedia('(hover: hover)').matches && !('ontouchstart' in window),
            touchHandled: false
        }

        this.addEventListeners()
    }

    /**
     * Add all event listeners
     */
    addEventListeners() {

        //click and keyboard events for triggers
        this.state.$triggers.forEach($trigger => {

            //prevent duplicate event listeners by checking if already initialized
            if (!$trigger.dataset.tcwcListenersAdded) {
                $trigger.addEventListener('click', this.handleTriggerClick.bind(this))
                $trigger.addEventListener('keydown', this.handleTriggerKeyDown.bind(this))

                //add touch events for touch devices to improve responsiveness
                if (this.state.isTouchDevice) {
                    $trigger.addEventListener('touchstart', this.handleTriggerTouchStart.bind(this), {passive: true})
                }

                //mark this trigger as having listeners added
                $trigger.dataset.tcwcListenersAdded = 'true'
            }

        })

        //add hover management only for devices that support hover properly
        if (this.state.supportsHover) this.addHoverManagement()

        //enhanced keyboard navigation handling
        this.state.$megaMenuElement.addEventListener('keydown', this.handleKeyboardNavigation.bind(this))
    }

    /**
     * Handle trigger click
     * @param {Event} event
     */
    handleTriggerClick(event) {
        event.preventDefault()
        event.stopPropagation()

        //on touch devices, if touch was just handled, ignore the following click
        if (this.state.isTouchDevice && this.state.touchHandled) {
            this.state.touchHandled = false
            return
        }

        //clear any pending timeouts since this is an explicit click
        this.clearAllTimeouts()

        //check if the target is expanded or not
        const $trigger = event.currentTarget
        const targetId = $trigger.dataset.mmTrigger
        const isExpanded = $trigger.getAttribute('aria-expanded') === 'true'

        //bail if no target ID
        if (!targetId) return

        //toggle submenu
        isExpanded ? this.hideSubmenu(targetId) : this.showSubmenu(targetId)
    }

    /**
     * Handle trigger touch start for touch devices
     * @param {TouchEvent} event
     */
    handleTriggerTouchStart(event) {
        const $trigger = event.currentTarget
        const targetId = $trigger.dataset.mmTrigger
        const isExpanded = $trigger.getAttribute('aria-expanded') === 'true'

        //bail if no target ID
        if (!targetId) return

        //clear any pending timeouts since this is an explicit touch
        this.clearAllTimeouts()

        //mark that touch was handled to prevent the following click
        this.state.touchHandled = true

        //toggle the submenu immediately on touch
        isExpanded ? this.hideSubmenu(targetId) : this.showSubmenu(targetId)
    }

    /**
     * Handle trigger keyboard events
     * @param {KeyboardEvent} event
     */
    handleTriggerKeyDown(event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault()
            event.stopPropagation()

            //clear any pending timeouts since this is an explicit keyboard action
            this.clearAllTimeouts()

            //check if the target is expanded or not
            const $trigger = event.currentTarget
            const targetId = $trigger.dataset.mmTrigger
            const isExpanded = $trigger.getAttribute('aria-expanded') === 'true'

            //bail if no target ID
            if (!targetId) return

            if (isExpanded) {
                this.hideSubmenu(targetId)
            } else {
                this.showSubmenu(targetId)
                this.focusFirstSubmenuLink(targetId) // <-- focus the first link in the submenu after opening
            }
        }
    }

    /**
     * Handle keyboard navigation (ESC and TAB keys)
     * @param {KeyboardEvent} event
     */
    handleKeyboardNavigation(event) {
        //only handle ESC if we're in level 2, otherwise let MegaMenu class JS handle it
        if (event.key === 'Escape') {
            const isInLevel2 = this.isElementInLevel2(event.target)
            if (isInLevel2) this.handleEscapeKey(event)
        } else if (event.key === 'Tab') {
            //if not in level 2, let the event bubble up to MegaMenu class JS
            this.handleTabKey(event)
        }
    }

    /**
     * Handle escape key for level 2 items (go back to parent trigger)
     * This method is only called when focus is in level 2
     * @param {KeyboardEvent} event
     */
    handleEscapeKey(event) {
        if (this.closeAllAndFocusParent(event.target)) {
            event.preventDefault()
            event.stopPropagation()
        }
    }

    /**
     * Handle tab key for navigation between levels
     * @param {KeyboardEvent} event
     */
    handleTabKey(event) {
        const focusedElement = event.target
        const shouldCloseLevel2 = event.shiftKey ? this.isFirstItemInLevel2(focusedElement) : this.isLastItemInLevel2(focusedElement)
        if (shouldCloseLevel2 && this.closeAllAndFocusParent(focusedElement)) event.preventDefault()
    }

    /**
     * Add complete hover management for this preset
     * Replaces hover-intent functionality with custom implementation
     */
    addHoverManagement() {

        //add hover events to level 1 items
        this.state.$levelOneItems.forEach($item => {
            $item.addEventListener('mouseenter', this.handleLevel1Enter.bind(this))
            $item.addEventListener('mouseleave', this.handleLevel1Leave.bind(this))
        })

        //add hover events to the level 2 column if it exists
        if (this.state.$level2Column) {
            this.state.$level2Column.addEventListener('mouseenter', this.handleLevel2Enter.bind(this))
            this.state.$level2Column.addEventListener('mouseleave', this.handleLevel2Leave.bind(this))

            //also add hover events to individual submenu items for extra insurance
            const submenuItems = this.state.$level2Column.querySelectorAll('[data-mm-submenu] a')
            submenuItems.forEach($item => $item.addEventListener('mouseenter', this.handleLevel2Enter.bind(this)))
        }

        //add hover management to the entire mega menu for better gap handling
        this.state.$megaMenuElement.addEventListener('mouseenter', this.handleMegaMenuEnter.bind(this))
        this.state.$megaMenuElement.addEventListener('mouseleave', this.handleMegaMenuLeave.bind(this))

        //add mouse movement tracking for velocity-based timeouts
        this.state.$megaMenuElement.addEventListener('mousemove', this.trackMouseMovement.bind(this))
    }

    /**
     * Handle mouse enter on level 1 item - Smart switching with hover intent
     * @param {MouseEvent} event
     */
    handleLevel1Enter(event) {
        this.clearAllTimeouts()
        this.state.userInLevel2 = false

        const $item = event.currentTarget
        const itemId = $item.dataset.mmItem

        if (itemId && this.hasSubmenu(itemId)) {

            //if this is already the active submenu, do nothing
            if (this.state.activeSubmenuId === itemId) return

            //if no submenu is currently active, show immediately (fast initial response)
            if (!this.state.activeSubmenuId) {
                this.showSubmenu(itemId)
                this.state.activeSubmenuId = itemId
                return
            }

            //we have an active submenu, and the user is hovering a different item
            //add delay to prevent accidental switches when passing through
            this.state.pendingSwitchId = itemId
            this.setSwitchTimeout(() => {
                this.showSubmenu(itemId)
                this.state.activeSubmenuId = itemId
                this.state.pendingSwitchId = null
            })
        } else {
            //item has no submenu - close everything with slight delay to allow passing through
            this.state.pendingSwitchId = null
            this.setSwitchTimeout(() => {
                this.hideAllSubmenus()
                this.state.activeSubmenuId = null
            })
        }
    }

    /**
     * Handle mouse leave from level 1 item - Enhanced path detection for level 2 column
     * @param {MouseEvent} event
     */
    handleLevel1Leave(event) {
        const relatedTarget = event.relatedTarget
        const $currentItem = event.currentTarget

        //enhanced check: if moving to the level 2 column OR its children, keep the submenu active
        if (relatedTarget && this.isMovingToLevel2Area(relatedTarget)) return

        //if moving to another level 1 item, let that item's "enter" key handler take over
        if (relatedTarget && relatedTarget.closest('[data-mm-item]')) return

        //if moving to gaps/empty space within the mega menu - be more forgiving for level 2 navigation
        if (relatedTarget && this.state.$megaMenuElement.contains(relatedTarget)) {
            //user is moving through gaps - might be heading to level 2
            //set a longer safety timeout for better diagonal navigation
            this.setDiagonalTimeout()
            return
        }

        //moving completely outside the mega menu - but check if it might be a path to level 2
        if (this.state.activeSubmenuId && this.isPotentialLevel2Path(event)) {
            //give extra time for complex diagonal movements to level 2
            this.setDiagonalTimeout()
            return
        }

        //moving completely outside mega menu - close after delay
        this.setExitTimeout()
    }

    /**
     * Handle mouse enter on level 2 column - User successfully reached level 2!
     */
    handleLevel2Enter() {

        //cancel any pending switches or close actions
        this.clearAllTimeouts()

        //mark that user successfully reached level 2
        this.state.userInLevel2 = true

        //cancel any pending submenu switch
        this.state.pendingSwitchId = null
    }

    /**
     * Handle mouse leave from level 2 column - Enhanced to be more forgiving
     * @param {MouseEvent} event
     */
    handleLevel2Leave(event) {
        const relatedTarget = event.relatedTarget
        this.state.userInLevel2 = false

        //enhanced check: if still moving within level 2 area, don't trigger leave logic
        if (relatedTarget && this.isMovingToLevel2Area(relatedTarget)) {
            this.state.userInLevel2 = true //keep user in level 2 state
            return
        }

        //if moving back to level 1 item, let that item handle the transition
        if (relatedTarget && relatedTarget.closest('[data-mm-item]')) return

        //if staying within mega menu gaps, set diagonal timeout (be more forgiving)
        if (relatedTarget && this.state.$megaMenuElement.contains(relatedTarget)) {
            this.setDiagonalTimeout()
            return
        }

        //check if the mouse is still near the level 2 area (potential return path)
        if (this.isPotentialLevel2Path(event)) {
            this.setDiagonalTimeout() //give extra time for complex movements
            return
        }

        //moving completely outside a mega menu
        this.setExitTimeout()
    }

    /**
     * Handle mouse enter on the entire mega menu
     * Used to cancel any pending close timeouts when user re-enters
     */
    handleMegaMenuEnter() {
        this.clearExitTimeout() // <-- cancel exit timeout if user re-enters mega menu
    }

    /**
     * Handle mouse leave from the entire mega menu
     * Only close if truly leaving the mega menu area
     * @param {MouseEvent} event
     */
    handleMegaMenuLeave(event) {
        const relatedTarget = event.relatedTarget

        //if moving to a child element within the mega menu, don't close
        if (relatedTarget && this.state.$megaMenuElement.contains(relatedTarget)) return

        //definitely leaving mega menu - close after delay
        this.setExitTimeout()
    }

    /**
     * Set a diagonal navigation timeout with velocity-based duration
     * - safety net if the user doesn't reach level 2
     */
    setDiagonalTimeout() {
        this.clearDiagonalTimeout()
        const adaptiveTimeout = this.calculateVelocityBasedTimeout()
        this.state.diagonalTimeout = setTimeout(() => {
            //only close if the user didn't successfully reach level 2
            if (!this.state.userInLevel2) {
                this.hideAllSubmenus()
                this.state.activeSubmenuId = null
            }
        }, adaptiveTimeout)
    }

    /**
     * Clear diagonal navigation timeout
     */
    clearDiagonalTimeout() {
        if (this.state.diagonalTimeout) {
            clearTimeout(this.state.diagonalTimeout)
            this.state.diagonalTimeout = null
        }
    }

    /**
     * Set exit timeout for leaving mega menu entirely
     */
    setExitTimeout() {
        this.clearExitTimeout()
        this.state.exitTimeout = setTimeout(() => {
            this.hideAllSubmenus()
            this.state.activeSubmenuId = null
            this.state.userInLevel2 = false
        }, MEGA_MENU_EXIT_DELAY)
    }

    /**
     * Clear exit timeout
     */
    clearExitTimeout() {
        if (this.state.exitTimeout) {
            clearTimeout(this.state.exitTimeout)
            this.state.exitTimeout = null
        }
    }

    /**
     * Set switch timeout for level 1 item changes
     * @param {Function} callback
     */
    setSwitchTimeout(callback) {
        this.clearSwitchTimeout()
        this.state.switchTimeout = setTimeout(callback, LEVEL_1_SWITCH_DELAY)
    }

    /**
     * Clear switch timeout
     */
    clearSwitchTimeout() {
        if (this.state.switchTimeout) {
            clearTimeout(this.state.switchTimeout)
            this.state.switchTimeout = null
        }
    }

    /**
     * Clear all timeouts - for explicit user actions
     */
    clearAllTimeouts() {
        this.clearSwitchTimeout()
        this.clearDiagonalTimeout()
        this.clearExitTimeout()
    }

    /**
     * Show submenu for given ID
     * @param {string} targetId
     */
    showSubmenu(targetId) {

        //hide all other submenus first (but preserve the current trigger state)
        this.hideAllSubmenusPersistingTarget(targetId)

        //find and show target submenu
        const $targetSubmenu = this.state.$megaMenuElement.querySelector(`[data-mm-submenu="${targetId}"]`)
        if ($targetSubmenu) $targetSubmenu.classList.add(this.SUBMENU_ACTIVE_CLASS)

        //update trigger aria-expanded for accessibility
        const $trigger = this.state.$megaMenuElement.querySelector(`[data-mm-trigger="${targetId}"]`)
        if ($trigger) $trigger.setAttribute('aria-expanded', 'true')

        //show parent title
        this.showParentTitle(targetId)
    }

    /**
     * Hide submenu for given ID
     * @param {string} targetId
     */
    hideSubmenu(targetId) {

        //hide target submenu
        const $targetSubmenu = this.state.$megaMenuElement.querySelector(`[data-mm-submenu="${targetId}"]`)
        if ($targetSubmenu) $targetSubmenu.classList.remove(this.SUBMENU_ACTIVE_CLASS)

        //update trigger aria-expanded for accessibility
        const $trigger = this.state.$megaMenuElement.querySelector(`[data-mm-trigger="${targetId}"]`)
        if ($trigger) $trigger.setAttribute('aria-expanded', 'false')

        this.hideAllParentTitles()
    }

    /**
     * Hide all submenus
     */
    hideAllSubmenus() {

        //hide all submenus
        this.state.$megaMenuElement.querySelectorAll(`.${this.SUBMENU_ACTIVE_CLASS}`).forEach($submenu => $submenu.classList.remove(this.SUBMENU_ACTIVE_CLASS))

        //reset all aria-expanded for accessibility
        this.state.$triggers.forEach($trigger => $trigger.setAttribute('aria-expanded', 'false'))

        //hide all parent titles
        this.hideAllParentTitles()
    }

    /**
     * Hide all submenus except the target one, and preserve the aria-expanded state of the target trigger
     * @param {string} preserveTargetId - The target ID whose submenu and trigger state should be preserved
     */
    hideAllSubmenusPersistingTarget(preserveTargetId) {

        //hide all submenus except the target one
        this.state.$megaMenuElement.querySelectorAll(`.${this.SUBMENU_ACTIVE_CLASS}`).forEach($submenu => {
            const submenuId = $submenu.getAttribute('data-mm-submenu')
            if (submenuId !== preserveTargetId) $submenu.classList.remove(this.SUBMENU_ACTIVE_CLASS)
        })

        //reset all aria-expanded for accessibility except for the target trigger
        this.state.$triggers.forEach($trigger => {
            const triggerTargetId = $trigger.dataset.mmTrigger
            if (triggerTargetId !== preserveTargetId) $trigger.setAttribute('aria-expanded', 'false')
        })

        //hide all parent titles
        this.hideAllParentTitles()
    }

    /**
     * Check if item has submenu
     * @param {string} itemId
     * @returns {boolean}
     */
    hasSubmenu(itemId) {
        return this.state.$megaMenuElement.querySelector(`[data-mm-submenu="${itemId}"]`) !== null
    }

    /**
     * Show parent title for given ID
     * @param {string} targetId
     */
    showParentTitle(targetId) {
        this.hideAllParentTitles()
        const $parentTitle = this.state.$megaMenuElement.querySelector(`[data-mm-parent="${targetId}"]`)
        if ($parentTitle) $parentTitle.classList.add('tcwc-preset__parent-title--active')
    }

    /**
     * Hide all parent titles (except main)
     */
    hideAllParentTitles() {
        this.state.$parentTitles.forEach($title => {
            if (!$title.hasAttribute('data-mm-main-parent')) $title.classList.remove('tcwc-preset__parent-title--active')
        })
    }

    /**
     * Focus first hyperlink in level 2 area (parent title link or first submenu link)
     * @param {string} targetId
     */
    focusFirstSubmenuLink(targetId) {

        //look for a parent title link first
        const $parentTitle = this.state.$megaMenuElement.querySelector(`[data-mm-parent="${targetId}"]`)
        const $parentTitleLink = $parentTitle?.querySelector('a[href]')

        //if the parent title has a link, focus on that first
        if ($parentTitleLink) {
            requestAnimationFrame(() => $parentTitleLink.focus())
            return
        }

        //otherwise, focus the first link in the submenu
        const $targetSubmenu = this.state.$megaMenuElement.querySelector(`[data-mm-submenu="${targetId}"]`)
        const $firstSubmenuLink = $targetSubmenu?.querySelector('a[href]')
        if ($firstSubmenuLink) requestAnimationFrame(() => $firstSubmenuLink.focus())
    }

    /**
     * Close all level 2 content and focus parent trigger for a level 2 element
     * @param {Element} level2Element
     * @returns {boolean} - Whether the operation was successful
     */
    closeAllAndFocusParent(level2Element) {

        //get the parent trigger for this level 2 element
        const parentTrigger = this.findParentTriggerForLevel2Element(level2Element)

        //if found, close all and focus the trigger
        if (parentTrigger) {
            this.hideAllSubmenus()
            parentTrigger.focus()
            return true
        }

        return false
    }

    /**
     * Get all visible focusable elements in the level 2 area
     * @returns {Array}
     */
    getVisibleLevel2FocusableElements() {
        if (!this.state.$level2Column) return []
        const focusableElements = Array.from(this.state.$level2Column.querySelectorAll('a[href], button, [tabindex]:not([tabindex="-1"])'))
        return focusableElements.filter(el => {
            const parentTitle = el.closest('.tcwc-preset__parent-title')
            const submenu = el.closest('.tcwc-preset__submenu')
            return (parentTitle && parentTitle.classList.contains('tcwc-preset__parent-title--active')) || (submenu && submenu.classList.contains(this.SUBMENU_ACTIVE_CLASS))
        })
    }

    /**
     * Check if element is within level 2 area (submenu items or parent title links)
     * @param {Element} element
     * @returns {boolean}
     */
    isElementInLevel2(element) {

        //check if the element is within a submenu
        if (element.closest('.tcwc-preset__submenu')) return true

        //check if the element is within an active parent title (level 2 parent link)
        const parentTitle = element.closest('.tcwc-preset__parent-title')

        //only count if the parent title is currently active
        return parentTitle && parentTitle.classList.contains('tcwc-preset__parent-title--active')
    }

    /**
     * Find the parent trigger for a level 2 element (submenu item or parent title link)
     * @param {Element} level2Element
     * @returns {Element|null}
     */
    findParentTriggerForLevel2Element(level2Element) {
        let targetId = null

        //check if the element is within a submenu
        const submenu = level2Element.closest('.tcwc-preset__submenu')
        if (submenu) {
            targetId = submenu.getAttribute('data-mm-submenu')
        } else {

            //check if element is within a parent title
            const parentTitle = level2Element.closest('.tcwc-preset__parent-title')

            //get the parent ID from data-mm-parent attribute
            if (parentTitle) targetId = parentTitle.getAttribute('data-mm-parent')
        }

        //bail if no target ID found
        if (!targetId) return null

        return this.state.$megaMenuElement.querySelector(`[data-mm-trigger="${targetId}"]`)
    }

    /**
     * Check if element is the first focusable item in the entire level 2 area
     * @param {Element} element
     * @returns {boolean}
     */
    isFirstItemInLevel2(element) {
        if (!this.isElementInLevel2(element)) return false
        const visibleElements = this.getVisibleLevel2FocusableElements()
        return visibleElements.length > 0 && visibleElements[0] === element
    }

    /**
     * Check if element is the last focusable item in the entire level 2 area
     * @param {Element} element
     * @returns {boolean}
     */
    isLastItemInLevel2(element) {
        if (!this.isElementInLevel2(element)) return false
        const visibleElements = this.getVisibleLevel2FocusableElements()
        return visibleElements.length > 0 && visibleElements[visibleElements.length - 1] === element
    }

    /**
     * Enhanced detection: Check if mouse is moving toward level 2 area
     * @param {Element} relatedTarget - The element the mouse is moving to
     * @returns {boolean}
     */
    isMovingToLevel2Area(relatedTarget) {

        //bail if no level 2 column found
        if (!relatedTarget || !this.state.$level2Column) return false

        //direct hit: mouse is in level 2 column
        if (this.state.$level2Column.contains(relatedTarget)) return true

        //check if moving to any element that's part of level 2 navigation
        const isLevel2Element = relatedTarget.closest('.tcwc-preset__submenu') ||
            relatedTarget.closest('[data-mm-submenu]') ||
            relatedTarget.closest('.tcwc-preset__col--second')

        return !!isLevel2Element
    }

    /**
     * Smart detection: Check if mouse movement might be a diagonal path to level 2
     * @param {MouseEvent} event - The mouse leave event
     * @returns {boolean}
     */
    isPotentialLevel2Path(event) {
        if (!this.state.$level2Column) return false

        //get the bounds of the level 2 column
        const level2Bounds = this.state.$level2Column.getBoundingClientRect()
        const mouseX = event.clientX
        const mouseY = event.clientY

        //check if the mouse is moving in the general direction of the level 2 column
        //allow for a generous "cone" of movement toward the level 2 area
        const isInHorizontalRange = mouseX >= (level2Bounds.left - 50) && mouseX <= (level2Bounds.right + 50)
        const isInVerticalRange = mouseY >= (level2Bounds.top - 50) && mouseY <= (level2Bounds.bottom + 50)

        return isInHorizontalRange && isInVerticalRange
    }

    /**
     * Track mouse movement for velocity-based timeout calculations
     * @param {MouseEvent} event - Mouse movement event
     */
    trackMouseMovement(event) {
        const now = performance.now()
        const position = {x: event.clientX, y: event.clientY, timestamp: now}

        //add current position to tracking array
        this.state.mousePositions.push(position)

        //keep only the most recent positions for velocity calculation
        if (this.state.mousePositions.length > VELOCITY_SAMPLE_COUNT) this.state.mousePositions.shift()

        //calculate velocity if we have enough data points
        if (this.state.mousePositions.length >= 2) this.state.currentVelocity = this.calculateMouseVelocity()

        this.state.lastMouseTime = now
    }

    /**
     * Calculate current mouse velocity based on recent positions
     * @returns {number} - Velocity in pixels per millisecond
     */
    calculateMouseVelocity() {
        if (this.state.mousePositions.length < 2) return 0

        //use the oldest and newest positions for velocity calculation
        const recent = this.state.mousePositions
        const oldest = recent[0]
        const newest = recent[recent.length - 1]

        //calculate distance moved
        const deltaX = newest.x - oldest.x
        const deltaY = newest.y - oldest.y
        const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY)

        //calculate time elapsed
        const timeElapsed = newest.timestamp - oldest.timestamp

        //bail if no time elapsed
        if (timeElapsed === 0) return 0

        //return velocity in pixels per millisecond
        return distance / timeElapsed
    }

    /**
     * Calculate adaptive timeout based on mouse velocity and movement patterns
     * @returns {number} - Timeout duration in milliseconds
     */
    calculateVelocityBasedTimeout() {
        const velocity = this.state.currentVelocity

        //fast movement (> 1.5 pixels/ms) = shorter timeout (responsive)
        if (velocity > 1.5) return BASE_DIAGONAL_TIMEOUT

        //slow movement (< 0.3 pixels/ms) = longer timeout (deliberate/careful)
        if (velocity < 0.3) return MAX_DIAGONAL_TIMEOUT

        //moderate movement = interpolated timeout
        //linear interpolation between min and max based on velocity
        const velocityRange = 1.5 - 0.3
        const normalizedVelocity = Math.max(0, Math.min(1, (velocity - 0.3) / velocityRange))
        const timeoutRange = MAX_DIAGONAL_TIMEOUT - BASE_DIAGONAL_TIMEOUT

        return Math.round(MAX_DIAGONAL_TIMEOUT - (normalizedVelocity * timeoutRange))
    }
}

export default TwoColumnsWithCardPreset