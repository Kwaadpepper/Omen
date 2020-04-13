FuzzySet = require('fuzzyset.js')
fuzzysearch = require('fuzzysearch')

# module scope
filterTimeOut = null
gInodeType = null
gInputTextValue = ''

showAll = true
showFiles = false
showArchives = false
showImages = false
showVideos = false
showAudios = false

filterFigures = ->

    switch gInodeType
        when 'file'
            showFiles = !showFiles
        when 'archive'
            showArchives = !showArchives
        when 'image'
            showImages = !showImages
        when 'video'
            showVideos = !showVideos
        when 'audio'
            showAudios = !showAudios

    if showFiles or showArchives or showImages or showVideos or showAudios then showAll = false else showAll = true


    # SHOW OR HIDE FIGURE BY FILE TYPE
    figures = document.getElementById('inodesContainer').children

    for figure,k in figures

        if k == 0 then continue # ignore viewListTopBar

        if gInputTextValue.length < 2
            figure.classList.remove('fuzzHide')
        else

            lowerCaseName = figure.getElementsByTagName('figcaption')[0].textContent.toLowerCase()

            # permissive fuzzy search
            # useLevenshtein = true, gramSizeLower = 1, gramSizeUpper = 1
            fuzzyset = new FuzzySet([lowerCaseName], true, 1, 1)

            # Use partial lower match
            # fuzzymatch (no levenstein, all input chars must be present)
            # fuzzy (permissive + levenstein)
            if lowerCaseName.indexOf(gInputTextValue) != -1 or fuzzysearch(gInputTextValue, lowerCaseName) or fuzzyset.get(gInputTextValue, null, 0.2) != null
                figure.classList.remove('fuzzHide')
            else
                figure.classList.add('fuzzHide')

        # do only if figure is File
        if figure.classList.contains('figureFile')
            switch true

                when figure.classList.contains('ext-archive')
                    if showArchives or showAll
                        figure.classList.remove('typeHide')
                    else
                        figure.classList.add('typeHide')

                when figure.classList.contains('ext-image')
                    if showImages or showAll
                        figure.classList.remove('typeHide')
                    else
                        figure.classList.add('typeHide')

                when figure.classList.contains('ext-video')
                    if showVideos or showAll
                        figure.classList.remove('typeHide')
                    else
                        figure.classList.add('typeHide')

                when figure.classList.contains('ext-audio')
                    if showAudios or showAll
                        figure.classList.remove('typeHide')
                    else
                        figure.classList.add('typeHide')

                else # is any other file
                    if showFiles or showAll
                        figure.classList.remove('typeHide')
                    else
                        figure.classList.add('typeHide')

        if figure.classList.contains('typeHide') or figure.classList.contains('fuzzHide')
            figure.classList.add('d-none')
        else if not figure.classList.contains('fuzzHide') and figure.classList.contains('typeHide')
            figure.classList.remove('d-none')
        else if figure.classList.contains('fuzzHide') and not figure.classList.contains('typeHide')
            figure.classList.add('d-none')
        else if not figure.classList.contains('fuzzHide') and not figure.classList.contains('typeHide')
            figure.classList.remove('d-none')
    

    # END SHOW OR HIDE FIGURE BY FILE TYPE   

module.exports = (InodeType)->
    (event)->
        
        gInodeType = InodeType

        if this.tagName is "INPUT"
            gInputTextValue = this.value.toLowerCase()
            clearTimeout(filterTimeOut)
            filterTimeOut = setTimeout filterFigures.bind(this), 400

        if this.tagName is "BUTTON"
            # update button status
            this.classList.toggle('active')
            filterFigures()
        
        undefined # return nothing