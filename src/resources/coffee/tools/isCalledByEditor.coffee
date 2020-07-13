getUrlLocationParameter = require('./getUrlLocationParameter.coffee')

module.exports = ->
    editor = getUrlLocationParameter('editor')
    switch editor
        when 'tinymce' then return true
        when 'ckeditor' then return true
        else return false