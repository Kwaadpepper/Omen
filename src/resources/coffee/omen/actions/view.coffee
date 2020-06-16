lazyload = require('lazyload')
hightlightJS = require('highlight.js')
omenApi = require('./../../omenApi.coffee')
browserSupportedImage = require('../../tools/browserSupportedImages.coffee')
ajaxCalls = require('./../../tools/ajaxCalls.coffee')
logException = require('./../../tools/logException.coffee')
ln = require('./../../tools/getLine.coffee')
makeFullscreen = require('./../../tools/fullscreenRequest.coffee')
onFullScreenChange = require('./../../tools/fullscreenChangeEvent.coffee')
pdfInjector = require('./../../tools/pdfInjector.coffee')
alert = require('./../../tools/alert.coffee')
trans = require('./../../tools/translate.coffee')
mediaElement = require('./../../tools/mediaElement.coffee')
imageEditor = require('./../imageEditor.coffee')
textEditor = require('./../textEditor.coffee')
require('wheelzoom')

figureElement = null
currentInode = null


# ANCHOR Image Modal Vars
#*========================== Image Modal VARS ==================================
imageEditButton = $('#imageViewerModal button.edit')
imageModal = $('#imageViewerModal')
image = $('#imageViewerModal img')
imageZoom = null
imageModal.on 'click', 'button.imageModalDownload', -> figureElement.parents('figure').find('button.actionDownload').click()
imageModal.on 'click', 'button.imageModalFullscreen', makeFullscreen('#imageViewerModal .modal-content', false) # go fullscreen
imageModal.on 'click', 'button.imageModalFullscreenExit', makeFullscreen('#imageViewerModal .modal-content', true) # exit fullscreen
imageModal.on 'hide.bs.modal', makeFullscreen('#imageViewerModal .modal-content', true) # exit fullscreen
onFullScreenChange('#imageViewerModal .modal-content', ->
    # switch fullscreen buttons on fullScreen Exit
    imageModal.find('button.imageModalFullscreen').toggleClass('d-none')
    imageModal.find('button.imageModalFullscreenExit').toggleClass('d-none')
)
imageEditButton.on 'click',->
    imageModal.modal('hide')
    imageEditor(currentInode)
imageModal.on 'hidden.bs.modal', (e)-> # ON Modal Hidden
    #hide error message
    imageErrorMessage.addClass('d-none')
    # reset elements data
    imageModal.find('h5').text('')
    imageModal.find('img').attr('src', '')
    # destroy lazyload
    if(lazyLoadImage) then lazyLoadImage.destroy()
    if imageZoom then triggerEvent(imageZoom, 'wheelzoom.destroy'); imageZoom = null

image.on 'load', (->
    if not imageZoom then imageZoom = wheelzoom(image[0])
)
lazyLoadImage = null;
imageErrorMessage = imageModal.find('h4')
#*======================= END Image Modal VARS ==================================

# ANCHOR Text Modal Vars
#*========================= Text Modal VARS =====================================
textEditButton = $('#textViewerModal button.edit')
textModal = $('#textViewerModal')
textModal.on 'click', 'button.textModalDownload', -> figureElement.parents('figure').find('button.actionDownload').click()
textModal.on 'click', 'button.textModalFullscreen', makeFullscreen('#textViewerModal .modal-content', false) # go fullscreen
textModal.on 'click', 'button.textModalFullscreenExit', makeFullscreen('#textViewerModal .modal-content', true) # exit fullscreen
textModal.on 'hide.bs.modal', makeFullscreen('#textViewerModal .modal-content', true) # exit fullscreen
onFullScreenChange('#textViewerModal .modal-content', ->
    # switch fullscreen buttons on fullScreen Exit
    textModal.find('button.textModalFullscreen').toggleClass('d-none')
    textModal.find('button.textModalFullscreenExit').toggleClass('d-none')
)
textEditButton.on 'click',->
    textModal.modal('hide')
    textEditor(currentInode)
