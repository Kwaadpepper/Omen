import 'jquery.fancytree/dist/modules/jquery.fancytree.edit'
import 'jquery.fancytree/dist/modules/jquery.fancytree.filter'
import 'jquery.fancytree/dist/modules/jquery.fancytree.glyph'

fancyTree = require 'jquery.fancytree'
omenApi = require './../omenApi.coffee'
dataProcessor = require "./../tools/fancyTreeDataProcessor.coffee"
readyEvent = require('./../tools/loadingSplash.coffee').registerWaiting()
ajaxNavigation = require('./../tools/ajaxNavigation.coffee')
Base64 = require('js-base64').Base64
getUrlLocationParameter = require('./../tools/getUrlLocationParameter.coffee')
logException = require('./../tools/logException.coffee')
ln = require('./../tools/getLine.coffee')
ajaxCalls = require('./../tools/ajaxCalls.coffee')
view = require('./actions/view.coffee')
viewAction = require('./actionEvents.coffee').view
getParentFolder = require('./../tools/getParentFolder.coffee')

fillNodeChilds = require('./../tools/fancyTreeFillNodeChilds.coffee')

# path Inode
source = dataProcessor(omenApi.getProp('inodes'))

config = require('./../omenApi.coffee').config

#! Events
openInode = (evt,data)->
    treeNodeType = data.node.data.refType
    path = Base64.decode(data.node.key)
    inode = omenApi.getProp('inodes')[data.node.key]
    if treeNodeType is 'directory'
        ajaxNavigation(path).then(-> fillNodeChilds(data.node))
    else if typeof inode != 'undefined'
        if inode.type == 'directory'
            ajaxNavigation(inode.path)
        else
            view(viewAction).apply($("figure[data-path='#{data.node.key}']").children().first())
    else
        ajaxNavigation(getParentFolder(path)).then(->
            view(viewAction).apply($("figure[data-path='#{data.node.key}']").children().first())
        )
    return false

#! CONFIG
fancyTreeConfig = {
    debugLevel: if config('omen.debug') then 4 else 0,
    extensions: ['edit', 'filter', 'glyph'],
    source: source,
    autoScroll: true,
    autoCollapse: true,
    icon: ((event, data)->
        node = data.node;
        # Create custom icons
        # must match InodeFileType.php
    
        switch node.data.refType
            when 'archive'
                return 'mdi mdi-leftPannelIcon mdi-zip-box-outline'
            when 'audio'
                return 'mdi mdi-leftPannelIcon mdi-file-image-outline'
            when 'video'
                return 'mdi mdi-leftPannelIcon mdi-file-video-outline'
            when 'image'
                return 'mdi mdi-leftPannelIcon mdi-file-image-outline'
            when 'pdf'
                return 'mdi mdi-leftPannelIcon mdi-file-pdf-outline'
            when 'text'
                return 'mdi mdi-leftPannelIcon mdi-note-text-outline'
            when 'file'
                return 'mdi mdi-leftPannelIcon mdi-file-outline'
            when 'writer'
                return 'mdi mdi-leftPannelIcon mdi-file-word-outline'
            when 'calc'
                return 'mdi mdi-leftPannelIcon mdi-file-excel-outline'
            when 'impress'
                return 'mdi mdi-leftPannelIcon mdi-file-powerpoint-outline'
            when 'diskimage'
                return 'mdi mdi-leftPannelIcon mdi-disc'
            when 'executable'
                return 'mdi mdi-leftPannelIcon mdi-console'
            when null
                return 'mdi mdi-leftPannelIcon mdi-folder-outline'
        # Exit without returning a value: continue with default processing.
    ),
    glyph: {
        # The preset defines defaults for all supported icon types.
        # It also defines a common class name that is prepended (in this case 'fa ')
        preset: 'awesome4',
        map: {
            _addClass: '',
            checkbox: 'mdi mdi-leftPannelIcon mdi-checkbox-blank-outline',
            checkboxSelected: 'mdi mdi-leftPannelIcon mdi-checkbox-marked',
            checkboxUnknown: 'mdi mdi-checkbox-intermediate',
            radio: 'mdi mdi-leftPannelIcon mdi-radiobox-blank',
            radioSelected: 'mdi mdi-leftPannelIcon mdi-radiobox-marked',
            radioUnknown: 'mdi mdi-leftPannelIcon mdi-radiobox-blank',
            dragHelper: 'mdi mdi-leftPannelIcon mdi-drag-variant',
            dropMarker: 'mdi mdi-leftPannelIcon mdi-arrow-all',
            error: 'mdi mdi-leftPannelIcon mdi-alert-cricle-outline',
            expanderClosed: 'mdi mdi-leftPannelIcon mdi-menu-right',
            expanderLazy: 'mdi mdi-leftPannelIcon mdi-chevron-right',
            expanderOpen: 'mdi mdi-leftPannelIcon mdi-menu-down',
            loading: 'mdi mdi-leftPannelIcon mdi-spin mdi-loading',
            nodata: 'mdi mdi-leftPannelIcon mdi-emoticon-neutral-outline',
            noExpander: '',
            # Default node icons.
            # (Use tree.options.icon callback to define custom icons based on node data)
            doc: 'mdi mdi-leftPannelIcon mdi-file-outline',
            docOpen: 'mdi mdi-leftPannelIcon mdi-file-outline',
            folder: 'mdi mdi-leftPannelIcon mdi-folder-outline',
            folderOpen: 'mdi mdi-leftPannelIcon mdi-folder-open-outline'
        }
    },
    init: ->
        ftree = fancyTree.getTree('#leftPanelTreeView')
        ftree.expandAll()
        rootNode = ftree .getRootNode()
        fillNodeChilds(rootNode).then(->
            rootNode.sortChildren(((a, b)->
                x = "#{a.data.refType}#{a.title.toLowerCase()}"
                y = "#{b.data.refType}#{b.title.toLowerCase()}"

                return x == y ? 0 : x > y ? 1 : -1
            ), true)
            key = Base64.encode(decodeURIComponent(getUrlLocationParameter('path')).replace(/\/?$/, ''))
            ftree.activateKey(key, { activeVisible: true})
        )

        readyEvent.resolve()
}
if config('omen.doubleClickToOpen') then fancyTreeConfig['dblclick'] = openInode
else fancyTreeConfig['click'] = openInode

#! INIT
$('#leftPanelTreeView').fancytree(fancyTreeConfig)

omenApi.setProp 'LeftPanelFancyTree', fancyTree.getTree('#leftPanelTreeView')
