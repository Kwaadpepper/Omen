timeout = null
waiting = []

# fadeIn on change page
window.onbeforeunload =  (e)->
    $('#loadingSplash').fadeIn()
    undefined

# fadeOut when ready to show
readyToShow = ->
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