module.exports = (paramName)->

    vars = {}
    parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, (m,key,value)->
        vars[key] = value
    )
    return vars[paramName] ? ''
