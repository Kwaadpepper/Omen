# register All Action Events
# for this router see routes/api.php or routes/web.php
# 
isCalledByEditor = require('./../tools/isCalledByEditor.coffee')
trans = require('./../tools/translate.coffee')

config = require('./../omenApi.coffee').config
window.lockUi = lockUi = require('./../tools/lockUi.coffee')

# remove sarting '/' of urlPrefix
urlPrefix = config('omen.urlPrefix')
urlPrefix = if urlPrefix.indexOf('/') == 0 then urlPrefix.substring(1) else urlPrefix

$filterFiles = $('#filterFiles')
$filterArchives = $('#filterArchives')
$filterImages = $('#filterImages')
$filterAudio = $('#filterAudio')
$filterVideo = $('#filterVideo')
$filterInputText = $('#filterInputText')

module.exports = actions = {
    # URL is 
    upload: { method: 'POST', url: '/' + urlPrefix + '/upload' }
    download: { method: 'GET', url: '/' + urlPrefix + '/download' }
    view: { method: 'GET', url: '/' + urlPrefix + '/download' },
    rename: { method: 'POST', url: '/' + urlPrefix + '/rename' }
    delete: { method: 'POST', url: '/' + urlPrefix + '/delete' },
    moveTo: { method: 'POST', url: "/#{urlPrefix}/moveto" },
    copyTo: { method: 'POST', url: "/#{urlPrefix}/copyto" },
    createFile: { method: 'POST', url: '/' + urlPrefix + '/createtextfile' },
    updateFile: { method: 'POST', url: "/#{urlPrefix}/updatetextfile" },
    createDirectory: { method: 'POST', url: '/' + urlPrefix + '/createdirectory' },
    getInode: { method: 'GET', url: '/' + urlPrefix + '/getinode' },
    getInodes: { method: 'GET', url: '/' + urlPrefix + '/getinodes' }
    getBreadcrumb: { method: 'GET', url: '/' + urlPrefix + '/getbreadcrumb' },
    resizeImage: { method: 'POST', url: "/#{urlPrefix}/resizeimage"},
    cropImage: { method: 'POST', url: "/#{urlPrefix}/cropimage"},
    ping: { method: 'POST', url: "/#{urlPrefix}/ping" }
    resetFilters: ->
        if $filterFiles.hasClass('active')
            setFilterStorage('filterFiles', 'file', $filterFiles[0])
            $filterFiles.removeClass('active')
        if $filterArchives.hasClass('active')
            setFilterStorage('filterArchives', 'archive', $filterArchives[0])
            $filterArchives.removeClass('active')
        if $filterImages.hasClass('active')
            setFilterStorage('filterImages', 'image', $filterImages[0])
            $filterImages.removeClass('active')
        if $filterAudio.hasClass('active')
            setFilterStorage('filterAudio', 'audio', $filterAudio[0])
            $filterAudio.removeClass('active')
        if $filterVideo.hasClass('active')
            setFilterStorage('filterVideo', 'video', $filterVideo[0])
            $filterVideo.removeClass('active')
        if $filterInputText.val().length
            localStorage.setItem('filterText', "")
            $filterInputText.val('')
            require('./actions/filterInodes.coffee')().apply($filterInputText)

    applySort: -> require('./actions/sortNodes.coffee')(localStorage.getItem('sortFiles'))()
}

inodesView = $('#viewInodes')
leftPanel = $('#leftPanelMenuBar')

actionEvent = if config('omen.doubleClickToOpen') then 'dblclick' else 'click'

# Figure Actions
inodesView.on('click','button.actionDownload', (e)-> if not lockUi.locked then (require('./actions/download.coffee')(actions.download)).call this, e)
inodesView.on('click','button.actionView', (e)-> if not lockUi.locked then (require('./actions/view.coffee')(actions.view)).call this, e)
inodesView.on('click','button.actionRename', (e)-> if not lockUi.locked then (require('./actions/rename.coffee')(actions.rename)).call this, e)
inodesView.on('click','button.actionDelete', (e)-> if not lockUi.locked then (require('./actions/delete.coffee')(actions.delete)).call this, e)
inodesView.on(actionEvent, 'figure > .figIcon', (e)-> if not lockUi.locked then (require('./actions/figureAction.coffee')()).call this, e)

# left Pannel Actions
leftPanel.on('click', 'button#leftPanelActionReload', require('./actions/reload.coffee')())
$('#leftPanelLocalesList').on('click', 'a', require('./actions/languageChange.coffee')())

# breadcrumb action
$breacrumbContainer = $('#viewInodes').children().first()
if actionEvent is 'dblclick'
    $breacrumbContainer.on('click', '#actionUpperDirectory', (e)-> e.preventDefault(); return false; )
    $breacrumbContainer.on('click', '#pathBreadcrumbList ', (e)-> e.preventDefault(); return false; )
$breacrumbContainer.on(actionEvent, '#actionUpperDirectory', (e)-> if not lockUi.locked then (require('./actions/upperDirectory.coffee')()).call this, e)
$breacrumbContainer.on(actionEvent, '#pathBreadcrumbList a', (e)-> if not lockUi.locked then (require('./actions/breadcrumbNavigateTo.coffee')()).call this, e)

