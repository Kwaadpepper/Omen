viewAction = require('./actionEvents.coffee').view
updateFileAction = require('./actionEvents.coffee').updateFile
ajaxCalls = require('../tools/ajaxCalls.coffee')
lockUi = require('./../tools/lockUi.coffee')
progressbar = require('./../tools/progressbar.coffee')
logException = require('./../tools/logException.coffee')
alert = require('./../tools/alert.coffee')
ln = require('./../tools/getLine.coffee')
trans = require('./../tools/translate.coffee')
getUrlLocationParameter = require('./../tools/getUrlLocationParameter.coffee')
hightlightJS = require('highlight.js')

currentInode = null

# Dom Elements
editFileModal = $('#editFileModal')
editFileForm = $('#editFileForm')
editFileTextInput = $('#editFileTextInput')
editFormSubmitButton = $('#editFileForm button[type="submit"]')

# INPUTS


#! Methods
# allow tab insert
editFileTextInput.parent().delegate('pre', 'keydown', (event)->
    switch(event.which)
        when 9
            event.preventDefault()
            document.execCommand('insertHTML', false, '&#009')
)

#! EVENTS
editFileForm.on('submit', (e)->
    filetext = editFileTextInput.innerText()
    filepath = currentInode.path

    lockUi.lock()
    progressbar.run(0.3)
    ajaxCalls(updateFileAction.method, updateFileAction.url, {
        filePath: filepath
        fileText: filetext
    },
    ((inode)->
        # clean Modal inputs
        editFileTextInput.text('')

        # close Modal
        editFileModal.modal('hide')

        lockUi.unlock()
        progressbar.end()

        # show toast
        alert('success', trans('File updated'), trans("${inodename} has been updated", { 'inodename': currentInode.baseName }))
    ),
    ((error)->
        lockUi.unlock()
        progressbar.end()
        # show toast
        alert('danger', trans('Action failure'), trans("Could not update ${inodename}, server said no", { 'inodename': currentInode.baseName }))

        # log error
        logException("Error Occured on update  #{error.status} #{error.statusText} INODE => #{currentInode.path} URL => #{updateFileAction.url}", "9#{ln()}")
    ))

    e.preventDefault()
    false    
)

#! Init
editFileModal.on 'shown.bs.modal', ((e)->
    # get the inode showable url
    url = viewAction.url + currentInode.path
    editFileModal.find('h5').text(currentInode.baseName)

    editFormSubmitButton.prop('disabled', true)
    lockUi.lock()
    progressbar.run(0.3)
    ajaxCalls(
        'GET',
        url,
        null,
        (textData)->
            lockUi.unlock()
            progressbar.end()
            editFormSubmitButton.prop('disabled', false)
            editFileModal.find('pre').text(textData)
            document.querySelectorAll('pre').forEach((block)->
                hightlightJS.highlightBlock(block)
            )
        ,
        (error)->
            lockUi.unlock()
            progressbar.end()
            alert('danger', trans('Error'), trans('Could not retrieve text file'))
            logException("Error Occured #{error.status} #{error.statusText} INODE => #{currentInode.path} URL => #{url}", "9#{ln()}")
        ,
        { dataType : 'html'}
    )
)
editFileModal.on 'hidden.bs.modal', ((e)->
    console.log 'hidden'
)

module.exports = ((inode)->
    currentInode = inode
    editFileModal.modal('show')
)