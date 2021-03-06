ajax = require './../../tools/ajaxCalls.coffee'
logException = require('./../../tools/logException.coffee')
alert = require('./../../tools/alert.coffee')
trans = require('./../../tools/translate.coffee')
getUrlLocationParameter = require('./../../tools/getUrlLocationParameter.coffee')
Base64 = require('js-base64').Base64
addInodeFigure = require('./addInodeFigure.coffee')
reloadPage = require('./reload.coffee')
actions = require('./../actionEvents.coffee')
omenApi = require('./../../omenApi.coffee')
progressbar = require('./../../tools/progressbar.coffee')
lockUi = require('./../../tools/lockUi.coffee')
applySort = require('./../actionEvents.coffee').applySort

actionInfo = null

newDirectoryModal = $('#newDirectoryModal')
newDirectoryForm = $('#newDirectoryForm')
newDirectoryTitleInput = $('#newDirectoryNameInput')

newDirectoryForm.on('submit', (e)->

    if newDirectoryTitleInput.val().length < 3
        alert('danger', trans('Wrong input'), trans('the file shall be at least 3 characters'))
        e.preventDefault()
        return false
    
    directoryname = newDirectoryTitleInput.val()
    directorypath = decodeURIComponent(getUrlLocationParameter('path'))
    urlCheck = actions.download.url + "#{directorypath}/#{directoryname}"

    lockUi.lock()
    progressbar.run(0.3)

    #check directory don't exists on server HEAD method
    
    ajax(
        'HEAD',
        urlCheck,
        null,
        null,
        null,
        { 
            complete : (jxhr)->
                # if no directory exists
                if jxhr.status is 404
                    # create the directory
                    createDirectory()
                # if server answer anything else than directory is present
                else if jxhr.status is not 200
                    lockUi.unlock()
                    progressbar.end()
                    logException("Error Occured #{jxhr.status} #{jxhr.statusText} INODE => #{directorypath} URL => #{urlCheck}")
                    alert('danger', trans('Directory check error'), trans("Server could not say if ${inodename} exists", { 'inodename': directoryname }))
                
                # if directory already exists
                else
                    lockUi.unlock()
                    progressbar.end()
                    alert('danger', trans('This directory name already exists !'), trans("Please choose another name than ${inodename}", { 'inodename': directoryname }))
        }
    )

    createDirectory = (->
        # try create the directory
        ajax(actionInfo.method, actionInfo.url, {
            directoryPath: directorypath
            directoryName: directoryname
        },
        ((inode)->
            # clean Modal input
            newDirectoryTitleInput.val('')

            # close Modal
            newDirectoryModal.modal('hide')

            # add inode
            encPath = Base64.encode(inode.path)
            inodes = omenApi.getProp('inodes')
            inodes[encPath] = inode
            omenApi.setProp('inodes', inodes)

            # add figure
            addInodeFigure(inode, true).then(
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
            alert('success', trans('Directory created'), trans("${inodename} has been created", { 'inodename': directoryname }))
        ),
        ((error)->
            lockUi.unlock()
            progressbar.end()

            if error.status == 400
                newDirectoryTitleInput.val(error.responseJSON.filename)
                alert('danger', trans('Action failure'), error.responseJSON.message)
            else
                alert('danger', trans('Action failure'), trans("Could not create ${inodename}, server said no", { 'inodename': directoryname }))
                logException("Error Occured on create  #{error.status} #{error.statusText} INODE => #{filepath} URL => #{actionInfo.url}")
        ))
    )

    # do not submit form
    e.preventDefault()
    false
)

module.exports = (action)->
    (event)->
        actionInfo = action
        newDirectoryModal.modal('show')
