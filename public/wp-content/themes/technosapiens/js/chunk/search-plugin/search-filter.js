const $filterSelect = document.querySelector('.archive-filters__select')

if ($filterSelect) {
    $filterSelect.addEventListener('change', () => {
        const currentUrl = window.location.href
        const selectedUrl = $filterSelect.value

        if (currentUrl !== selectedUrl) {
            window.location.href = selectedUrl
        }
    })
}
