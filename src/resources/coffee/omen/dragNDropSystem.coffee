Draggable = require('@shopify//draggable/lib/es5/draggable').default
omenApi = require('./../omenApi.coffee')
alert = require('./../tools/alert.coffee')
trans = require('./../tools/translate.coffee')
moveTo = require('./actions/moveTo.coffee')
getUrlLocationParameter = require('./../tools/getUrlLocationParameter.coffee')
getParentFolder = require('./../tools/getParentFolder.coffee')
Base64 = require('js-base64').Base64

$el = null
$lastTarget = null
moveSize = {}

container = document.getElementById('inodesContainer')

draggable = new Draggable(container, {
    distance: 80,
    delay: 300
})

draggable.on('drag:start', (evt)->
    $el = $(evt.data.source)
    if $el.hasClass('Root') then evt.cancel()
    moveSize.width = parseFloat($el.css('width')) * 0.6
    moveSize.height = parseFloat($el.css('height')) * 0.6
    $el.css({'z-index': 99999, 'width': "#{moveSize.width}px", 'height': "#{moveSize.height}px", opacity: 0.6})
)

draggable.on('drag:over', (evt)->
    $lastTarget = $(evt.data.over)
    $lastTarget.css({'background-color': 'yellow'})
    $el.css({'visiblity': 'hidden'})
)
draggable.on('drag:out', (evt)->
    $el.css({'visiblity': ''})
    $lastTarget.css({'background-color': ''})
    $lastTarget = null
)

draggable.on('drag:stop', ()->
    if $lastTarget
        $el.css({'visiblity': ''})
        $lastTarget.css({'background-color': ''})
        if $lastTarget.hasClass('figureDirectory') and $lastTarget.data('path') != $el.data('path')
            inodes = omenApi.getProp('inodes')
            sourcePath = $el.data('path')
            # keep in mind elements
            sourcePath = inodes[sourcePath].path
            if $lastTarget.hasClass('Root')
                destPath = getParentFolder(decodeURIComponent(getUrlLocationParameter('path')))
            else
                destPath = inodes[$lastTarget.data('path')].path
            console.log sourcePath,destPath
            moveTo(sourcePath, destPath).done(->
                inodes = omenApi.getProp('inodes')
                if typeof inodes[sourcePath] != 'undefined'
                    delete inodes[sourcePath]
                    $("figure[data-path='#{sourcePath}'").remove()
                    alert('success', trans('Moved'), trans("Element was move successfully"))
            ).fail((message)->
                if typeof message != 'undefined' and message.length
                    alert('danger', trans('Move failed'), message)
                else
                    alert('danger', trans('Move failed'), trans("Element could not be moved, server said no"))
            )
)