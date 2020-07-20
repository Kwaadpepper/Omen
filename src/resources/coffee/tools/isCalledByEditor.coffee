getUrlLocationParameter = require('./getUrlLocationParameter.coffee')

module.exports = (editorLookFor)->
    editor = getUrlLocationParameter('editor')
    switch editor
        when 'tinymce', 'ckeditor', 'button'
            return true
        else return false