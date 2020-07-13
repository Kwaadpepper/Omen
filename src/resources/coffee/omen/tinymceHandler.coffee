clipboard = require('./../tools/clipboard.coffee')
inodeToHtml = require('./../tools/inodeToHtml.coffee')
getUrlLocationParameter = require('./../tools/getUrlLocationParameter.coffee')
logException = require('./../tools/logException.coffee')
lockUi = require('./../tools/lockUi.coffee')
isCalledByEditor = require('./../tools/isCalledByEditor.coffee')
omenApi = require('./../omenApi.coffee')
require('jquery-whenall')

editor = getUrlLocationParameter('editor')
type = getUrlLocationParameter('type')
wysiwygButton = $('#wysiwygButton')
wysiwygButton.hide()

if not isCalledByEditor() then return

if type is 'image'
    $('#sortType').hide()
    $('#filterFiles').parent().hide()
if type is 'media'
    $('#filterFiles').hide()
    $('#filterArchives').hide()
    $('#filterImages').hide()

exportDataToParent = (->
    inodes = omenApi.getProp('inodes')
    inodesPromises = []
    for figure in $('#viewInodes figure input:checked').parents('figure').toArray()
        inode = inodes[$(figure).data('path')]
        inodesPromises.push(inodeToHtml(inode))
    $.whenAll.apply(null, inodesPromises).always(->
        objs = []
        for k,value of arguments
            objs.push(value)
        switch editor
            when 'tinymce'
                console.log 'tinymce', objs
                parent.postMessage({ sender: 'omen', message: objs }, parent.location)
            when 'ckeditor'
                    console.log 'ckeditor'
            else
                logException("Unkown editor #{editor}")
    )
)

wysiwygButton.on('click', exportDataToParent)