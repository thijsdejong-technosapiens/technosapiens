class MaterialInput {

    /**
     * Define CSS class for when input is active
     * @type {string}
     */
    CSS_CLASS_ACTIVE = 'material-input--active'

    /**
     * Define CSS class for when class is initiated
     * @type {string}
     */
    CSS_CLASS_INITIATED = 'material-input--initiated'

    /**
     * MaterialInput constructor
     * @param $containerElement {Element}
     * @param $labelElement {HTMLLabelElement}
     * @param $inputElement {HTMLInputElement|HTMLTextAreaElement}
     */
    constructor($containerElement = null, $labelElement = null, $inputElement = null) {
        if ($containerElement === null || !$containerElement instanceof Element) throw new Error('Missing required parameter `$containerElement` in class MaterialInput or given parameter was not an Element.')
        if ($labelElement === null || !$labelElement instanceof HTMLLabelElement) throw new Error('Missing required parameter `$labelElement` in class MaterialInput or given parameter was not an HTMLLabelElement.')
        if ($inputElement === null || (!$inputElement instanceof HTMLInputElement || !$inputElement instanceof HTMLTextAreaElement)) throw new Error('Missing required parameter `$inputElement` in class MaterialInput or given parameter was not an HTMLInputElement or HTMLTextAreaElement.')

        //bail if class is already initiated
        if ($containerElement.classList.contains(this.CSS_CLASS_INITIATED)) return

        this.state = {
            $containerElement: $containerElement,
            $inputElement: $inputElement,
            $labelElement: $labelElement,
            value: $inputElement.value,
            active: $inputElement.value.length > 0
        }

        //open if input has a default value
        if (this.state.active === true) this.activate()

        //handle input events
        this.state.$inputElement.addEventListener('change', this.handleChange.bind(this))
        this.state.$inputElement.addEventListener('focus', this.handleFocus.bind(this))
        this.state.$inputElement.addEventListener('blur', this.handleBlur.bind(this))

        //add initiated css class
        this.state.$containerElement.classList.add(this.CSS_CLASS_INITIATED)
    }

    /**
     * Update value in state
     * @param event
     */
    handleChange(event) {
        this.state.value = event.currentTarget.value
    }

    /**
     * Handle input focus
     */
    handleFocus() {
        this.activate()
    }

    /**
     * Handle input blur
     */
    handleBlur() {
        if (this.state.$inputElement.value === '') this.deactivate()
    }

    /**
     * Active material input
     */
    activate() {
        this.state.active = true
        this.state.$containerElement.classList.add(this.CSS_CLASS_ACTIVE)
    }

    /**
     * Deactivate material input
     */
    deactivate() {
        this.state.active = false
        this.state.$containerElement.classList.remove(this.CSS_CLASS_ACTIVE)
    }
}

export default MaterialInput