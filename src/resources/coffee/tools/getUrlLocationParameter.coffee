module.exports = (paramName, url = null)->

    url = if url is null then window.location.href else url
    vars = {}
    parts = url.replace(/[?&]+([^=&]+)=([^&]*)/gi, (m,key,value)->
        vars[key] = value
    )
    return vars[paramName] ? ''
