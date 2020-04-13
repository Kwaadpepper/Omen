translations = window.__omenTransalations

module.exports = (string, vars)->
    if typeof string is not 'string' then string = string.toString()
    out = translations[string] ? string
    if typeof vars is 'object'
        for key,variable of vars
            out = out.replace("${#{key}}", variable)
    return out