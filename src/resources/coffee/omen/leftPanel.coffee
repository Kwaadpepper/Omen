import 'jquery.fancytree/dist/modules/jquery.fancytree.edit'
import 'jquery.fancytree/dist/modules/jquery.fancytree.filter'
import 'jquery.fancytree/dist/modules/jquery.fancytree.glyph'

fancyTree = require 'jquery.fancytree'
omen = require './../omenApi.coffee'
dataProcessor = require "./../tools/fancyTreeDataProcessor.coffee"

readyEvent = require('./../tools/loadingSplash.coffee').registerWaiting()


source = dataProcessor(omen.inodes)
leftPanelTree = '#leftPanelTreeView'


$(leftPanelTree).fancytree {
  debugLevel: 4,
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
    readyEvent.resolve()
}

omen.setProp 'LeftPanelFancyTree', fancyTree.getTree(leftPanelTree)

readyEvent