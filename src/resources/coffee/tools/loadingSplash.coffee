$splashScreen = $('#loadingSplash')

timeout = null
waiting = []

window.onbeforeunload =  (e)->
    $splashScreen.fadeIn()
    undefined

readyToShow = ->
    $splashScreen.fadeOut()

module.exports = {
    registerWaiting: ->
        index = waiting.push false
        return $.Deferred().done(->
            waiting[index] = true
            if waitStatus is false then return null for waitStatus of waiting
            clearTimeout timeout
            setTimeout readyToShow, 600
        )

}