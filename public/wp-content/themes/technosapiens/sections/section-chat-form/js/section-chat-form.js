/**
 * SectionChatForm
 * Progressive enhancement for the Gravity Forms chatbot UI.
 */
class SectionChatForm {
    SECTION_CLASS = 'section-chat-form'
    ENHANCED_CLASS = 'section-chat-form--enhanced'
    SUBMIT_ICON_CLASS = 'section-chat-form__submit-icon'

    MESSAGE_SELECTOR = '.ts-chat-message'
    NAME_SELECTOR = '.ts-chat-name'
    EMAIL_SELECTOR = '.ts-chat-email'
    TOPIC_FIELD_SELECTOR = '.ts-chat-topic'
    TOPIC_BUTTON_SELECTOR = '.section-chat-form__topic'
    MIC_SELECTOR = '.section-chat-form__mic'
    HINT_SELECTOR = '.section-chat-form__hint'
    MIC_LIVE_SELECTOR = '[id^="section-chat-form-mic-"]'
    SUBMIT_SELECTOR = '.gform_button, .gform-button, button[type="submit"]'

    /**
     * @param {Element} $section
     */
    constructor($section) {
        if (!($section instanceof Element)) {
            throw new Error('Missing required parameter `$section` in SectionChatForm')
        }

        this.state = {
            $section,
            $formWrap: $section.querySelector('.section-chat-form__form'),
            $mic: $section.querySelector(this.MIC_SELECTOR),
            $hint: $section.querySelector(this.HINT_SELECTOR),
            $micLive: $section.querySelector(this.MIC_LIVE_SELECTOR),
            $topicButtons: Array.from($section.querySelectorAll(this.TOPIC_BUTTON_SELECTOR)),
            $messageField: null,
            $nameField: null,
            $emailField: null,
            $topicField: null,
            $messageInput: null,
            $nameInput: null,
            $emailInput: null,
            $topicInput: null,
            $submit: null,
            selectedTopicSlug: '',
            recognition: null,
            isListening: false
        }

        this.init()
    }

    /**
     * Enable enhanced mode and bind form behaviour.
     */
    init() {
        this.state.$section.classList.add(this.ENHANCED_CLASS)
        this.bindForm()
        this.initTopics()
        this.initMic()
        this.bindGformPostRender()
    }

    /**
     * Re-query GF nodes and attach listeners after render / AJAX validation.
     */
    bindForm() {
        const {$formWrap} = this.state

        if (!($formWrap instanceof Element)) {
            return
        }

        this.state.$messageField = $formWrap.querySelector(this.MESSAGE_SELECTOR)
        this.state.$nameField = $formWrap.querySelector(this.NAME_SELECTOR)
        this.state.$emailField = $formWrap.querySelector(this.EMAIL_SELECTOR)
        this.state.$topicField = $formWrap.querySelector(this.TOPIC_FIELD_SELECTOR)
        this.state.$messageInput = this.getFieldControl(this.state.$messageField)
        this.state.$nameInput = this.getFieldControl(this.state.$nameField)
        this.state.$emailInput = this.getFieldControl(this.state.$emailField)
        this.state.$topicInput = this.getFieldControl(this.state.$topicField)
        this.state.$submit = $formWrap.querySelector(this.SUBMIT_SELECTOR)

        this.decorateSubmitButton()
        this.syncSubmitGate()
        this.syncTopicField()
        this.addFieldListeners()
    }

    /**
     * @param {Element|null} $field
     * @returns {HTMLInputElement|HTMLTextAreaElement|null}
     */
    getFieldControl($field) {
        if (!($field instanceof Element)) {
            return null
        }

        const $control = $field.querySelector('textarea, input:not([type="hidden"])')
            || $field.querySelector('input[type="hidden"]')

        if ($control instanceof HTMLInputElement || $control instanceof HTMLTextAreaElement) {
            return $control
        }

        return null
    }

    /**
     * Hide GF submit label visually and inject send icon once.
     */
    decorateSubmitButton() {
        const {$submit} = this.state

        if (!($submit instanceof HTMLElement)) {
            return
        }

        if ($submit.querySelector(`.${this.SUBMIT_ICON_CLASS}`)) {
            return
        }

        const $icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg')
        $icon.setAttribute('class', this.SUBMIT_ICON_CLASS)
        $icon.setAttribute('viewBox', '0 0 24 24')
        $icon.setAttribute('fill', 'none')
        $icon.setAttribute('stroke', 'currentColor')
        $icon.setAttribute('stroke-width', '2')
        $icon.setAttribute('stroke-linecap', 'round')
        $icon.setAttribute('stroke-linejoin', 'round')
        $icon.setAttribute('aria-hidden', 'true')
        $icon.setAttribute('focusable', 'false')

        const $path = document.createElementNS('http://www.w3.org/2000/svg', 'path')
        $path.setAttribute('d', 'M22 2L11 13')
        $icon.appendChild($path)

        const $pathTwo = document.createElementNS('http://www.w3.org/2000/svg', 'path')
        $pathTwo.setAttribute('d', 'M22 2L15 22L11 13L2 9L22 2Z')
        $icon.appendChild($pathTwo)

        $submit.appendChild($icon)
    }

