module.exports = (cssSelector, handler)->
    element = $(cssSelector).get(0)
    # Standard syntax
    element.addEventListener 'fullscreenchange',handler

    # Firefox
    element.addEventListener 'mozfullscreenchange', handler

    # Chrome, Safari and Opera
    element.addEventListener 'webkitfullscreenchange',handler

    # IE / Edge
    element.addEventListener 'msfullscreenchange', handler