# register All Action Events
# for this router see routes/api.php or routes/web.php

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
    createFile: { method: 'POST', url: '/' + urlPrefix + '/createtextfile' },
    createDirectory: { method: 'POST', url: '/' + urlPrefix + '/createdirectory' },
    getInodeHtml: { method: 'GET', url: '/' + urlPrefix + '/getinodehtml' },
    getInodesAtPath: { method: 'GET', url: '/' + urlPrefix + '/getinodesatpath' }
    getBreadcrumbAtPath: { method: 'GET', url: '/' + urlPrefix + '/getbreadcrumbatpath' }
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
        if $filterInputText .val().length
            localStorage.setItem('filterText', "")
            $filterInputText .val('')
            require('./actions/filterInodes.coffee')().apply $filterInputText 

    applySort: ->
        sortType = localStorage.getItem('sortFiles')
        sortWay = localStorage.getItem("sortFilesWay#{sortType}")
        require('./actions/sortNodes.coffee')(localStorage.getItem('sortFiles'), sortWay)()
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
$filterInputText .on('input', ->
    localStorage.setItem('filterText', $(this).val())
    require('./actions/filterInodes.coffee')().apply this
)
if localStorage.getItem('filterFiles') is "true" then require('./actions/filterInodes.coffee')('file').apply $filterFiles[0]
if localStorage.getItem('filterArchives') is "true" then require('./actions/filterInodes.coffee')('archive').apply $filterArchives[0]
if localStorage.getItem('filterImages') is "true" then require('./actions/filterInodes.coffee')('image').apply $filterImages[0]
if localStorage.getItem('filterAudio') is "true" then require('./actions/filterInodes.coffee')('audio').apply $filterAudio[0]
if localStorage.getItem('filterVideo') is "true" then require('./actions/filterInodes.coffee')('video').apply $filterVideo[0]
if localStorage.getItem('filterText') != null and localStorage.getItem('filterText').length
    $filterInputText .val(localStorage.getItem('filterText'))
    require('./actions/filterInodes.coffee')().apply $filterInputText [0]

#breadcrumb toolbar
setSortStorage = (sortType, element, event)->
    sortWay = undefined
    clickedEvent = typeof event != 'undefined'
    localStorage.setItem 'sortFiles', sortType
    if clickedEvent
        if typeof localStorage.getItem("sortFilesWay#{sortType}") !='undefined'
            if localStorage.getItem("sortFilesWay#{sortType}") == 'true' then localStorage.setItem "sortFilesWay#{sortType}", false
            else localStorage.setItem "sortFilesWay#{sortType}", true
        else
            localStorage.setItem "sortFilesWay#{sortType}", true
    else
        sortWay = localStorage.getItem("sortFilesWay#{sortType}") == 'false'
    require('./actions/sortNodes.coffee')(sortType, sortWay).call element, event
$('#sortAlpha').on('click', (e)-> setSortStorage('alpha', this, e))
$('#sortDate').on('click', (e)-> setSortStorage('date', this, e))
$('#sortSize').on('click', (e)-> setSortStorage('size', this, e))
$('#sortType').on('click', (e)-> setSortStorage('type', this, e))

switch localStorage.getItem 'sortFiles'
    when 'alpha' then setSortStorage('alpha', $('#sortAlpha'))
    when 'date' then setSortStorage('date', $('#sortDate'))
    when 'size' then setSortStorage('size', $('#sortSize'))
    when 'type' then setSortStorage('type', $('#sortType'))
