omenApi = require('../../omenApi.coffee')
clipboard = require('../../tools/clipboard.coffee')
alert = require('./../../tools/alert.coffee')
trans = require('./../../tools/translate.coffee')

module.exports = ->
    inodes = omenApi.getProp('inodes')
    items = []
    $('#viewInodes figure input:checked:visible').parents('figure').each((k,el)->
        items.push inodes[$(el).data('path')]
    )

    if items.length
        alert('info', trans('Clipboard'), trans("${n} elements in clipboard", { 'n': items.length }))
        clipboard.save(items, 'copy')
