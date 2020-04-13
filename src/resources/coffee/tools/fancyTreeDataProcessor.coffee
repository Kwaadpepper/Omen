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
    # console.log 'inodes'
    # console.log inodes

    processedData = []
    
    for k,inode of inodes
        processedData.push({
            title: inode.baseName,
            key: k,
            refType: inode.fileType
        })
    return processedData


