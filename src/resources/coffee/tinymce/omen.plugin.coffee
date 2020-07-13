tinymce.PluginManager.add('omen', (editor)->

    # common vars
    title = "Omen File Manager"
    fileUrl = "#{editor.settings.external_filemanager_path}&editor=tinymce"
    width = window.innerWidth - 20
    height = window.innerHeight - 40
    if width > 1800 then width=1800
    if height > 1200 then height=1200
    if width > 600
        width_reduce = (width - 20) % 138
        width = width - width_reduce + 10
    winConfig = {
        title: title,
        file: fileUrl, # for tinymce 4
        url: fileUrl, # for tinymce 5
        width: width,
        height: height,
        inline: 1,
        resizable: true,
        maximizable: true
    }
    specialHandler = null

    # Handle iframe message
    _onMessage = ((event)->
        if editor.settings.external_filemanager_path.toLowerCase().indexOf(event.origin.toLowerCase()) == 0
            if event.data.sender == 'omen'
                if specialHandler
                    specialHandler(event.data.message[0].url)
                    tinymce.activeEditor.windowManager.close()
                else
                    html = ''
                    for k,element of event.data.message
                        html += element.html
                    tinymce.activeEditor.insertContent(html)
                    tinymce.activeEditor.windowManager.close()
                dettachHandler()
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

    # register image file picker and so..
    editor.settings.file_picker_callback = ((callback, value, meta)->
        attachHandler(callback)

        url = fileUrl.replace(/(type=\d+)(&?)/, "type=#{meta.filetype}$2")
        winConfig.url = url
        winConfig.file = url
        winConfig.close_previous = "no"

        param = {
            window : win,
            resizable : true,
            inline : true,  # This parameter only has an effect if you use the inlinepopups plugin!
            editor_id : editor.id
        }

        if tinymce.majorVersion < 5 then editor.windowManager.open(winConfig, param)
        else win = editor.windowManager.openUrl(winConfig,param)
    
            
        return false
    )
    # end register image file picker and so

    # register file picker button handler
    openmanager = (->
        attachHandler()
        editor.focus(true)

        url = fileUrl.replace(/(type=\d+)(&?)/, "type=all$2")
        winConfig.url = url
        winConfig.file = url

        if tinymce.majorVersion < 5 then editor.windowManager.open winConfig
        else editor.windowManager.openUrl winConfig
    )

    menuItem = {
        icon: 'browse',
        text: 'Omen file manager',
        shortcut: 'Ctrl+E',
        context: 'insert'
    }
    button = {
        icon: 'browse',
        tooltip: 'Omen file manager',
        shortcut: 'Ctrl+E',
    }

    editor.addShortcut('Ctrl+E', '', openmanager)
    if tinymce.majorVersion < 5
        menuItem.onClick = openmanager
        button.onClick = openmanager
        editor.addButton('omen', button)
        editor.addMenuItem('omen', menuItem)
    else
        menuItem.onAction = openmanager
        button.onAction = openmanager
        editor.ui.registry.addButton('omen', button)
        editor.ui.registry.addMenuItem('omen', menuItem)
    # end register file picker button

    return
)