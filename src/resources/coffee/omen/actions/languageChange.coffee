setLocationParameters = require('../../tools/setLocationParameters.coffee')

module.exports = (action)->
    (event)->

        # redirect changing locale
        window.location.replace(setLocationParameters({
            'locale': $(this).data('locale')
        }))
        false