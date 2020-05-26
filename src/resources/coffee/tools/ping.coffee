pingAction = require('./../omen/actionEvents.coffee').ping
ajaxCalls = require('./ajaxCalls.coffee')
lockUi = require('./lockUi.coffee')
trans = require('./translate.coffee')

timeOutPing = null
pingInterval = 1000 * 30 # ping every 30 sec

module.exports = ->
    timeoutFunction = ->
        ajaxCalls(
            pingAction.method,
            pingAction.url,
            null,
            ((data)->
                timeOutPing = setTimeout timeoutFunction, pingInterval
            ),
            ((data)->
                if confirm(trans('Session expired, do you want to extend it?'))
                    document.location.reload(true);
                else
                    window.close()
                    if not window.closed then $('body').html("<h1>#{trans('Session expired')}</h1>")
            )
        )
        return
    timeOutPing = setTimeout timeoutFunction, pingInterval