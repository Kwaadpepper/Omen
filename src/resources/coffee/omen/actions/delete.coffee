omenApi = require('./../../omenApi.coffee')
ajaxCalls = require('./../../tools/ajaxCalls.coffee')
logException = require('./../../tools/logException.coffee')
trans = require('./../../tools/translate.coffee')
alert = require('./../../tools/alert.coffee')

progressbar = require('./../../tools/progressbar.coffee')
lockUi = require('./../../tools/lockUi.coffee')


module.exports = (action)->
    (event)->

        if !confirm(trans('Delete inode ?')) then return false

        inodeFigure = $(this).parents('figure')
        inodePath = inodeFigure.data('path')
        inodes = omenApi.getProp('inodes')
        inode = inodes[inodePath]
        lockUi.lock()
        progressbar.run(0.3)

        # test file exists
        ajaxCalls(
            action.method,
            action.url,
            { 'filepath':  inode.path },
            null,
            null,
            { 
                complete : (jxhr)->
                    lockUi.unlock()
                    progressbar.end()
                    if jxhr.status != 200
                        logException("Error Occured on delete inode #{jxhr.status} #{jxhr.statusText} INODE => #{inode.path}")
                        alert('danger', trans('File delete error'), trans("Server error on delete ${filename}", { 'filename': inode.name }))
                    else
                        # remove inode
                        delete inodes[inodePath]
                        omenApi.setProp('inodes', inodes)
                        inodeFigure.remove()

                        alert('success', trans('File deletion'), trans("File is removed"))
            }
        )
    

        