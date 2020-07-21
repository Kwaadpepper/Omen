$.fn.omenButton = ((type = null)->
    self = this
    popup = null
    input = document.getElementById($(self).data('input'))

    if not window._omenButtonParam
        console.error("no url prefix, please use @omenButton() directive from laravel package")
        return
    
    # Handle iframe message
    _onMessage = ((event)->
        if "#{window.location.origin}/#{window._omenButtonParam}".toLowerCase().indexOf(event.origin.toLowerCase()) == 0
            if event.data.sender == 'omenButton'
                $(input).val(event.data.message[0].url)
                dettachHandler()
                popup.close()
    )

    # handle omen message
    attachHandler = ((callback = null)->
        specialHandler = callback
        if window.addEventListener then window.addEventListener('message', _onMessage, false)
        else window.attachEvent('onmessage', _onMessage)
    )
    # remove omen message handler
    dettachHandler = (->
        specialHandler = null
        if window.removeEventListener
            window.removeEventListener('message', _onMessage, false)
        else
            window.detachEvent('onmessage', _onMessage)
    )

    $(this).on('click', ->
        title = "Omen File Manager"
        urlPrefix = "/#{window._omenButtonParam}"
        fileUrl = "#{urlPrefix}?editor=popup" + if type then "&type=#{type}" else 'type=all'
        width = window.innerWidth - 20
        height = window.innerHeight - 40
        if width > 1800 then width=1800
        if height > 1200 then height=1200
        if width > 600
            width_reduce = (width - 20) % 138
            width = width - width_reduce + 10
        popup = window.open(fileUrl, title, "width=#{width}, height=#{height}, scrollbars=yes, status=no, location=no, toolbar=no, menubar=no")
        attachHandler()
        console.log popup


    )
)