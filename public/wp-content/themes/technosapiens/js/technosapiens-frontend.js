/**
 * Class Frontend
 * Initiates a bunch of (dynamic) JS in the front-end
 */
class Frontend {

    /**
     * Window width integer
     * @type {number}
     */
    windowWidth = 0

    /**
     * Define mobile breakpoint
     * @type {number}
     */
    mobileBreakPoint = 1023

    /**
     * Frontend constructor
     */
    constructor() {

        //get window width
        this.windowWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth

        //init dataLayer
        window.dataLayer = window.dataLayer || []

        //init public methods
        this.initMainNavigation()
        this.initSideNavigation()

        //init static methods
        Frontend.initGravityForms()
        Frontend.initMaterialInputs()
        Frontend.initScrollBehaviour()
    }

    /**
     * Init main navigation
     */
    initMainNavigation() {
        const $mainNavigationElement = document.querySelector('.main-navigation')
        if ($mainNavigationElement && this.windowWidth > this.mobileBreakPoint) {
            import(/* webpackChunkName: "main-navigation" */ "./lib/MainNavigation").then(({default: MainNavigation}) => {
                new MainNavigation($mainNavigationElement)
            }).catch((error) => console.log("Something went wrong while importing the main-navigation chunk..", error))
        }
    }

    /**
     * Init side navigation
     */
    initSideNavigation() {
        const $sideNavigationElement = document.querySelector('.side-navigation')
        if ($sideNavigationElement && this.windowWidth <= this.mobileBreakPoint) {
            import(/* webpackChunkName: "side-navigation" */ "./lib/SideNavigation").then(({default: SideNavigation}) => {
                new SideNavigation($sideNavigationElement)
            }).catch((error) => console.log("Something went wrong while importing the side-navigation chunk..", error))
        }
    }

    /**
     * Init Gravity Forms JS
     */
    static initGravityForms() {
        window.addEventListener('DOMContentLoaded', () => {
            if (typeof jQuery !== 'undefined') {

                //init JS after gform post render and rebind JS on ajax submit
                jQuery(document).on('gform_post_render', () => Frontend.initMaterialInputs())

                //add dataLayer event for Gravity Forms tracking
                jQuery(document).bind("gform_confirmation_loaded", (event, formId) => {
                    const submitEvent = {
                        event: "ts_form_submit_simple",
                        formId: formId,
                        timestamp: Math.round(new Date().getTime() / 1000)
                    }
                    const eventExists = window.dataLayer.find((event) => JSON.stringify(event) === JSON.stringify(submitEvent))
                    if (!eventExists) window.dataLayer.push(submitEvent)
                })
            }
        })
    }

    /**
     * Init material inputs
     */
    static initMaterialInputs() {
        const selectors = [...Array.from(document.querySelectorAll('.material-input'))]
        if (selectors.length > 0) {
            import(/* webpackChunkName: "material-input" */ "./lib/MaterialInput").then(({default: MaterialInput}) => {
                selectors.forEach(($containerElement) => {
                    const isTextarea = $containerElement.classList.contains('type-textarea') || $containerElement.classList.contains('type-post_content')
                    const $labelElement = $containerElement.querySelector('label')
                    const $inputElement = $containerElement.querySelector(`${isTextarea ? `textarea` : `input`}`)
                    if ($labelElement && $inputElement) new MaterialInput($containerElement, $labelElement, $inputElement)
                })
            }).catch((error) => console.log("Something went wrong while importing material-input chunk..", error))
        }
    }

    /**
     * Init scroll behaviour
     */
    static initScrollBehaviour() {
        // Select the header element
        const header = document.querySelector('.ts-header');

        // Function to handle the scroll logic
        function handleScroll() {
            // Check if the user has scrolled 16 pixels or more
            if (window.scrollY >= 48) {
                header.classList.add('ts-header--scrolled');
            } else {
                header.classList.remove('ts-header--scrolled');
            }
        }

        // Listen for the scroll event on the window
        window.addEventListener('scroll', handleScroll);
    }
}

//init frontend JS
new Frontend()