ajaxCalls = require './ajaxCalls.coffee'
config = require('./../omenApi.coffee').config

module.exports = (message, code)->
    deferred = $.Deferred()
    console.error "OMEN Error #{code} :  #{message}"
    ajaxCalls(
        'POST',
        config('omen.urlPrefix') + '/log',
        {
            code: code,
            message: message
        },
        null,
        null,
        {
            complete: ->
                deferred.resolve()
        }
    )
    return deferred
