ajaxCalls = require './ajaxCalls.coffee'
config = require('./../omenApi.coffee').config
Base64 = require('js-base64').Base64
ln = require('./getLine.coffee')

module.exports = (message)->
    line = ln(true)
    if typeof message != 'string'
        line = message.lineNumber 
        message = "#{message.message} line #{line}"
    length = "FRONT#{line}".length
    code = Base64.encode("FRONT#{("000000"+line).slice(-7)}")
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
