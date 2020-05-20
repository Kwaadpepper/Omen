ajax = require('./../tools/ajaxCalls.coffee')
omenApi = require('./../omenApi.coffee')
getInodesAtPathActionInfo = require('./../omen/actionEvents.coffee').getInodesAtPath
getBreadcrumbAtPathActionInfo = require('./../omen/actionEvents.coffee').getBreadcrumbAtPath
setLocationParameters = require('./../tools/setLocationParameters.coffee')
logException = require('./../tools/logException.coffee')
ln = require('./../tools/getLine.coffee')

$inodeContainer = $('#inodesContainer')
$breadcrumbContainer = $('#viewInodes').children().first()

module.exports = (path)->

    inodes = null
    inodesHtml = null
    breadcrumbHtml = null

    inodesPromise = ajax(getInodesAtPathActionInfo.method, getInodesAtPathActionInfo.url, {
        path: path
    },
    ((data)->
        inodes = data.inodes
        inodesHtml = data.inodesHtml
    ),
    ((error)->
        # log error
        logException("Error Occured on ajax navigation  #{error.status} #{error.statusText} INODE => #{path} URL => #{getInodesAtPathActionInfo.url}", "9#{ln()}").done(->
            # fallback to redirection   
            window.location.replace(setLocationParameters({
                'path': encodeURIComponent(path)
            }))
        )

    ))

    breadcrumbPromise = ajax(getBreadcrumbAtPathActionInfo.method, getBreadcrumbAtPathActionInfo.url, {
        path: path
    },
    ((data)->
        breadcrumbHtml = data.breadcrumbHtml
    ),
    ((error)->
        # log error
        logException("Error Occured on ajax navigation  #{error.status} #{error.statusText} INODE => #{path} URL => #{getBreadcrumbAtPathActionInfo.url}", "9#{ln()}").done(->
            # fallback to redirection   
            window.location.replace(setLocationParameters({
                'path': encodeURIComponent(path)
            }))
        )

    ))

    $.when(inodesPromise, breadcrumbPromise).then(->
        omenApi.setProp('inodes', inodes)
        $inodeContainer.html(inodesHtml)
        $breadcrumbContainer.html(breadcrumbHtml)
        window.history.pushState("","", setLocationParameters({ 'path': encodeURIComponent(path) }));
    )
    