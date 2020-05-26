pingAction = require('./../omen/actionEvents.coffee').ping
ajaxCalls = require('./ajaxCalls.coffee')
lockUi = require('./lockUi.coffee')
trans = require('./translate.coffee')

timeOutPing = null
pingInterval = 1000 * 5 # ping every 30 sec

module.exports = ->
    timeoutFunction = ->
        ajaxCalls(
            pingAction.method,
            pingAction.url,
            null,
            ((data)->
                if $('#lostConnectionBanner').is(':visible')
                    lockUi.unlock()
                    $('#lostConnectionBanner').hide()
                timeOutPing = setTimeout timeoutFunction, pingInterval
            ),
            ((data)->
                timeOutPing = setTimeout timeoutFunction, pingInterval
                if data.readyState == 0
                    # Network error (i.e. connection refused, access denied due to CORS, etc.)
                    $('#lostConnectionBanner').show()
                    lockUi.lock()
                    return
                if data.status == 419
                    if confirm(trans('Session expired, do you want to extend it?'))
                        document.location.reload(true);
                    else
                        window.close()
                        if not window.closed then $('body').html("<h1>#{trans('Session expired')}</h1>")
            ),
        )
        return
    timeOutPing = setTimeout timeoutFunction, pingInterval