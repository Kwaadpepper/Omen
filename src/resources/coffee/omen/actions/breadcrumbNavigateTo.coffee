getUrlLocationParameter = require('./../../tools/getUrlLocationParameter.coffee')
ajaxNavigation = require('./../../tools/ajaxNavigation.coffee')

module.exports = ->
    (e)->
        ajaxNavigation(decodeURIComponent(getUrlLocationParameter('path', this.href)))
        e.preventDefault()
        false