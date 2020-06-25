ajax = require('./../../tools/ajaxCalls.coffee')
logException = require('./../../tools/logException.coffee')
actions = require('./../actionEvents.coffee')

module.exports = (inode, isDirectory = false)->

    return new Promise((successCallback, failureCallback)->
        ajax(actions.getInode.method, actions.getInode.url, { inodepath: inode.path },
        ((data)->
            $('#inodesContainer').children().eq(1).after(data.inodeHtml)
            successCallback(data)
        ),
        ((data)->
            logException("Error Occured #{data.status} #{data.statusText} INODE => #{inode.path} URL => #{actions.getInode.url}")
            failureCallback(data)
        ))
    )
