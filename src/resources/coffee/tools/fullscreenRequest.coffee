# https://www.w3schools.com/jsref/met_element_exitfullscreen.asp
module.exports = (cssSelector, exit = false, callBack = ()->)->
    ->
        callBack()
    
        if exit # want to exit fullscreen 
            if ( # is indeed in fullscreen
                document.fullscreenElement or # Standard syntax
                document.webkitFullscreenElement or # Chrome, Safari and Opera syntax
                document.mozFullScreenElement or # Firefox syntax
                document.msFullscreenElement # IE/Edge syntax
            )
                # then exit fullscreen
                if document.exitFullscreen
                    document.exitFullscreen()
                else if document.mozCancelFullScreen # Firefox
                    document.mozCancelFullScreen()
                else if document.webkitExitFullscreen # Chrome, Safari and Opera
                    document.webkitExitFullscreen()
                else if document.msExitFullscreen # IE/Edge
                    document.msExitFullscreen()
                return
        else # want to go fullscreen
            #Get the element you want displayed in fullscreen mode (a video in this example):
            elem = $(cssSelector).get(0)

            # When the openFullscreen() function is executed, open the video in fullscreen.
            # Note that we must include prefixes for different browsers, as they don't support the requestFullscreen method yet

            if elem.requestFullscreen
                elem.requestFullscreen()
            else if elem.mozRequestFullScreen # Firefox
                elem.mozRequestFullScreen()
            else if elem.webkitRequestFullscreen # Chrome, Safari and Opera
                elem.webkitRequestFullscreen()
            else if elem.msRequestFullscreen # IE/Edge
                elem.msRequestFullscreen()
