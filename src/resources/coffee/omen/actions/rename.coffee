ajax = require './../../tools/ajaxCalls.coffee'
inodes = require('./../../omenApi.coffee').inodes
logException = require('./../../tools/logException.coffee')
ln = require('./../../tools/getLine.coffee')
alert = require('./../../tools/alert.coffee')
trans = require('./../../tools/translate.coffee')
Base64 = require('js-base64').Base64

currentFigure = null
currentinode = null
actionInfo = null

renameModal = $('#renameModal')
renameForm = $('#renameForm')
renameInput = $('#renameInput')

clearVars = ->
    currentFigure = null
    currentinode = null
    actionInfo = null

renameForm.on('submit', (e)->
    ajax(actionInfo.method, actionInfo.url, {
        filename: renameInput.val()+'.'+currentinode.extension,
        filepath: currentinode.path
    },
    ((data)->
        # close Modal
        renameModal.modal('hide')

        fullBase64 = Base64.encode(data.fullPath)

        # update figure
        currentFigure.find('figcaption').text(data.name)
        currentFigure.data('path', fullBase64)
        currentFigure.attr('data-path', fullBase64)

        #update inode
        delete inodes[Base64.encode(currentinode.fullPath)]
        inodes[fullBase64] = data

        # show toast
        alert('success', trans('Name changed'), trans("File was renamed in ${filename}", { 'filename': renameInput.val() }))

        # clean memory
        clearVars()
    ),
    ((error)->

        # show toast
        alert('danger', trans('Action failure'), trans("Could not rename file ${filename}, server said no", { 'filename': renameInput.val() }))

        # log error
        logException("Error Occured on rename  #{error.status} #{error.statusText} INODE => #{currentinode.path} URL => #{actionInfo.url}", "9#{ln()}")

        # clean memory
        clearVars()
    ))
    e.preventDefault()
    false
)

module.exports = (action)->
    (event)->
        actionInfo = action
        currentFigure = $(this).parents('figure')
        fileBase64FullPath = currentFigure.data('path')
        currentinode = inodes[fileBase64FullPath]

        renameInput.val(currentinode.name)
        renameModal.modal('show')
