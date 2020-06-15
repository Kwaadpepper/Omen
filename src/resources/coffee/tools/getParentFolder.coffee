module.exports = (path)->

    if path.indexOf('/') is 0 then path = path.substr(1)
    path = path.split('/')
    path = path.splice(0, path.length - 1)
    path = if path.length then path.join('/') else '/'
    return "/#{path}"