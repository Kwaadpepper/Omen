config = require('./../omenApi.coffee').config
trans = require('./../tools/translate.coffee')
getUrlLocationParameter = require('./../tools/getUrlLocationParameter.coffee')
alert = require('./../tools/alert.coffee')
addInodeFigure = require('./actions/addInodeFigure.coffee')
omenApi = require('./../omenApi.coffee')
Base64 = require('js-base64').Base64
actions = require('./actionEvents.coffee')
ProgressBar = require('progressbar.js')
uuid = require('./../tools/uuid.coffee')
applySort = require('./actionEvents.coffee').applySort

uploadPath = null
progressBar = null
progressBarInterval = null

# handle locale
locales = require('./../omenApi.coffee').bootstrapInputLocales
uploadLocale = if locales.indexOf(config('omen.locale')) != -1 then config('omen.locale') else 'en'

$uploadForm = $('#uploadForm')
$uploadInput = $('#uploadInput')
$uploadButton = $uploadForm.find('button.uploadBtnUpload')
$browseButton = $uploadForm .find('button.browseBtnUpload')
$clearButton = $uploadForm .find('button.clearBtnUpload')
$pauseButton = $uploadForm .find('button.pauseBtnUpload')
$resumeButton = $uploadForm .find('button.resumeBtnUpload')
$cancelButton = $uploadForm .find('button.cancelBtnUpload')

$uploadButton.prop('disabled', true).show()
$resumeButton.prop('disabled', true).hide()
$pauseButton.prop('disabled', true).hide()
$cancelButton.prop('disabled', true).hide()
$clearButton.prop('disabled', true).hide()

