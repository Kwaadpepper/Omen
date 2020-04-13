# register All Action Events
# for this router see routes/api.php or routes/web.php

config = require('./../omenApi.coffee').config

# remove sarting '/' of urlPrefix
urlPrefix = config('omen.urlPrefix')
urlPrefix = if urlPrefix.indexOf('/') == 0 then urlPrefix.substring(1) else urlPrefix

module.exports = actions = {
    # URL is 
    upload: { method: 'POST', url: '/' + urlPrefix + '/upload' }
    download: { method: 'GET', url: '/' + urlPrefix + '/download' }
    view: { method: 'GET', url: '/' + urlPrefix + '/download' },
    rename: { method: 'POST', url: '/' + urlPrefix + '/rename' }
    delete: { method: 'POST', url: '/' + urlPrefix + '/delete' },
    createFile: { method: 'POST', url: '/' + urlPrefix + '/createtextfile' },
    createDirectory: { method: 'POST', url: '/' + urlPrefix + '/createdirectory' },
    getInodeHtml: { method: 'GET', url: '/' + urlPrefix + '/getinodehtml' }
}

inodesView = $('#viewInodes')
leftPanel = $('#leftPanelMenuBar')

actionEvent = if config('omen.doubleClickToOpen') then 'dblclick' else 'click'

# Figure Actions
inodesView.on('click','button.actionDownload', require('./actions/download.coffee')(actions.download))
inodesView.on('click','button.actionView', require('./actions/view.coffee')(actions.view))
inodesView.on('click','button.actionRename', require('./actions/rename.coffee')(actions.rename))
inodesView.on('click','button.actionDelete', require('./actions/delete.coffee')(actions.delete))
inodesView.on(actionEvent, 'figure > .figIcon', require('./actions/figureAction.coffee')())

# left Pannel Actions
leftPanel.on('click', 'button#leftPanelActionReload', require('./actions/reload.coffee')())
$('#leftPanelLocalesList').on('click', 'a', require('./actions/languageChange.coffee')())

# breadcrumb action
$('#actionUpperDirectory').on('click', require('./actions/upperDirectory.coffee')())

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
$('#filterFiles').on('click', -> setFilterStorage('filterFiles', 'file', this))
$('#filterArchives').on('click', -> setFilterStorage('filterArchives', 'archive', this))
$('#filterImages').on('click', -> setFilterStorage('filterImages', 'image', this))
$('#filterAudio').on('click', -> setFilterStorage('filterAudio', 'audio', this))
$('#filterVideo').on('click', -> setFilterStorage('filterVideo', 'video', this))
$('#filterInputText').on('input', ->
    localStorage.setItem('filterText', $(this).val())
    require('./actions/filterInodes.coffee')().apply this
)
if localStorage.getItem('filterFiles') is "true" then require('./actions/filterInodes.coffee')('file').apply $('#filterFiles')[0]
if localStorage.getItem('filterArchives') is "true" then require('./actions/filterInodes.coffee')('archive').apply $('#filterArchives')[0]
if localStorage.getItem('filterImages') is "true" then require('./actions/filterInodes.coffee')('image').apply $('#filterImages')[0]
if localStorage.getItem('filterAudio') is "true" then require('./actions/filterInodes.coffee')('audio').apply $('#filterAudio')[0]
if localStorage.getItem('filterVideo') is "true" then require('./actions/filterInodes.coffee')('video').apply $('#filterVideo')[0]
if localStorage.getItem('filterText') != null and localStorage.getItem('filterText').length
    $('#filterInputText').val(localStorage.getItem('filterText'))
    require('./actions/filterInodes.coffee')().apply $('#filterInputText')[0]

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