ajax = require('./../tools/ajaxCalls.coffee')
omenApi = require('./../omenApi.coffee')
getInodesAtPathActionInfo = require('./../omen/actionEvents.coffee').getInodesAtPath
getBreadcrumbAtPathActionInfo = require('./../omen/actionEvents.coffee').getBreadcrumbAtPath
setLocationParameters = require('./../tools/setLocationParameters.coffee')
logException = require('./../tools/logException.coffee')
Base64 = require('js-base64').Base64
fancyTree = require('jquery.fancytree')
resetFilters = require('./../omen/actionEvents.coffee').resetFilters
applySort = require('./../omen/actionEvents.coffee').applySort
fillNodeChilds = require('./../tools/fancyTreeFillNodeChilds.coffee')

breadcrumbRefresh = require('./../omen/breadcrumb.coffee')

$inodeContainer = $('#inodesContainer')
$breadcrumbContainer = $('#viewInodes').children().first()

progressbar = require('../tools/progressbar.coffee')

module.exports = (path)->

    inodes = null
    inodesHtml = null
    breadcrumbHtml = null

    progressbar.run(0.3)

    inodesPromise = ajax(getInodesAtPathActionInfo.method, getInodesAtPathActionInfo.url, {
        path: path
    },
    ((data)->
        inodes = data.inodes
        inodesHtml = data.inodesHtml
    ),
    ((error)->
        progressbar.end()
        # log error
        logException("Error Occured on ajax navigation  #{error.status} #{error.statusText} INODE => #{path} URL => #{getInodesAtPathActionInfo.url}").done(->
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
        progressbar.end()
        # log error
        logException("Error Occured on ajax navigation  #{error.status} #{error.statusText} INODE => #{path} URL => #{getBreadcrumbAtPathActionInfo.url}").done(->
            # fallback to redirection   
            window.location.replace(setLocationParameters({
                'path': encodeURIComponent(path)
            }))
        )

    ))

    $.when(inodesPromise, breadcrumbPromise).then(->
        progressbar.end()
        resetFilters()
        omenApi.setProp('inodes', inodes)
        $inodeContainer.html(inodesHtml)
        $breadcrumbContainer.html(breadcrumbHtml)
        window.history.pushState("","", setLocationParameters({ 'path': encodeURIComponent(path) }))
        applySort()
        breadcrumbRefresh()

        try
            ftree = fancyTree.getTree('#leftPanelTreeView')
            key = Base64.encode(path.replace(/\/?$/, ''))
            ftree.activateKey(key, { activeVisible: true})
            fNode = ftree.getNodeByKey(Base64.encode(path))
            fNode.makeVisible()
            if fNode.data.refType is 'directory' then fillNodeChilds(fNode)
        catch e
            console.error 'fTree navigation error',e
            
    )
    