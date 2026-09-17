/**
 * Prevent clicking on links in gutenberg preview with "ts-preview-link" CSS class
 */
const preventPreviewLinkClicks = () => {
    const $tsPreviewLinkElements = [
        ...Array.from(document.querySelectorAll('.ts-preview-link')),
        ...Array.from(document.querySelectorAll('.ts-preview-link a'))
    ]

    if ($tsPreviewLinkElements && $tsPreviewLinkElements.length > 0) {
        $tsPreviewLinkElements.forEach(($tsPreviewLinkElement) => {
            if (!$tsPreviewLinkElement.dataset.tsPreventClick) {
                $tsPreviewLinkElement.dataset.tsPreventClick = 'true'
                $tsPreviewLinkElement.addEventListener('click', (event) => {
                    if (event) event.preventDefault()
                })
            }
        })
    }
}

/**
 * Initialize admin scripts when DOM is ready
 */
const initAdminScripts = () => {
    const isGutenberg = document.body?.classList?.contains('block-editor-page') ?? false
    const isGutenbergIframe = document.body?.classList?.contains('block-editor-iframe__body') ?? false

    if (isGutenberg || isGutenbergIframe) {
        if (typeof window.acf !== 'undefined') {
            window.acf.addAction('render_block_preview', preventPreviewLinkClicks)
        }
    }

    if (isGutenbergIframe) {
        //use MutationObserver for iframe context (block patterns)
        const observer = new MutationObserver(preventPreviewLinkClicks)
        observer.observe(document.body, { childList: true, subtree: true })

        //also run on initial load
        preventPreviewLinkClicks()
    }
}

//wait for DOM to be ready before checking body classes
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminScripts)
} else {
    initAdminScripts()
}