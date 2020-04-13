omen = require './../omenApi.coffee'
viewport = require './../tools/viewport.coffee'

# Jquery Elements
$gutter = $('#omenView .gutter')
$omenView = $('#omenView')
$leftPanel = $('#leftPanel')
$leftPanelToggler = $('#leftPanelToggler')

# ! LeftPanel
#* is opened with class .leftPanelOpened on XS media query
#* is opened with split.js above that

# Click Event
$leftPanelToggler.on 'click', (event) ->
    if viewport.mediaQuery() is'xs'
        $leftPanelToggler.toggleClass 'active'
        $omenView.toggleClass 'leftPanelOpened'
        setTimeout (->
            $leftPanel.toggleClass 'leftPanelTransition'
            return
        ), 50
    else
        $leftPanelToggler.toggleClass 'active'
        if omen.splitterLeftPanelVisible
            omen.splitterHideLeftPanel()
        else
            omen.splitterShowLeftPanel()
    return

# Resize Event
$(window).on('resize', ->
    if viewport.mediaQuery() is 'xs'
        $leftPanelToggler.removeClass 'active'
        $omenView.removeClass 'leftPanelOpened'
        $leftPanel.removeClass 'leftPanelTransition'
    else
        if omen.splitterLeftPanelVisible
            $leftPanelToggler.removeClass 'active'
            omen.splitterShowLeftPanel()
        else
            $leftPanelToggler.addClass 'active'
            omen.splitterHideLeftPanel()

)