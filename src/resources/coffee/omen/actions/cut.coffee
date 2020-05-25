omenApi = require('../../omenApi.coffee')
clipboard = require('../../tools/clipboard.coffee')

module.exports = ->
    inodes = omenApi.getProp('inodes')
    items = []
    $('#viewInodes figure input:checked').parents('figure').each((k,el)->
        items.push inodes[$(el).data('path')]
    )

    if items.length then clipboard.save(items, 'cut')