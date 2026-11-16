/**
 * Section FAQ
 * Progressive enhancement for collapsible FAQ items with height transition.
 */
class SectionFaq {
    QUESTION_SELECTOR = '.section-faq__question'
    OPEN_CLASS = 'is-open'

    /**
     * @param {Element} $section
     */
    constructor($section) {
        if (!($section instanceof Element)) {
            throw new Error('Missing required parameter `$section` in SectionFaq')
        }

        this.state = {
            $section,
            $questions: Array.from($section.querySelectorAll(this.QUESTION_SELECTOR)),
            prefersReducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
            animatingAnswers: new WeakMap(),
        }

        this.bindEvents()
    }

    bindEvents() {
        this.state.$questions.forEach(($question) => {
            if (!($question instanceof HTMLButtonElement)) {
                return
            }

            $question.addEventListener('click', () => {
                this.toggleQuestion($question)
            })
        })
    }

    /**
     * @param {HTMLButtonElement} $question
     */
    toggleQuestion($question) {
        const controlsId = $question.getAttribute('aria-controls')
        if (!controlsId) {
            return
        }

        const $answer = this.state.$section.querySelector(`#${CSS.escape(controlsId)}`)
        if (!($answer instanceof HTMLElement)) {
            return
        }

        const isExpanded = $question.getAttribute('aria-expanded') === 'true'

        this.stopAnswerAnimation($answer)

        if (isExpanded) {
            this.closeAnswer($question, $answer)
            return
        }

        this.openAnswer($question, $answer)
    }

    /**
     * @param {HTMLElement} $answer
     */
    stopAnswerAnimation($answer) {
        const previous = this.state.animatingAnswers.get($answer)
        if (!previous) {
            return
        }

        $answer.removeEventListener('transitionend', previous.onEnd)
        window.cancelAnimationFrame(previous.frameId)
        this.state.animatingAnswers.delete($answer)
    }

    /**
     * @param {HTMLButtonElement} $question
     * @param {HTMLElement} $answer
     */
    openAnswer($question, $answer) {
        $question.setAttribute('aria-expanded', 'true')
        $answer.removeAttribute('hidden')

        if (this.state.prefersReducedMotion) {
            $answer.style.height = ''
            $answer.classList.add(this.OPEN_CLASS)
            return
        }

        const startHeight = $answer.getBoundingClientRect().height
        $answer.classList.remove(this.OPEN_CLASS)
        $answer.style.height = `${startHeight}px`
        // Force layout so the browser registers the starting height.
        $answer.getBoundingClientRect()

        const targetHeight = $answer.scrollHeight
        const onEnd = (event) => {
            if (event.target !== $answer || event.propertyName !== 'height') {
                return
            }

            this.finishOpen($answer, onEnd)
        }

        const frameId = window.requestAnimationFrame(() => {
            $answer.style.height = `${targetHeight}px`
        })

        this.state.animatingAnswers.set($answer, { onEnd, frameId })
        $answer.addEventListener('transitionend', onEnd)
    }

    /**
     * @param {HTMLElement} $answer
     * @param {(event: TransitionEvent) => void} onEnd
     */
    finishOpen($answer, onEnd) {
        $answer.removeEventListener('transitionend', onEnd)
        this.state.animatingAnswers.delete($answer)
        $answer.classList.add(this.OPEN_CLASS)
        $answer.style.height = ''
    }

    /**
     * @param {HTMLButtonElement} $question
     * @param {HTMLElement} $answer
     */
    closeAnswer($question, $answer) {
        $question.setAttribute('aria-expanded', 'false')

        if (this.state.prefersReducedMotion) {
            $answer.classList.remove(this.OPEN_CLASS)
            $answer.style.height = ''
            $answer.setAttribute('hidden', '')
            return
        }

        // Lock current visible height before leaving height:auto / mid-animation.
        const currentHeight = $answer.getBoundingClientRect().height
        $answer.style.height = `${currentHeight}px`
        $answer.classList.remove(this.OPEN_CLASS)
        // Force layout so the fixed px height is applied before animating to 0.
        $answer.getBoundingClientRect()

        const onEnd = (event) => {
            if (event.target !== $answer || event.propertyName !== 'height') {
                return
            }

            this.finishClose($answer, onEnd)
        }

        const frameId = window.requestAnimationFrame(() => {
            $answer.style.height = '0px'
        })

        this.state.animatingAnswers.set($answer, { onEnd, frameId })
        $answer.addEventListener('transitionend', onEnd)
    }

    /**
     * @param {HTMLElement} $answer
     * @param {(event: TransitionEvent) => void} onEnd
     */
    finishClose($answer, onEnd) {
        $answer.removeEventListener('transitionend', onEnd)
        this.state.animatingAnswers.delete($answer)
        $answer.style.height = ''
        $answer.setAttribute('hidden', '')
    }
}

document.addEventListener('DOMContentLoaded', () => {
    Array.from(document.querySelectorAll('.section-faq--collapsible')).forEach(($section) => {
        new SectionFaq($section)
    })
})

export default SectionFaq
