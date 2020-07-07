window.omenApi = require('../omenApi.coffee')
timeout = null
waiting = []

window.omenLoaded = 0

# fadeIn on change page
window.onbeforeunload =  (e)->
    $('.modal').modal('hide') # close all modals
    $('#loadingSplash').fadeIn()
    undefined

# fadeOut when ready to show
readyToShow = ->
    if omenApi.config('omen.debug')
        window.omenLoaded = 1
        window.dispatchEvent(new Event('omen.loaded'));
    $('#loadingSplash').fadeOut()

module.exports = {
    registerWaiting: ->
        index = (waiting.push false) - 1
        return $.Deferred().done(->
            waiting[index] = true
            clearTimeout timeout
            for k of waiting
                if waiting[k] == false then return null
            setTimeout readyToShow, 2000
        )
}