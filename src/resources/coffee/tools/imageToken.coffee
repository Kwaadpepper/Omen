omenApi = require('./../omenApi.coffee')
uuid = require('./uuid.coffee')

module.exports = (->
    omenApi.setMethod('updateImageToken', ->
        omenApi.setProp('imageToken', uuid().replace(/_/g, ''))
    )
    omenApi.setMethod('getImageToken', ->
        omenApi.getProp('imageToken')   
    )
    omenApi.updateImageToken()
)()