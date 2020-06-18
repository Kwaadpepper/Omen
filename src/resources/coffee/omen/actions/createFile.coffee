ajax = require './../../tools/ajaxCalls.coffee'
logException = require('./../../tools/logException.coffee')
ln = require('./../../tools/getLine.coffee')
alert = require('./../../tools/alert.coffee')
trans = require('./../../tools/translate.coffee')
getUrlLocationParameter = require('./../../tools/getUrlLocationParameter.coffee')
Base64 = require('js-base64').Base64
addInodeFigure = require('./addInodeFigure.coffee')
reloadPage = require('./reload.coffee')
actions = require('./../actionEvents.coffee')
omenApi = require('./../../omenApi.coffee')
hightlightJS = require('highlight.js')
applySort = require('./../actionEvents.coffee').applySort


actionInfo = null

newFileModal = $('#newFileModal')
newFileForm = $('#newFileForm')
newFileTitleInput = $('#newFileNameInput')
newFileTextInput = $('#newFileTextInput')
progressbar = require('./../../tools/progressbar.coffee')
lockUi = require('./../../tools/lockUi.coffee')

newFileModal.on('shown.bs.modal', -> hightlightJS.highlightBlock(newFileTextInput[0]))

clearVars = ->
    actionInfo = null

# allow tab insert
newFileTextInput.parent().delegate('pre', 'keydown', (event)->
        switch(event.which)
            when 9
                event.preventDefault()
                document.execCommand('insertHTML', false, '&#009')
    )


newFileForm.on('submit', (e)->
  
    filename = "#{newFileTitleInput.val()}.txt"
    filetext = newFileTextInput.innerText()
    filepath = decodeURIComponent(getUrlLocationParameter('path'))
    urlCheck = actions.download.url + "#{filepath}/#{filename}"

    progressbar.run(0.3)
    lockUi.lock()

    #check file don't exists on server HEAD method
    
    ajax(
        'HEAD',
        urlCheck,
        null,
        null,
        null,
        { 
            complete : (jxhr)->
                # if no file exists
                if jxhr.status is 404
                    # create the file
                    createFile()
                # if server answer anything else than file is present
                else if jxhr.status is not 200
                    lockUi.unlock()
                    progressbar.end()
                    logException("Error Occured #{jxhr.status} #{jxhr.statusText} INODE => #{filepath} URL => #{urlCheck}", "9#{ln()}")
                    alert('danger', trans('File check error'), trans("Server could not say if ${inodename} exists", { 'inodename': filename }))
                
                # if file already exists
                else
                    lockUi.unlock()
                    progressbar.end()
                    alert('danger', trans('This file name already exists !'), trans("Please choose another name than ${inodename}", { 'inodename': filename }))
        }
    )

    createFile = (->
        # try create the file
        ajax(actionInfo.method, actionInfo.url, {
            filePath: filepath
            fileName: filename
            fileText: filetext
        },
        ((inode)->
            # clean Modal inputs
            newFileTitleInput.val('')
            newFileTextInput.text('')

            # close Modal
            newFileModal.modal('hide')

            # add inode
            encPath = Base64.encode(inode.path)
            inodes = omenApi.getProp('inodes')
            inodes[encPath] = inode
            omenApi.setProp('inodes', inodes)

            # add figure
            addInodeFigure(inode).then(
                # if figure was added then scrolltop
                (->
                    applySort()
                    lockUi.unlock()
                    progressbar.end()
                    require('./../../omenApi.coffee').simpleBarInodes.getScrollElement().scroll(0, 0)
                ),
                # if figure could not be added to Dom reload the page
                (->
                    lockUi.unlock()
                    progressbar.end()
                    reloadPage()()
                )
            )

            # show toast
            alert('success', trans('File created'), trans("${inodename} has been created", { 'inodename': filename }))
        ),
        ((error)->
            lockUi.unlock()
            progressbar.end()

            if error.status == 400
                newFileTitleInput.val(error.responseJSON.filename)
                alert('danger', trans('Action failure'), error.responseJSON.message)
            else
                alert('danger', trans('Action failure'), trans("Could not create ${inodename}, server said no", { 'inodename': filename }))
                logException("Error Occured on create  #{error.status} #{error.statusText} INODE => #{filepath} URL => #{actionInfo.url}", "9#{ln()}")
        ))
    )

    # do not submit form
    e.preventDefault()
    false
)

module.exports = (action)->
    (event)->
        actionInfo = action

        newFileModal.modal('show')
