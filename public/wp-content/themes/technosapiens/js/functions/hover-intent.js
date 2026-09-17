/**
 * Define hover intent duration in milliseconds
 * WCAG 2.1 AA - we set this to 800ms for people with a motor impairment
 * @type {number}
 */
const HOVER_INTENT_DURATION = 800

/**
 * Close sibling sub menus
 * @param {HTMLElement} currentElement
 */
const closeSiblingSubMenus = (currentElement) => {
    const $parentElement = currentElement.parentElement
    if ($parentElement) {

        //find siblings with active hover-intent based on current hover-intent element
        const $activeHoverIntentSiblingElements = Array.from($parentElement.querySelectorAll('[aria-haspopup="true"][aria-expanded="true"]'))

        //deactivate the sibling hover-intent to prevent multiple dropdowns visible
        $activeHoverIntentSiblingElements.forEach(activeHoverIntentSiblingElement => {
            activeHoverIntentSiblingElement.setAttribute('aria-expanded', false)
        })
    }
}

/**
 * Init hover intent
 * @param {array} $elements
 */
const initHoverIntent = ($elements = []) => {
    $elements.forEach($element => {
        let timer
        const $buttonElement = $element.querySelector('[aria-haspopup="true"][aria-expanded]')
        if ($buttonElement) {

            //handle mouse enter
            $element.addEventListener('mouseenter', () => {
                closeSiblingSubMenus($element)
                if (timer) clearTimeout(timer)
                $buttonElement?.setAttribute('aria-expanded', true)
            })

            //handle mouse leave
            $element.addEventListener('mouseleave', () => {
                if (timer) clearTimeout(timer)
                timer = setTimeout(() => {
                    $buttonElement?.setAttribute('aria-expanded', false)
                }, HOVER_INTENT_DURATION)
            })
        }
    })
}

export default initHoverIntent