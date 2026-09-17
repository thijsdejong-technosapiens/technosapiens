const isSafari = () => /^((?!chrome|android).)*safari/i.test(navigator.userAgent)

const blockScroll = () => {
    document.documentElement.style.overflowY = "hidden"
    if (isSafari() === true) document.body.style.position = "fixed"
}

const unBlockScroll = () => {
    document.documentElement.style.overflowY = "auto"
    if (isSafari() === true) document.body.style.position = "relative"
}

export {blockScroll, unBlockScroll}