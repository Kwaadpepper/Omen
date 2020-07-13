trans = require('./translate.coffee')
viewAction = require('./../omen/actionEvents.coffee').view
ajaxCalls = require('./ajaxCalls.coffee')

module.exports = (inode)->
    promise = $.Deferred()
    out = { url: inode.url, html: "<a href='#{inode.url}' >#{inode.baseName}</a>" }
    switch inode.type
        when 'directory'
            promise.resolve(out)
        when 'file'
            switch inode.fileType
                when 'archive'
                ,'file'
                ,'executable'
                ,'diskimage'
                ,'writer'
                ,'calc'
                ,'impress'
                    promise.resolve(out)
                when 'video'
                    out.html += """<video controls width='250'>
                    <source src="#{inode.url}" type="#{inode.mimeType}">
                    #{trans('Sorry, your browser doesn\'t support embedded videos.')}
                    </video>"""
                    promise.resolve(out)
                when 'audio'
                    out.html += """<audio controls width='250'>
                    <source src="#{inode.url}" type="#{inode.mimeType}">
                    #{trans('Sorry, your browser doesn\'t support embedded audio.')}
                    </audio>"""
                    promise.resolve(out)
                when 'image'
                    out.html += "<img src='#{inode.url}' alt='#{inode.baseName}'/>"
                    promise.resolve(out)
                when 'pdf'
                    out.html += """<object type="application/pdf"
                    data='#{inode.url}'
                    width='250'
                    height='200'>
                    <a href='#{inode.url}' >#{inode.baseName}</a>
                    </object>"""
                    promise.resolve(out)
                when 'text'
                    await ajaxCalls(
                        'GET',
                        viewAction.url + inode.path,
                        null,
                        (textData)->
                            console.log 'got text'
                            console.log textData.toString()
                            out.html += "<pre>#{textData.toString()}</pre>"
                            promise.resolve(out)
                        ,
                        (error)->
                            errorText = "Error Occured #{error.status} #{error.statusText} INODE => #{inode.path} URL => #{viewAction.url + inode.path}"
                            logException(errorText)
                            out.html += "<pre>#{errorText}</pre>"
                            promise.reject(out)
                        ,
                        { async : false}
                    )
    return promise
