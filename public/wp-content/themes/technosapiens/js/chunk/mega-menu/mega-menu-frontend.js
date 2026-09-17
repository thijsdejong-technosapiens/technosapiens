const $megaMenuElements = Array.from(document.querySelectorAll('.mega-menu-navigation'))

if ($megaMenuElements.length > 0) {
    import(/* webpackChunkName: "mega-menu" */ './classes/MegaMenu.js').then(({default: MegaMenu}) => {
        $megaMenuElements.forEach($megaMenuElement => new MegaMenu($megaMenuElement))
    }).catch((error) => console.log('Something went wrong while importing mega-menu chunk..', error))
}
