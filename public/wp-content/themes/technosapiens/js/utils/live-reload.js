(() => {
    const scriptTagId = "webpack-livereload-plugin-script"

    //bail if script already exists
    if (document.getElementById(scriptTagId)) return

    //create new script tag with live reload script
    const $newScriptEl = document.createElement("script")
    $newScriptEl.id = scriptTagId
    $newScriptEl.async = true
    $newScriptEl.src = `http://localhost:35729/livereload.js`

    //Add new script tag to the head
    document.getElementsByTagName("head")[0].appendChild($newScriptEl)
    console.log("[Live Reload] enabled")
})()