    /**
     * Bind input listeners once per control instance.
     */
    addFieldListeners() {
        const {$messageInput, $nameInput, $emailInput} = this.state

        if ($messageInput && !$messageInput.dataset.tsChatBound) {
            $messageInput.dataset.tsChatBound = '1'
            $messageInput.addEventListener('input', this.handleMessageInput.bind(this))
        }

        if ($nameInput && !$nameInput.dataset.tsChatBound) {
            $nameInput.dataset.tsChatBound = '1'
            $nameInput.addEventListener('input', this.handleContactInput.bind(this))
        }

        if ($emailInput && !$emailInput.dataset.tsChatBound) {
            $emailInput.dataset.tsChatBound = '1'
            $emailInput.addEventListener('input', this.handleContactInput.bind(this))
            $emailInput.addEventListener('blur', this.handleEmailBlur.bind(this))
        }
    }

    /**
     * @param {Event} event
     */
    handleMessageInput(event) {
        this.syncSubmitGate()
    }

    /**
     * @param {Event} event
     */
    handleContactInput(event) {
        this.syncSubmitGate()
    }

    /**
     * Set aria-invalid on email only after blur when invalid.
     */
    handleEmailBlur() {
        const {$emailInput} = this.state

        if (!($emailInput instanceof HTMLInputElement)) {
            return
        }

        const value = $emailInput.value.trim()

        if (!value) {
            $emailInput.removeAttribute('aria-invalid')
            return
        }

        if (this.isValidEmail(value)) {
            $emailInput.removeAttribute('aria-invalid')
            return
        }

        $emailInput.setAttribute('aria-invalid', 'true')
    }

    /**
     * Disable submit until message, name, and email are valid.
     * Name/email may be GF-hidden via conditional logic until the message is filled.
     */
    syncSubmitGate() {
        const {$submit, $hint, $messageInput, $nameInput, $emailInput, $nameField, $emailField} = this.state

        if (!($submit instanceof HTMLButtonElement) && !($submit instanceof HTMLInputElement)) {
            return
        }

        const messageOk = Boolean($messageInput && $messageInput.value.trim().length > 0)
        const nameVisible = this.isFieldVisiblyRequired($nameField)
        const emailVisible = this.isFieldVisiblyRequired($emailField)
        const nameOk = !nameVisible || Boolean($nameInput && $nameInput.value.trim().length > 0)
        const emailValue = $emailInput ? $emailInput.value.trim() : ''
        const emailOk = !emailVisible || this.isValidEmail(emailValue)
        const isValid = messageOk && nameOk && emailOk

        $submit.disabled = !isValid

        if ($hint instanceof HTMLElement) {
            if (isValid) {
                $hint.setAttribute('hidden', '')
                $submit.removeAttribute('aria-describedby')
            } else {
                $hint.removeAttribute('hidden')

                if ($hint.id) {
                    $submit.setAttribute('aria-describedby', $hint.id)
                }
            }
        }
    }

    /**
     * @param {Element|null} $field
     * @returns {boolean}
     */
    isFieldVisiblyRequired($field) {
        if (!($field instanceof HTMLElement)) {
            return false
        }

        if ($field.hasAttribute('hidden') || $field.classList.contains('gfield_visibility_hidden')) {
            return false
        }

        return $field.getClientRects().length > 0
    }

    /**
     * @param {string} value
     * @returns {boolean}
     */
    isValidEmail(value) {
        if (!value) {
            return false
        }

        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
    }

    /**
     * Topic pills: aria-pressed + optional ?topic= allowlist.
     */
    initTopics() {
        this.state.$topicButtons.forEach(($button) => {
            $button.addEventListener('click', this.handleTopicClick.bind(this, $button))
        })

        const topicFromUrl = new URLSearchParams(window.location.search).get('topic')

        if (!topicFromUrl) {
            return
        }

        const $match = this.state.$topicButtons.find(($button) => {
            return $button.getAttribute('data-topic-slug') === topicFromUrl
        })

        if ($match) {
            this.selectTopic($match)
        }
    }

    /**
     * @param {HTMLElement} $button
     * @param {Event} event
     */
    handleTopicClick($button, event) {
        event.preventDefault()

        const isPressed = $button.getAttribute('aria-pressed') === 'true'

        if (isPressed) {
            this.clearTopicSelection()
            return
        }

        this.selectTopic($button)
    }

    /**
     * @param {HTMLElement} $button
     */
    selectTopic($button) {
        this.state.$topicButtons.forEach(($topicButton) => {
            $topicButton.setAttribute('aria-pressed', $topicButton === $button ? 'true' : 'false')
        })

        this.state.selectedTopicSlug = $button.getAttribute('data-topic-slug') || ''
        this.syncTopicField()
    }

    /**
     * Deselect all topic pills and clear the hidden field.
     */
    clearTopicSelection() {
        this.state.$topicButtons.forEach(($topicButton) => {
            $topicButton.setAttribute('aria-pressed', 'false')
        })

        this.state.selectedTopicSlug = ''
        this.syncTopicField()
    }