# navBar actions

# inodes toolbar
$('#actionUpload').on('click', require('./actions/uploadFile.coffee')(actions.uploadFile))
$('#actionNewFile').on('click', require('./actions/createFile.coffee')(actions.createFile))
$('#actionNewDirectory').on('click', require('./actions/createDirectory.coffee')(actions.createDirectory))

# view toolbar
$('#viewIcon').on('click', (e)->
    localStorage.setItem 'viewType', 'icon'
    require('./actions/changeViewType.coffee')('icon')()
)
$('#viewList').on('click', (e)->
    localStorage.setItem 'viewType', 'list'
    require('./actions/changeViewType.coffee')('list')()
)
switch localStorage.getItem 'viewType'
    when 'icon' then require('./actions/changeViewType.coffee')('icon')()
    when 'list' then require('./actions/changeViewType.coffee')('list')() 

# filter toolbar
setFilterStorage = (filterType, filterAction, element)->
    if $(element).hasClass 'active' then localStorage.setItem filterType, false
    else localStorage.setItem filterType, true
    require('./actions/filterInodes.coffee')(filterAction).apply element
$filterFiles.on('click', -> setFilterStorage('filterFiles', 'file', this))
$filterArchives.on('click', -> setFilterStorage('filterArchives', 'archive', this))
$filterImages.on('click', -> setFilterStorage('filterImages', 'image', this))
$filterAudio.on('click', -> setFilterStorage('filterAudio', 'audio', this))
$filterVideo.on('click', -> setFilterStorage('filterVideo', 'video', this))
$filterInputText.on('input', ->
    localStorage.setItem('filterText', $(this).val())
    require('./actions/filterInodes.coffee')().apply this
)
if localStorage.getItem('filterFiles') is "true" then require('./actions/filterInodes.coffee')('file').apply $filterFiles[0]
if localStorage.getItem('filterArchives') is "true" then require('./actions/filterInodes.coffee')('archive').apply $filterArchives[0]
if localStorage.getItem('filterImages') is "true" then require('./actions/filterInodes.coffee')('image').apply $filterImages[0]
if localStorage.getItem('filterAudio') is "true" then require('./actions/filterInodes.coffee')('audio').apply $filterAudio[0]
if localStorage.getItem('filterVideo') is "true" then require('./actions/filterInodes.coffee')('video').apply $filterVideo[0]
if localStorage.getItem('filterText') != null and localStorage.getItem('filterText').length
    $filterInputText.val(localStorage.getItem('filterText'))
    require('./actions/filterInodes.coffee')().apply $filterInputText[0]

#breadcrumb toolbar
setSortStorage = (sortType, element, event)-> require('./actions/sortNodes.coffee')(sortType, if event then true else false).call element, event
$('#sortAlpha').on('click', (e)-> setSortStorage('alpha', this, e))
$('#sortDate').on('click', (e)-> setSortStorage('date', this, e))
$('#sortSize').on('click', (e)-> setSortStorage('size', this, e))
$('#sortType').on('click', (e)-> setSortStorage('type', this, e))

switch localStorage.getItem 'sortFiles'
    when 'alpha' then setSortStorage('alpha', $('#sortAlpha'))
    when 'date' then setSortStorage('date', $('#sortDate'))
    when 'size' then setSortStorage('size', $('#sortSize'))
    when 'type' then setSortStorage('type', $('#sortType'))

#operations toolbar
displayOperationsToolbar = ->
    calledByEditor = isCalledByEditor()
    setTimeout (->
        checkedItems = $('#viewInodes figure input:checked').parents('figure').toArray()
        $('#wysiwygButton').text(trans('Use') + checkedItems.length)
        if checkedItems.length
            $('#operationsToolbar').addClass('show')
            if calledByEditor then $('#wysiwygButton').show()
        else
            $('#operationsToolbar').removeClass('show')
            if calledByEditor then $('#wysiwygButton').hide()
        return
    ), 50
displayOperationsToolbar() # init onload
$('#viewInodes').on('click', 'span.checkmark',displayOperationsToolbar)
$('#operationsCopy').on('click', -> if not lockUi.locked then require('./actions/copy.coffee')())
$('#operationsCut').on('click', -> if not lockUi.locked then require('./actions/cut.coffee')())
$('#operationsPaste').on('click', -> if not lockUi.locked then require('./actions/paste.coffee')())
# selection
$('#operationsSelectAll').on('click', 'input', ->
    if not lockUi.locked
        require('./actions/selectAll.coffee')(true, $(this).is(':checked'))
        displayOperationsToolbar()
)
$('#inodesContainer').on('click', '#viewListTopBar input', ->
    if not lockUi.locked 
        require('./actions/selectAll.coffee')(true, $(this).is(':checked'))
        displayOperationsToolbar()
)
$('#inodesContainer').on('click', 'figure input[type="checkbox"]', -> if not lockUi.locked then require('./actions/selectAll.coffee')())