textModal.on 'hidden.bs.modal', (e)->
    # #hide error message
    # imageErrorMessage.addClass('d-none')
    # reset elements data
    textModal.find('h5').text('')
    textModal.find('pre').text('')
#*======================== END Text Modal VARS ==================================

# ANCHOR PDF Modal Vars
#*========================= PDF Modal VARS =====================================
pdfModal = $('#pdfViewerModal')
pdfModal.on 'click', 'button.pdfModalDownload', -> figureElement.parents('figure').find('button.actionDownload').click()
pdfModal.on 'click', 'button.pdfModalFullscreen', makeFullscreen('#pdfViewerModal .modal-content', false) # go fullscreen
pdfModal.on 'click', 'button.pdfModalFullscreenExit', makeFullscreen('#pdfViewerModal .modal-content', true) # exit fullscreen
pdfModal.on 'hide.bs.modal', makeFullscreen('#pdfViewerModal .modal-content', true) # exit fullscreen
onFullScreenChange('#pdfViewerModal .modal-content', ->
    # switch fullscreen buttons on fullScreen Exit
    pdfModal.find('button.pdfModalFullscreen').toggleClass('d-none')
    pdfModal.find('button.pdfModalFullscreenExit').toggleClass('d-none')
)
pdfModal.on 'hidden.bs.modal', (e)->
    # #hide error message
    # imageErrorMessage.addClass('d-none')
    # reset elements data
    pdfModal.find('h5').text('')
    pdfModal.find('pre').text('')
#*======================== END PDF Modal VARS ==================================

# ANCHOR Document Modal Vars
#*========================= Document Modal VARS =====================================
documentModal = $('#documentViewerModal')
documentModal.attr('src', 'about:blank') # reset iframe
documentModal.on 'click', 'button.documentModalDownload', -> figureElement.parents('figure').find('button.actionDownload').click()
documentModal.on 'click', 'button.documentModalFullscreen', makeFullscreen('#documentViewerModal .modal-content', false) # go fullscreen
documentModal.on 'click', 'button.documentModalFullscreenExit', makeFullscreen('#documentViewerModal .modal-content', true) # exit fullscreen
documentModal.on 'hide.bs.modal', makeFullscreen('#documentViewerModal .modal-content', true) # exit fullscreen
onFullScreenChange('#documentViewerModal .modal-content', ->
    # switch fullscreen buttons on fullScreen Exit
    documentModal.find('button.documentModalFullscreen').toggleClass('d-none')
    documentModal.find('button.documentModalFullscreenExit').toggleClass('d-none')
)
documentModal.on 'hidden.bs.modal', (e)->
    # #hide error message
    # imageErrorMessage.addClass('d-none')
    # reset elements data
    documentModal.find('h5').text('')
    documentModal.find('pre').text('')
#*======================== END Document Modal VARS ==================================

# ANCHOR Video Modal Vars
#*========================= Video Modal VARS =====================================
videoModal = $('#videoViewerModal')
videoModal.on 'click', 'button.videoModalDownload', -> figureElement.parents('figure').find('button.actionDownload').click()
videoModal.on 'hidden.bs.modal', (e)->
    # #hide error message
    # imageErrorMessage.addClass('d-none')
    mediaElement.destroy()
    # reset elements data
    videoModal.find('h5').text('')
    videoModal.find('pre').text('')
#*======================== END Video Modal VARS ==================================

# ANCHOR Audio Modal Vars
#*========================= Video Modal VARS =====================================
audioModal = $('#audioViewerModal')
audioModal.on 'click', 'button.audioModalDownload', -> figureElement.parents('figure').find('button.actionDownload').click()

audioModal.on 'hidden.bs.modal', (e)->
    # #hide error message
    # imageErrorMessage.addClass('d-none')
    mediaElement.destroy()
    # reset elements data
    audioModal.find('h5').text('')
    audioModal.find('pre').text('')
#*======================== END Video Modal VARS ==================================

