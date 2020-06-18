hash = require('./stringHash.coffee')
timeouts = {}

module.exports = (func, rRate = 1000)->
    id = hash(func.toString())
    if not timeouts[id]
        timeouts[id] = true
        setTimeout(->
            timeouts[id] = false
            func.apply(null, Array.prototype.shift.apply(arguments))
        , rRate)