    /**
     * Write the selected topic label into the optional GF topic field.
     */
    syncTopicField() {
        const {$topicInput, $topicButtons, selectedTopicSlug} = this.state

        if (!($topicInput instanceof HTMLInputElement || $topicInput instanceof HTMLTextAreaElement)) {
            return
        }

        if (!selectedTopicSlug) {
            $topicInput.value = ''
            $topicInput.dispatchEvent(new Event('input', {bubbles: true}))
            return
        }

        const $selected = $topicButtons.find(($button) => {
            return $button.getAttribute('data-topic-slug') === selectedTopicSlug
        })

        const label = $selected ? ($selected.getAttribute('data-topic-label') || '') : ''
        $topicInput.value = label
        $topicInput.dispatchEvent(new Event('input', {bubbles: true}))
    }

    /**
     * Web Speech mic — hidden when unsupported.
     */
    initMic() {
        const {$mic} = this.state
        const SpeechRecognitionApi = window.SpeechRecognition || window.webkitSpeechRecognition

        if (!($mic instanceof HTMLElement) || !SpeechRecognitionApi) {
            if ($mic instanceof HTMLElement) {
                $mic.setAttribute('hidden', '')
            }

            return
        }

        const recognition = new SpeechRecognitionApi()
        recognition.continuous = false
        recognition.interimResults = false
        recognition.lang = document.documentElement.lang || 'nl-NL'

        recognition.addEventListener('start', this.handleSpeechStart.bind(this))
        recognition.addEventListener('end', this.handleSpeechEnd.bind(this))
        recognition.addEventListener('error', this.handleSpeechError.bind(this))
        recognition.addEventListener('result', this.handleSpeechResult.bind(this))

        this.state.recognition = recognition
        $mic.removeAttribute('hidden')
        $mic.addEventListener('click', this.handleMicClick.bind(this))
    }

    /**
     * @param {Event} event
     */
    handleMicClick(event) {
        event.preventDefault()

        const {recognition, isListening} = this.state

        if (!recognition) {
            return
        }

        if (isListening) {
            recognition.stop()
            return
        }

        try {
            recognition.start()
        } catch (error) {
            this.setMicStatus('Kon microfoon niet starten.')
        }
    }

    handleSpeechStart() {
        this.state.isListening = true

        if (this.state.$mic) {
            this.state.$mic.setAttribute('aria-pressed', 'true')
        }

        this.setMicStatus('Luisteren...')
    }

    handleSpeechEnd() {
        this.state.isListening = false

        if (this.state.$mic) {
            this.state.$mic.setAttribute('aria-pressed', 'false')
        }

        this.setMicStatus('Gestopt')
    }

    /**
     * @param {SpeechRecognitionErrorEvent} event
     */
    handleSpeechError(event) {
        this.state.isListening = false

        if (this.state.$mic) {
            this.state.$mic.setAttribute('aria-pressed', 'false')
        }

        const errorCode = event && event.error ? event.error : ''

        if (errorCode === 'not-allowed') {
            this.setMicStatus('Geen toestemming voor de microfoon.')
            return
        }

        if (errorCode === 'network') {
            this.setMicStatus('Geen netwerk voor spraakherkenning.')
            return
        }

        if (errorCode === 'aborted' || errorCode === 'no-speech') {
            this.setMicStatus('Gestopt')
            return
        }

        this.setMicStatus('Spraakherkenning mislukt.')
    }

    /**
     * @param {SpeechRecognitionEvent} event
     */
    handleSpeechResult(event) {
        const {$messageInput} = this.state

        if (!($messageInput instanceof HTMLTextAreaElement || $messageInput instanceof HTMLInputElement)) {
            return
        }

        const transcript = Array.from(event.results || [])
            .map((result) => (result[0] && result[0].transcript) ? result[0].transcript : '')
            .join(' ')
            .trim()

        if (!transcript) {
            return
        }

        const current = $messageInput.value
        const needsSpace = current.length > 0 && !/\s$/.test(current)
        $messageInput.value = current + (needsSpace ? ' ' : '') + transcript
        $messageInput.dispatchEvent(new Event('input', {bubbles: true}))
    }

    /**
     * @param {string} message
     */
    setMicStatus(message) {
        if (this.state.$micLive) {
            this.state.$micLive.textContent = message
        }
    }

    /**
     * Re-bind after GF AJAX validation re-renders the form.
     */
    bindGformPostRender() {
        if (typeof window.jQuery === 'undefined') {
            return
        }

        window.jQuery(document).on('gform_post_render', () => {
            if (!(this.state.$formWrap instanceof Element)) {
                return
            }

            if (!this.state.$formWrap.querySelector('form')) {
                return
            }

            this.bindForm()
        })
    }
}

document.addEventListener('DOMContentLoaded', () => {
    Array.from(document.querySelectorAll('.section-chat-form')).forEach(($section) => {
        if ($section.querySelector('.section-chat-form__form')) {
            new SectionChatForm($section)
        }
    })
})

export default SectionChatForm
