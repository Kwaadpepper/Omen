ajax = require './../../tools/ajaxCalls.coffee'
omenApi = require('./../../omenApi.coffee')
logException = require('./../../tools/logException.coffee')
ln = require('./../../tools/getLine.coffee')
alert = require('./../../tools/alert.coffee')
trans = require('./../../tools/translate.coffee')
Base64 = require('js-base64').Base64
progressbar = require('./../../tools/progressbar.coffee')
lockUi = require('./../../tools/lockUi.coffee')
imageEditor = require('./../imageEditor.coffee')
textEditor = require('./../textEditor.coffee')
config = require('./../../omenApi.coffee').config

currentFigure = null
currentinode = null
actionInfo = null
inodes = null

renameModal = $('#renameModal')
renameForm = $('#renameForm')
renameInput = $('#renameInput')
renameEditButton = $('#renameModal button.edit')
imageEditorModal = $('#imageEditorModal')

if not config('omen.imageLib') then renameEditButton.hide()

clearVars = ->
    currentFigure = null
    currentinode = null
    actionInfo = null

renameForm.on('submit', (e)->
    lockUi.lock()
    progressbar.run(0.3)
    ajax(actionInfo.method, actionInfo.url, {
        filename: renameInput.val()+'.'+currentinode.extension,
        filepath: currentinode.path
    },
    ((data)->
        lockUi.unlock()
        progressbar.end()
        # close Modal
        renameModal.modal('hide')

        encPath = Base64.encode(data.path)

        # update figure
        currentFigure.find('figcaption').text(data.name)
        currentFigure.data('path', encPath)
        currentFigure.attr('data-path', encPath)

        #update inode
        delete inodes[Base64.encode(currentinode.path)]
        inodes[encPath] = data
        omenApi.setProp('inodes', inodes)

        # show toast
        alert('success', trans('Name changed'), trans("File was renamed in ${filename}", { 'filename': renameInput.val() }))

        # clean memory
        clearVars()
    ),
    ((error)->
        lockUi.unlock()
        progressbar.end()
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

renameModal.on 'hidden.bs.modal', (e)->
    renameEditButton.addClass('d-none')

renameEditButton.on 'click', (e)->
    if currentinode.fileType == 'image' then imageEditor(currentinode)
    if currentinode.fileType == 'text' then textEditor(currentinode)
    

module.exports = (action)->
    (event)->
        actionInfo = action
        currentFigure = $(this).parents('figure')
        fileBase64Path = currentFigure.data('path')
        inodes = omenApi.getProp('inodes')
        currentinode = inodes[fileBase64Path]

        if currentinode.fileType == 'image' or currentinode.fileType == 'text' then renameEditButton.removeClass('d-none')

        renameInput.val(currentinode.name)
        renameModal.modal('show')