# File uploader config
$('#uploadInput').fileinput {
    language: uploadLocale, # changed

    # upload
    uploadAsync: true, # changed
    enableResumableUpload: true, # changed
    resumableUploadOptions: {
        chunkSize: 1024,
        maxThreads: 4,
        maxRetries: 3,
        showErrorLog: true
    },
    maxAjaxThreads: 2,
    uploadUrl: actions.upload.url,
    uploadExtraData : {
        _token: $('meta[name="csrf-token"]').attr('content'),
        filePath: "#{decodeURIComponent(getUrlLocationParameter('path'))}/"
    },
        
    # delete
    deleteUrl: actions.delete.url,
    deleteExtraData: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        filePath: "#{decodeURIComponent(getUrlLocationParameter('path'))}/"
    },

    # display
    showCaption: false, # changed
    showBrowse: false, # changed
    showPreview: true,
    showRemove: false, # changed
    showUpload: false, # changed
    showUploadStats: true,
    showCancel: false,
    showPause: false,
    showClose: false, # changed
    showUploadedThumbs: true,
    showConsoleLogs: true,

    # behavior
    browseOnZoneClick: false,
    autoReplace: false,
    autoOrientImage: false, # changed
    autoOrientImageInitial: true,
    focusCaptionOnBrowse: true,
    focusCaptionOnClear: true,
    required: false,


    rtl: false,
    hideThumbnailContent: false,
    encodeUrl: true,
    previewClass: '',
    captionClass: '',
    frameClass: 'krajee-default',
    mainClass: 'file-caption-main',

    theme: "krajee-explorer",

    # allowedFileExtensions: ['jpg', 'png', 'gif'],

    overwriteInitial: false,
    initialPreviewAsData: true,
    maxFileSize: 10000,
    removeFromPreviewOnError: true,
    previewFileType: 'any',

    fileSizeGetter: (bytes)->
        i = Math.floor(Math.log(bytes) / Math.log(1024))
        sizes = [trans('B'), trans('KB'), trans('MB'), trans('GB'), trans('TB'), trans('PB'), trans('EB'), trans('ZB'), trans('YB')]
        return (bytes / Math.pow(1024, i)).toFixed(2) * 1 + ' ' + sizes[i]
    ,

    fileActionSettings: {
        removeIcon: '<i class="mdi mdi-delete"></i>', # changed
        removeClass: 'btn btn-sm btn-kv btn-default btn-outline-secondary ',
        removeErrorClass: 'btn btn-sm btn-kv btn-danger', # changed
        uploadIcon: '<i class="mdi mdi-upload"></i>', # changed
        uploadClass: 'btn btn-sm btn-kv btn-default btn-outline-secondary',
        uploadRetryIcon: '<i class="mdi mdi-repeat"></i>', # changed
        downloadIcon: '<i class="mdi mdi-download"></i>', # changed
        downloadClass: 'btn btn-sm btn-kv btn-default btn-outline-secondary',
        zoomIcon: '<i class="mdi mdi-magnify-plus"></i>', # changed
        zoomClass: 'btn btn-sm btn-kv btn-default btn-outline-secondary',
        dragIcon: '<i class="mdi mdi-arrow-all"></i>', # changed
        dragClass: 'text-info',
        dragSettings: {},
        indicatorNew: '<i class="mdi mdi-plus-circle text-warning"></i>', # changed
        indicatorSuccess: '<i class="mdi mdi-check-circle text-success"></i>', # changed
        indicatorError: '<i class="mdi mdi-alert-circle text-danger"></i>', # changed
        indicatorLoading: '<i class="mdi mdi-sync-circle mdi-spin text-muted"></i>', # changed
        indicatorPaused: '<i class="mdi mdi-pause-circle text-primary"></i>' # changed
    },


    previewZoomButtonIcons: {
        prev: '<i class="mdi mdi-play mdi-flip-h"></i>', # changed
        next: '<i class="mdi mdi-play"></i>', # changed
        toggleheader: '<i class="mdi mdi-arrow-up-down"></i>', # changed
        fullscreen: '<i class="mdi mdi-arrow-expand-all"></i>', # changed
        borderless: '<i class="mdi mdi-arrow-top-right-bottom-left"></i>', # changed
        close: '<i class="mdi mdi-close"></i>' # changed
    },
    previewZoomButtonClasses: {
        prev: 'btn btn-navigate',
        next: 'btn btn-navigate',
        toggleheader: 'btn btn-sm btn-kv btn-default btn-outline-secondary',
        fullscreen: 'btn btn-sm btn-kv btn-default btn-outline-secondary',
        borderless: 'btn btn-sm btn-kv btn-default btn-outline-secondary',
        close: 'btn btn-sm btn-kv btn-default btn-outline-secondary'
    },
    browseIcon: '<i class="mdi mdi-folder-open"></i>&nbsp;', # changed
    browseClass: 'btn btn-primary',
    removeIcon: '<i class="mdi mdi-delete"></i>', # changed
    removeClass: 'btn btn-default btn-secondary',
    cancelIcon: '<i class="mdi mdi-cancel"></i>', # changed
    cancelClass: 'btn btn-default btn-secondary',
    pauseIcon: '<i class="mdi mdi-pause"></i>', # changed
    pauseClass: 'btn btn-default btn-secondary',
    uploadIcon: '<i class="mdi mdi-upload"></i>', # changed
    uploadClass: 'btn btn-default btn-secondary',
    msgValidationErrorClass: 'text-danger',
    msgValidationErrorIcon: '<i class="mdi mdi-alert-circle"></i> ', # changed
    msgErrorClass: 'file-error-message',
    progressThumbClass: 'progress-bar progress-bar-striped active',
    progressClass: 'progress-bar bg-success progress-bar-success progress-bar-striped active',
    progressInfoClass: 'progress-bar bg-info progress-bar-info progress-bar-striped active',
    progressCompleteClass: 'progress-bar bg-success progress-bar-success',
    progressPauseClass: 'progress-bar bg-primary progress-bar-primary progress-bar-striped active',
    progressErrorClass: 'progress-bar bg-danger progress-bar-danger',
    previewFileIcon: '<i class="mdi mdi-file"></i>', # changed

    pdfRendererUrl: '',
    pdfRendererTemplate: '<iframe class="kv-preview-data file-preview-pdf" src="{renderer}?file={data}" {style}></iframe>'
}

# events
$uploadForm.on('change', (event)->
    console.log 'change'
)

$uploadForm.on('filebatchselected', (event, files)->
    console.log 'filebatchselected' 
)

$uploadForm.on('fileclear', (event)->
    console.log 'fileclear'
)

# remove file from uplaod list
$uploadForm.on('fileremoved', (event, id, index)->
    console.log 'fileremoved'
)
# delete uploaded file
$uploadForm.on('filedeleted', (event, key, jqXHR, data)->
    console.log 'filedeleted'
)
$uploadForm.on('fileloaded', (event, file, previewId, index, reader)->
    console.log 'fileloaded'
    $uploadButton.prop('disabled', false)
    $clearButton.prop('disabled', false).show()
)
$uploadForm.on('filebatchpreupload', (outData, previewId, i)->
    console.log 'filebatchpreupload',outData
)

