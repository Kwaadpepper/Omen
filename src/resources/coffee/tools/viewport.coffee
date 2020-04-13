module.exports = viewport = {}

$( "body" ).append """
    <div class='d-none' id='omenViewportCheck'>
        <span class='d-block'></span>
        <span class='d-sm-block'></span>
        <span class='d-md-block'></span>
        <span class='d-lg-block'></span>
        <span class='d-xl-block'></span>
    </div>"""

$viewportCheck = $ '#omenViewportCheck'
$viewPortXS = $viewportCheck.children '.d-block'
$viewPortSM = $viewportCheck.children '.d-sm-block'
$viewPortMD = $viewportCheck.children '.d-md-block'
$viewPortLG = $viewportCheck.children '.d-lg-block'
$viewPortXL = $viewportCheck.children '.d-xl-block'

viewport.isUnder = (target)->
    switch target
        when 'xs'
            $viewPortXS.css('display') is 'inline'
        when 'sm'
            $viewPortSM.css('display') is 'inline'
        when 'md'
            $viewPortMD.css('display') is 'inline'
        when 'lg'
            $viewPortLG.css('display') is 'inline'
        when 'xl'
            $viewPortXL.css('display') is 'inline'
        else
            false

viewport.isAbove = (target)->
    switch target
        when 'xl'
            $viewPortXL.css('display') is 'block'
        when 'lg'
            $viewPortLG.css('display') is 'block'
        when 'md'
            $viewPortMD.css('display') is 'block'
        when 'sm'
            $viewPortSM.css('display') is 'block'
        when 'xs'
            $viewPortXS.css('display') is 'block'
        else
            false

viewport.mediaQuery = ->
    for mediaQuery in ['xl', 'lg', 'md', 'sm', 'xs']
        if viewport.isAbove(mediaQuery)
            return mediaQuery
    return false