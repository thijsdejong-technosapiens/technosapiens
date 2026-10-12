import {gsap} from 'gsap'
import {ScrollTrigger} from 'gsap/ScrollTrigger'
import {SplitText} from 'gsap/SplitText'

gsap.registerPlugin(ScrollTrigger, SplitText)

/**
 * class TextColorScrub
 * Splits colored text spans into characters and scrubs each character from cream to the span's own color.
 * Spans in the same sentence animate serially in document order; separate sentences animate in parallel,
 * each scrubbed against its parent section (or the main hero).
 */
class TextColorScrub {

    TARGET_SELECTOR = '.text-color-primary, .text-color-secondary, .text-color-tertiary'
    SENTENCE_SELECTOR = 'h1, h2, h3, h4, h5, h6, p, li, blockquote, figcaption'
    TRIGGER_SELECTOR = '.section, .hm-main-hero'
    EXCLUDED_SELECTOR = '#ts-footer, .main-navigation, .side-navigation'
    START_COLOR_VARIABLE = '--ts-font-color-cream'
    START_COLOR_FALLBACK = 'ivory'
    SCRUB_START = 'top 75%'
    SCRUB_END = 'top 40%'
    STAGGER = 0.05

    /**
     * @param {NodeListOf<Element>|Element[]} $targets
     */
    constructor($targets) {
        this.state = {
            startColor: this.getStartColor(),
            endColors: new Map(),
            splits: []
        }

        this.groupBySentence($targets).forEach(($group) => this.initGroup($group))
    }

    /**
     * @returns {string}
     */
    getStartColor() {
        const value = getComputedStyle(document.documentElement).getPropertyValue(this.START_COLOR_VARIABLE).trim()
        return value || this.START_COLOR_FALLBACK
    }

    /**
     * Group spans by their sentence element so they share one serial timeline
     * @param {NodeListOf<Element>|Element[]} $targets
     * @returns {Element[][]}
     */
    groupBySentence($targets) {
        const groups = new Map()

        Array.from($targets || []).forEach(($target) => {
            if ($target.closest(this.EXCLUDED_SELECTOR)) return
            if (!$target.closest(this.TRIGGER_SELECTOR)) return

            const $sentence = $target.closest(this.SENTENCE_SELECTOR) || $target.parentElement
            if (!groups.has($sentence)) groups.set($sentence, [])
            groups.get($sentence).push($target)
        })

        return Array.from(groups.values())
    }

    /**
     * @param {Element[]} $group
     */
    initGroup($group) {
        const $trigger = $group[0].closest(this.TRIGGER_SELECTOR)

        // Read before splitting: the class color is the scrub end state, chars only get cream inline.
        $group.forEach(($target) => this.state.endColors.set($target, getComputedStyle($target).color))

        const split = SplitText.create($group, {
            type: 'words, chars',
            autoSplit: true,
            onSplit: (self) => gsap.fromTo(self.chars, {
                color: this.state.startColor
            }, {
                color: (index, $char) => this.state.endColors.get($char.closest(this.TARGET_SELECTOR)),
                ease: 'none',
                stagger: this.STAGGER,
                scrollTrigger: {
                    trigger: $trigger,
                    start: this.SCRUB_START,
                    end: this.SCRUB_END,
                    scrub: true
                }
            })
        })

        this.state.splits.push(split)
    }
}

export default TextColorScrub
