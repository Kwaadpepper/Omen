ajaxCalls = require('./../../tools/ajaxCalls.coffee')
actionEvent = require('../actionEvents.coffee').copyTo

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
            deferred.resolve(data.inode)
        ),
        ((data)->
            deferred.reject(data.responseJSON.message)
        )
    )
    deferred