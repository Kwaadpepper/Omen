ajax = require('./../../tools/ajaxCalls.coffee')
logException = require('./../../tools/logException.coffee')
ln = require('./../../tools/getLine.coffee')

actions = require('./../actionEvents.coffee')

module.exports = (inode, isDirectory = false)->

    options = if isDirectory then { directorypath: inode.path } else { filepath: inode.path }

    return new Promise((successCallback, failureCallback)->
        ajax(actions.getInodeHtml.method, actions.getInodeHtml.url, options,
        ((answer)->
            $('#inodesContainer').children().eq(2).after(answer.inodeHtml)
            successCallback()
        ),
        ((jxhr)->
            logException("Error Occured #{jxhr.status} #{jxhr.statusText} INODE => #{inode.path} URL => #{actions.getInodeHtml.url}", "9#{ln()}")
            failureCallback()
        ))
    )
