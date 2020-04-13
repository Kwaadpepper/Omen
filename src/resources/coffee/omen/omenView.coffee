# load components
Split = require('split.js').default
SimpleBar = require('simplebar').default

omen = require './../omenApi.coffee'

sizes = localStorage.getItem 'split-sizes'

leftPanelId = '#leftPanel'
viewInodesId = '#viewInodes'
gutter = '#omenView .gutter'

leftPanelVisible = true;
scrollTimer = null;

if sizes
    sizes = JSON.parse sizes
else
    # in percent
    sizes = [
        $('#leftPanel').width() * 100 / Math.max(document.documentElement.clientWidth, window.innerWidth || 0),
        $('#viewInodes').width() * 100 / Math.max(document.documentElement.clientWidth, window.innerWidth || 0)
    ]

coverSizesDisplayValue = ->
    sizes = omen.splitter.getSizes()
    document.getElementById('viewInodes').style.setProperty("--content", "'#{Math.round(sizes[1])} %'")
    document.getElementById('leftPanel').style.setProperty("--content", "'#{Math.round(sizes[0])} %'")

setConfig =(sizes, minSize = [150, 300])->
    if sizes[0] in [0, 100]
        gutterSize = 0
    else
        gutterSize = 4
    return {
        sizes: sizes,
        minSize: minSize,
        direction: 'horizontal',
        gutterSize: gutterSize,
        elementStyle: ((dimension, size, gutterSize)->
            return {
                'flex-basis': 'calc(' + size + '% - ' + gutterSize + 'px + 2px)',
                'max-width': 'calc(' + size + '% - ' + gutterSize + 'px + 2px)'
            }
        ),
        gutterStyle: ((dimension, gutterSize)->
            return {
                'flex-basis': gutterSize + 'px',
                'max-width': gutterSize + 'px'
            }
        ),
        onDragStart: (()->
            document.getElementById('viewInodes').classList.add('resizeCover', 'resizeCoverHideChilds')
            # document.getElementById('leftPanel').classList.add('resizeCover', 'resizeCoverHideChilds')
            coverSizesDisplayValue()
        ),
        onDrag: (->
            coverSizesDisplayValue()
        ),
        onDragEnd: ((sizes)->
            document.getElementById('viewInodes').classList.remove('resizeCover')
            # document.getElementById('leftPanel').classList.remove('resizeCover')
            setTimeout (->
                document.getElementById('viewInodes').classList.remove('resizeCoverHideChilds')
                # document.getElementById('leftPanel').classList.remove('resizeCoverHideChilds')
                return
            ), 90
            localStorage.setItem('split-sizes', JSON.stringify(sizes))
        )
    }

omen.setProp('splitterLeftPanelVisible', leftPanelVisible)
omen.setProp('splitter', Split [leftPanelId, viewInodesId], setConfig(sizes))
omen.setMethod('splitterHideLeftPanel', ->
    omen.splitter.destroy()
    omen.splitter = Split [leftPanelId, viewInodesId], setConfig([0, 100], [0, 100])
    omen.setProp('splitterLeftPanelVisible', false)
    document.querySelector(gutter).style.display = 'none'
    return
)
omen.setMethod('splitterShowLeftPanel', ->
    omen.splitter.destroy()
    omen.splitter = Split [leftPanelId, viewInodesId], setConfig(sizes)
    omen.setProp('splitterLeftPanelVisible', true)
    document.querySelector(gutter).style.display = 'block'
    return
)

# Inodes Scroll
omen.setProp('simpleBarInodes', new SimpleBar(document.getElementById('viewInodes').children[1]))
omen.simpleBarInodes.getScrollElement().addEventListener('scroll', ->
      
    # disable animations
    inodesContainer = document.getElementById('inodesContainer')
    if not inodesContainer.classList.contains('notransitions') then inodesContainer.classList.add('notransitions')
    
    clearTimeout(scrollTimer);
    scrollTimer = setTimeout(->
        inodesContainer.classList.remove('notransitions')
    , 400)
)

# left panel inodes scroll
omen.setProp('simpleBarLeftPanel', new SimpleBar(document.getElementById('leftPanel').children[1]))
