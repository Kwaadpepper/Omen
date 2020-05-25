Draggable = require('@shopify/draggable').Draggable

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
            console.log 'move ',$el, ' to ',$lastTarget
)