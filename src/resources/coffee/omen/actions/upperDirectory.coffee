getUrlLocationParameter = require('./../../tools/getUrlLocationParameter.coffee')
setLocationParameters = require('./../../tools/setLocationParameters.coffee')
getParentFolder = require('./../../tools/getParentFolder.coffee')

module.exports = (action)->
    (event)->

        # got to parent Folder
        window.location.replace(setLocationParameters({
            'path': encodeURIComponent(getParentFolder(decodeURIComponent(getUrlLocationParameter('path'))))
        }))

        return false