fancyTree = require('jquery.fancytree')
Base64 = require('js-base64').Base64
ajaxCalls = require('./../tools/ajaxCalls.coffee')
logException = require('./../tools/logException.coffee')
getInodesAction = require('./../omen/actionEvents.coffee').getInodes


fillNodeChilds = (ftreeNode)->
    ftree = fancyTree.getTree('#leftPanelTreeView')
    fKey = if ftreeNode.key.indexOf('root_') is -1 then Base64.decode(ftreeNode.key) else '/'
    ajaxCalls(getInodesAction.method, getInodesAction.url,{
        path: fKey
    },
    ((data)->
        # console.log data
        for index,inode of data.inodes
            ftreeChildNode = ftree.getNodeByKey(index)
            if ftreeChildNode == null
                ftreeNode.addChildren({
                    title: inode.baseName,
                    key: index,
                    refType: if inode.type is 'directory' then inode.type else inode.fileType
                    folder: if inode.type is 'directory' then true else false
                })
            else if ftreeChildNode.data.refType is 'directory'
                fillNodeChilds(ftreeChildNode)
    ),
    ((error)->
        # log error
        logException("Error Occured on left pannel tree build  #{error.status} #{error.statusText} INODE => #{Base64.decode(fKey)} URL => #{getInodesAction.url}")
    ))

module.exports = fillNodeChilds