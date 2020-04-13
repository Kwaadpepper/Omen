omenApiHolder = null;

class OmenApi

    constructor: ->

    get: ->
        @
        
    setProp: (property, value)->
        @[property] = value
    setMethod: (methodName, value)->
        Object.getPrototypeOf(@)[methodName] = value

module.exports = ((omenApiHolder)->
    if omenApiHolder is null then omenApiHolder = new OmenApi()
)(omenApiHolder)
