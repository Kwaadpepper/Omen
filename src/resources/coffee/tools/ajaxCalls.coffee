csrfToken = $('meta[name="csrf-token"]').attr('content')

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
    
    $.ajaxSetup {
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    }
    $.ajax(parameters)