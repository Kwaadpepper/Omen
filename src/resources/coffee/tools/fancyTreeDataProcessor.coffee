Base64 = require('js-base64').Base64
getUrlLocationParameter = require('./../tools/getUrlLocationParameter.coffee')

##
# {
#    "path": "omen/uploads/2015",
#    "dirName": "omen/uploads",
#    "baseName": "2015",
#    "type": "directory",
#    "extension": false,
#    "fileType": null,
#    "mimeType": false,
#    "size": false,
#    "lastModified": 1587802030,
#    "visibility": "public"
#  }
#{title: 'Node 1', key: '1', refType: 'file'},
#    {title: 'Folder 2', key: '2', folder: true, refType:'folder', children: [
#      {title: 'Node 2.1', key: '3', refType: 'file'},
#      {title: 'Node 2.2', key: '4', refType: 'archive'},
#      {title: 'Node 2.3', key: '5', refType: 'audio'},
#      {title: 'Node 2.4', key: '6', refType: 'video'},
#      {title: 'Node 2.5', key: '7', refType: 'image'}
#      {title: 'Node 2.6', key: '8', refType: 'pdf'}
#    ]}

module.exports = (inodes)->

    # build path
    path = ''
    lastObj = null
    lastDir = processedData = []
    pathDirectories = decodeURIComponent(getUrlLocationParameter('path')).split('/')
    pathDirectories.shift()
    $.each(pathDirectories, (k,directory)->
        if directory == '' then return;
        data = {
            title: directory,
            key: Base64.encode(path += "/#{directory}"),
            refType: 'directory',
            folder: true
        }
        if not lastDir.length then processedData.push(lastObj = data)
        else
            lastDir[0]['children'] = [lastObj = data]
            lastDir = lastDir[0]['children']
    )
    if lastObj then lastDir = lastObj['children'] = []
    
    # add path inodes
    for k,inode of inodes
        lastDir.push({
            title: inode.baseName,
            key: Base64.encode(inode.path),
            refType: if inode.type is 'directory' then inode.type else inode.fileType
            folder: if inode.type is 'directory' then true else false
        })
    console.log 'data',processedData
    return processedData
