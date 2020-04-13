laravelData = require './laravelDataParser.coffee'

module.exports = (paramPath, defaultValue)->
    config = {
        omen: laravelData.config
    };
    recursiveAccess = (config, path)->
        topPath = path.substring(0, path.indexOf('.'))
        return null if typeof config[topPath] is undefined
        if path.indexOf('.') isnt -1
            return recursiveAccess config[topPath], path.substring(path.indexOf('.') + 1)
        return config[path]

    return recursiveAccess(config, paramPath) ? defaultValue