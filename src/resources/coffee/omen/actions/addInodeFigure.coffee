ajax = require('./../../tools/ajaxCalls.coffee')
logException = require('./../../tools/logException.coffee')
ln = require('./../../tools/getLine.coffee')

actions = require('./../actionEvents.coffee')

module.exports = (inode, isDirectory = false)->

    options = if isDirectory then { directorypath: inode.path } else { filepath: inode.path }

    return new Promise((successCallback, failureCallback)->
        ajax(actions.getInodeHtml.method, actions.getInodeHtml.url, options,
        ((data)->
            $('#inodesContainer').children().eq(2).after(data.inodeHtml)
            successCallback(data)
        ),
        ((data)->
            logException("Error Occured #{data.status} #{data.statusText} INODE => #{inode.path} URL => #{actions.getInodeHtml.url}", "9#{ln()}")
            failureCallback(data)
        ))
    )