uploadedFileName = null
uploadedInode = null
$uploadForm.on('filechunksuccess', (e, id, index, retry, fm, rm, outData) ->
    uploadedFileName = outData.response.filename
    uploadedInode = outData.response.inode

    if outData.response.token then $('meta[name="csrf-token"]').attr('content', outData.response.token);
)
$uploadForm.on('fileuploaded', (event, t, h, f)->
    if uploadPath is decodeURIComponent(getUrlLocationParameter('path'))
        fileName = uploadedFileName
        filePath = "#{uploadPath}/#{fileName}"
        setTimeout (->
            addInodeFigure({ path: filePath })
            inodes = omenApi.getProp('inodes')
            inodes[Base64.encode(uploadedInode.path)] = uploadedInode
            omenApi.setProp('inodes', inodes)
            $uploadButton.prop('disabled', true)
            applySort()
            return
        ), 10
    if pendingFiles()
        $resumeButton.prop('disabled', true).hide()
        $pauseButton.prop('disabled', true).hide()
    else
        $uploadButton.prop('disabled', true)
        $browseButton.prop('disabled', false)
        $resumeButton.prop('disabled', true).hide()
        $pauseButton.prop('disabled', true).hide()
        $clearButton.prop('disabled', true).hide()
        $cancelButton.prop('disabled', true).hide()
        clearInterval progressBarInterval
        progressBar.destroy()
        alert('success', trans('Upload succeeded'), trans("All files were uploaded successfully"))
)
$uploadForm.on('fileuploaderror', (event)->
    $cancelButton.click()
)
$uploadForm.on('fileuploadsuccess', (event)->
    $uploadButton.prop('disabled', true)
)
$uploadForm.on('filecleared', (event)->
    $resumeButton.prop('disabled', true).hide()
    $pauseButton.prop('disabled', true).hide()
)
$uploadForm.on('fileuploadcancelled', (event)->
    $resumeButton.prop('disabled', true).hide()
    $pauseButton.prop('disabled', true).hide()
)

pendingFiles = ->
    !!$uploadInput.fileinput('getFileList').length

# Browse Action
$browseButton.on('click', (e)->

    console.log 'browse Action'

    # hack to trigger browse event
    $uploadInput.data('zoneClicked', true)
    $uploadInput.click()

    e.preventDefault()
    false
)

# Clear Action
$clearButton.on('click', (e)->

    console.log 'clear Action'

    $uploadInput.fileinput('cancel')
    $uploadInput.fileinput('clear')
    $resumeButton.prop('disabled', true).hide()
    $pauseButton.prop('disabled', true).hide()
    $cancelButton.prop('disabled', true).hide()
    $clearButton.prop('disabled', true).hide()
    $browseButton.prop('disabled', false)
    $uploadButton.prop('disabled', true)

    e.preventDefault()
    false
)

# Upload Action
$uploadButton.on('click', (e)->

    uploadPath = decodeURIComponent(getUrlLocationParameter('path'))

    progressBar = new ProgressBar.Line($('body > div.container-fluid')[1], {
        strokeWidth: 0.3,
        easing: 'easeInOut',
        duration: 1400,
        color: '#FFEA82',
        trailColor: '#eee',
        trailWidth: 1,
        svgStyle: {position: 'absolute', top: 0},
        from: {color: '#FFEA82'},
        to: {color: '#ED6A5A'},
        step: (state, bar) -> bar.path.setAttribute('stroke', state.color)
    })

    progressBarInterval = setInterval((->
        val = parseFloat($('div.kv-upload-progress div.progress-bar').attr('aria-valuenow')) / 100
        # errors in console are du to this https://github.com/kimmobrunfeldt/progressbar.js/issues/255
        progressBar.animate val
    ),500)

    $uploadInput.fileinput('upload')
    $resumeButton.prop('disabled', true).hide()
    $pauseButton.prop('disabled', false).show()
    $cancelButton.prop('disabled', false).show()
    $clearButton.prop('disabled', true).hide()
    $browseButton.prop('disabled', true)
    $uploadButton.prop('disabled', true)

    e.preventDefault()
    false
)

# Pause Action
$pauseButton.on('click', (e)->

    console.log 'pause Action'

    $uploadInput.fileinput('pause')
    $resumeButton.prop('disabled', false).show()
    $pauseButton.prop('disabled', true).hide()
    $cancelButton.prop('disabled', false).show()
    $clearButton.prop('disabled', true).hide()
    $browseButton.prop('disabled', false)
    $uploadButton.prop('disabled', true)

    e.preventDefault()
    false
)

# Resume Action
$resumeButton.on('click', (e)->

    console.log 'resume Action'

    $uploadInput.fileinput('resume')
    $resumeButton.prop('disabled', true).hide()
    $pauseButton.prop('disabled', false).show()
    $cancelButton.prop('disabled', false).show()
    $clearButton.prop('disabled', true).hide()
    $browseButton.prop('disabled', true)
    $uploadButton.prop('disabled', true)

    e.preventDefault()
    false
)

# Cancel Action
$cancelButton.on('click', (e)->

    console.log 'cancel Action'

    $uploadInput.fileinput('cancel')
    $resumeButton.prop('disabled', true).hide()
    $pauseButton.prop('disabled', true).hide()
    $cancelButton.prop('disabled', true).hide()
    $clearButton.prop('disabled', false).show()
    $browseButton.prop('disabled', false)
    $uploadButton.prop('disabled', false)

    e.preventDefault()
    false
)