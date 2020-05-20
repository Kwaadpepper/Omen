omenApi = require('./../../omenApi.coffee')
setLocationParameters = require('./../../tools/setLocationParameters.coffee')

actionEvents = require('./../actionEvents.coffee')
ajaxNavigateTo = require('./../../tools/ajaxNavigation.coffee')

getParentFolder = require('./../../tools/getParentFolder.coffee')
getUrlLocationParameter = require('./../../tools/getUrlLocationParameter.coffee')

module.exports = (action)->
    (event)->

        figure = $(this).parents('figure')
        fileBase64FullPath = figure.data('path')
        inodes = omenApi.getProp('inodes')
        inode = inodes[fileBase64FullPath]

        # if directory is Parent
        if(figure.hasClass('Root'))
            # got To parent
            # require('./upperDirectory.coffee')()()
            ajaxNavigateTo(getParentFolder(decodeURIComponent(getUrlLocationParameter('path'))))
            return false

        switch inode.type
            when 'directory'
                ajaxNavigateTo(inode.path)
            else
                # if file Type view is supported
                if ['image', 'text', 'pdf', 'writer', 'calc', 'impress', 'video', 'audio'].indexOf(inode.fileType) != -1
                    # file action view
                    figure.find('button.actionView').trigger('click')
                else
                    # file download
                    figure.find('button.actionDownload').trigger('click')


        return false