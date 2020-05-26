window.config = require('./configGetter.coffee')
omenApiCSRFTokenName = config('omen.CSRFTokenKey')
csrfToken = $('meta[name="csrf-token"]').attr('content')

mutex = false
ajaxQueue = []

module.exports = (method, url, data, successClosure = null, errorClosure = null, moreParameters = null)->

    if typeof moreParameters is not 'object' then console.log 'OMEN error moreParameters must be an object on ' + url

    parameters = {
        method: method,
        url: url,
        data: data
    }

    if successClosure then parameters.success = successClosure
    if errorClosure then parameters.error = errorClosure
    

    if moreParameters
        for k,parameter of moreParameters
            parameters[k] = parameter
    
    omenApiCSRFToken = $("meta[name='#{omenApiCSRFTokenName}']").attr('content')

    # console.log 'sent token ',omenApiCSRFToken
       
    parameters['headers'] = {
        'X-CSRF-TOKEN': csrfToken,
        "#{omenApiCSRFTokenName}": omenApiCSRFToken
    }

    deferred = $.Deferred()
    promise = deferred.promise()

    completeCallback = (->)
    if typeof parameters['complete'] == 'function' then completeCallback = parameters['complete']
    parameters['complete'] = (xhr,status)->
        apiToken = xhr.getResponseHeader(omenApiCSRFTokenName)
        # console.log 'received token ',apiToken
        if apiToken then $("meta[name='#{omenApiCSRFTokenName}']").attr('content', apiToken)
        completeCallback.apply $, arguments
        if ajaxQueue.length
            opts = ajaxQueue.shift()
            # inject last token
            opts['headers']["#{omenApiCSRFTokenName}"] = $("meta[name='#{omenApiCSRFTokenName}']").attr('content')
            $.ajax(opts)
        else mutex = false
    
    successCallback = (->)
    if typeof parameters['success'] == 'function' then successCallback = parameters['success']
    parameters['success'] = ->
        # console.log 'success'
        deferred.resolve.apply $,arguments
        successCallback.apply $, arguments
    
    errorCallback = (->)
    if typeof parameters['error'] == 'function' then errorCallback = parameters['error']
    parameters['error'] = ->
        # console.log 'error'
        deferred.reject.apply $,arguments
        errorCallback.apply $, arguments 

    if not mutex
        $.ajax(parameters)
        mutex = true
    else ajaxQueue.push parameters
    
    promise