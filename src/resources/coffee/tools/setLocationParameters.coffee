module.exports = (parameters)->

    url = window.location.href
    hash = window.location.hash
    url = url.replace(hash, '').replace('#', '')

    for paramName,paramValue of parameters
        if (url.indexOf(paramName + "=") >= 0)
            prefix = url.substring(0, url.indexOf(paramName + "="))
            suffix = url.substring(url.indexOf(paramName + "="))
            suffix = suffix.substring(suffix.indexOf("=") + 1)
            suffix = if (suffix.indexOf("&") >= 0) then suffix.substring(suffix.indexOf("&")) else ""
            url = prefix + paramName + "=" + paramValue + suffix
        else
            if (url.indexOf("?") < 0)
                url += "?" + paramName + "=" + paramValue
            else
                url += "&" + paramName + "=" + paramValue
    return url + hash
