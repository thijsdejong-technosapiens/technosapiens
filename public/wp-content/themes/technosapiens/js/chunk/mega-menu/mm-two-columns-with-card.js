window.addEventListener('ts-mega-menu:open', (event) => {
    if (event.detail.preset !== 'two-columns-with-card') return

    const $tcwcMegaMenuElements = Array.from(document.querySelectorAll('.mega-menu-navigation--preset-two-columns-with-card'))

    if ($tcwcMegaMenuElements.length > 0) {
        import(/* webpackChunkName: "mm-two-columns-with-card-class" */ './classes/TwoColumnsWithCardPreset.js').then(({default: TwoColumnsWithCardPreset}) => {
            $tcwcMegaMenuElements.forEach($tcwcMegaMenuElement => new TwoColumnsWithCardPreset($tcwcMegaMenuElement))
        }).catch((error) => console.error('Error loading TwoColumnsWithCardPreset class:', error))
    }
})