#! Disable fullscreen feature if browser does not allow it
if not (
    document.fullscreenEnabled or # Standard syntax
    document.webkitFullscreenEnabled or # Chrome, Safari and Opera syntax
    document.mozFullScreenEnabled or # Firefox syntax
    document.msFullscreenEnabled # IE/Edge syntax
)
    imageModal.find('button.imageModalFullscreen').hide()
    textModal.find('button.textModalFullscreen').hide()
    pdfModal.find('button.pdfModalFullscreen').hide()
    documentModal.find('button.documentModalFullscreen').hide()

module.exports = (action)->
    (event)->
        figureElement = $(this)
        fileBase64Path = figureElement.parents('figure').data('path')
        inodes = omenApi.getProp('inodes')
        inode = currentInode = inodes[fileBase64Path]

        # get the inode showable url
        url = if inode.visibility == 'public' then inode.url else action.url + inode.path

        switch inode.fileType

            # ANCHOR Image Modal
            #* =========== IMAGE MODAL =============================
            when 'image'

                # Check approx if browser can display image
                if (browserSupportedImage[inode.mimeType])
                    # test file exists
                    ajaxCalls(
                        'HEAD',
                        url,
                        null,
                        null,
                        null,
                        { 
                            complete : (jxhr)->
                                contentLength = jxhr.getResponseHeader('Content-Length')
                                if jxhr.status is not 200 or !contentLength
                                    alert('danger', trans('Error'), trans('Could not retrieve image file'))
                                    logException("Error Occured #{jxhr.status} #{jxhr.statusText} INODE => #{inode.path} URL => #{url}", "9#{ln()}")
                                else
                                    # Inject Image Data
                                    lazyLoadImage = new lazyload document.querySelectorAll('#imageViewerModal img'), {
                                        root: null,
                                        rootMargin: '0px',
                                        threshold: 0
                                    }
                                    imageModal.find('h5').text(inode.baseName)
                                    imageModal.find('img').attr('data-src', url)
                        }
                    )
                else
                    # Display Error message
                    imageErrorMessage.removeClass('d-none')
                    logException("Inode view is not supported by browser INODE => #{inode.path} URL => #{url}", "9#{ln()}")

                imageModal.modal('show')

            # ANCHOR Text Modal
            #* =========== TEXT MODAL =============================
            when 'text'

                textModal.find('h5').text(inode.baseName)
                ajaxCalls(
                    'GET',
                    url,
                    null,
                    (textData)->
                        textModal.find('pre').text(textData)
                        document.querySelectorAll('pre').forEach((block)->
                            hightlightJS.highlightBlock(block)
                        )
                    ,
                    (error)->
                        alert('danger', trans('Error'), trans('Could not retrieve text file'))
                        logException("Error Occured #{error.status} #{error.statusText} INODE => #{inode.path} URL => #{url}", "9#{ln()}")
                    ,
                    { dataType : 'html'}
                )

                textModal.modal('show')

            # ANCHOR PDF Modal
            #* =========== PDF MODAL =============================
            when 'pdf'

                # https://stackoverflow.com/questions/19654577/html-embedded-pdf-iframe

                pdfInjector(url)
                pdfModal.find('h5').text(inode.baseName).show()
                pdfModal.modal('show')

            # ANCHOR Document Modal
            #* =========== DOCUMENT MODAL =============================
            when 'writer'
            ,'calc'
            ,'impress'

                $('#documentViewerModalIFrame').attr('src', "https://docs.google.com/gview?url=#{encodeURI(url)}&embedded=true")
                documentModal.modal('show')
            
            when 'video'

                videoModal.find('h5').text(inode.baseName)
                mediaElement.inject(videoModal.find('video')[0], url)
                videoModal.modal('show')
                
            when 'audio'
                
                audioModal.find('h5').text(inode.baseName)
                mediaElement.inject(audioModal.find('audio')[0], url)
                audioModal.modal('show')

            else 
                # don't know how to display inode
                logException("dont know how to display INODE => #{inode.path} URL => #{url}", "9#{ln()}")




