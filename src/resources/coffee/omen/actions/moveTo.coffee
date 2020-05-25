ajaxCalls = require('./../../tools/ajaxCalls.coffee')
actionEvent = require('../actionEvents.coffee').moveTo

module.exports = (sourcePath, destPath)->
    deferred = $.Deferred()
    ajaxCalls(
        actionEvent.method,
        actionEvent.url,
        {
            sourcePath: sourcePath,
            destPath: destPath
        },
        ((data)->
            deferred.resolve(data.message)
        ),
        ((data)->
            deferred.reject(data.responseJSON.message)
        )
    )
    deferred