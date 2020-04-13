refreshRate = 1000 # in seconds
refreshed = true

module.exports = (func, rRate = refreshRate)->
    if refreshed
        refreshed = !refreshed
        setTimeout (()->
            refreshed = !refreshed
            func.apply(null, Array.prototype.shift.apply(arguments))
        ), rRate