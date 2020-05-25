omenApi = require('./../../omenApi.coffee')
clipboard = require('./../../tools/clipboard.coffee')
getUrlLocationParameter = require('./../../tools/getUrlLocationParameter.coffee')
setLocationParameters = require('./../../tools/setLocationParameters.coffee')
logException = require('./../../tools/logException.coffee')
ln = require('./../../tools/getLine.coffee')
moveTo = require('./moveTo.coffee')
copyTo = require('./copyTo.coffee')
trans = require('./../../tools/translate.coffee')
alert = require('./../../tools/alert.coffee')
Base64 = require('js-base64').Base64
addInodeFigure = require('./addInodeFigure.coffee')


module.exports = ->
    destination = decodeURIComponent(getUrlLocationParameter('path'))
    inodes = clipboard.get()
    if inodes.length
        console.log 'destination ', destination, ' action ', clipboard.getAction(), ' items ', inodes
        switch clipboard.getAction()
            when 'copy'
                operations = []
                for inode,k in inodes
                    operations.push copyTo(inode.path, destination)
                $.when.apply($,operations).done(->
                    if destination == decodeURIComponent(getUrlLocationParameter('path'))
                        locationInodes = omenApi.getProp('inodes')
                        for k,updatedInode of arguments
                            console.log updatedInode
                            addInodeFigure(updatedInode, updatedInode.type == 'directory').then(->
                                locationInodes[Base64.encode(updatedInode.fullPath)] = updatedInode
                                omenApi.setProp('inodes', locationInodes)
                            ,(data)->
                                # if failed
                                logException("Error Occured on paste  #{data.status} #{data.statusText} INODE => #{updatedInode.path}", "9#{ln()}").done(->
                                # fallback to redirection   
                                    window.location.replace(setLocationParameters({
                                        'path': getUrlLocationParameter('path')
                                    }))
                                )
                            )
                        alert('success', trans('Copied'), trans("Element was copied successfully"))  
                ).fail((message)->
                    if typeof message != 'undefined' and message.length
                        alert('danger', trans('Copy failed'), message)
                    else
                        alert('danger', trans('Copy failed'), trans("Element could not be copied, server said no"))
                )
            when 'cut'
                operations = []
                for inode,k in inodes
                    operations.push moveTo(inode.path, destination)
                $.when.apply($,operations).done(->
                    if destination == decodeURIComponent(getUrlLocationParameter('path'))
                        locationInodes = omenApi.getProp('inodes')
                        for inode,k in inodes
                            inode.path = "#{destination}/#{inode.baseName}"
                            addInodeFigure(inode, inode.type == 'directory').then((data)->
                                locationInodes[Base64.encode(data.inode.fullPath)] = data.inode
                                omenApi.setProp('inodes', locationInodes)
                            ,(data)->
                                # if failed
                                logException("Error Occured on paste  #{data.status} #{data.statusText} INODE => #{inode.path}", "9#{ln()}").done(->
                                # fallback to redirection   
                                    window.location.replace(setLocationParameters({
                                        'path': encodeURIComponent(path)
                                    }))
                                )
                            )
                        alert('success', trans('Moved'), trans("Element was move successfully"))  
                ).fail((message)->
                    if typeof message != 'undefined' and message.length
                        alert('danger', trans('Move failed'), message)
                    else
                        alert('danger', trans('Move failed'), trans("Element could not be moved, server said no"))
                )