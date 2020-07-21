getUrlLocationParameter = require('./getUrlLocationParameter.coffee')

module.exports = (editorLookFor)->
    editor = getUrlLocationParameter('editor')
    switch editor
        when 'tinymce', 'ckeditor', 'popup'
            return true
        else return false