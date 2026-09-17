import Dialog from './lib/Dialog'

Array.from(document.querySelectorAll('.ts-dialog')).forEach($dialogElement => {
    new Dialog($dialogElement)
})
