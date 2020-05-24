omenApi = require('./../../omenApi.coffee')
ajaxCalls = require('./../../tools/ajaxCalls.coffee')
logException = require('./../../tools/logException.coffee')
ln = require('./../../tools/getLine.coffee')
trans = require('./../../tools/translate.coffee')
alert = require('./../../tools/alert.coffee')
progressbar = require('./../../tools/progressbar.coffee')



module.exports = (action)->
    (event)->
        fileFullPath = $(this).parents('figure').data('path')
        inodes = omenApi.getProp('inodes')
        inode = inodes[fileFullPath]
        progressbar.run(0.3)

        url = if inode.visibility == 'public' then inode.url else action.url + inode.path

        # test file exists
        ajaxCalls(
            'HEAD',
            url,
            null,
            null,
            null,
            { 
                complete : (jxhr)->
                    progressbar.end()
                    contentLength = jxhr.getResponseHeader('Content-Length')
                    if jxhr.status is not 200 or !contentLength
                        logException("Error Occured #{jxhr.status} #{jxhr.statusText} INODE => #{inode.path} URL => #{url}", "9#{ln()}")
                        alert('danger', trans('File download error'), trans("Server could not get ${inodename}", { 'inodename': inode.name }))
                    else
                        # #Dynamic Download
                        a = document.createElement('a')
                        a.href = url
                        a.download = url
                        document.body.appendChild(a)
                        a.click()
                        document.body.removeChild(a)
            }
        )
    

        