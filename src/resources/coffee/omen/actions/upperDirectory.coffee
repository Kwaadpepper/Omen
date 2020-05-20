ajaxNavigation =require('./../../tools/ajaxNavigation.coffee')
getUrlLocationParameter = require('./../../tools/getUrlLocationParameter.coffee')
getParentFolder = require('./../../tools/getParentFolder.coffee')

module.exports = ->
    (e)->
        ajaxNavigation(getParentFolder(decodeURIComponent(getUrlLocationParameter('path'))))