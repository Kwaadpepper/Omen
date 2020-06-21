config = require('./../omenApi.coffee').config
downloadActionEvent = require('./actionEvents.coffee').download
# https://github.com/fengyuanchen/cropperjs#known-issues (ios memory limit)
CropperJS = require('cropperjs')
lazyload = require('lazyload')
require('jquery-ui/ui/widgets/resizable')

resizeAction = require('./actionEvents.coffee').resizeImage
cropAction = require('./actionEvents.coffee').cropImage
ajax = require('../tools/ajaxCalls.coffee')
lockUi = require('./../tools/lockUi.coffee')
progressbar = require('./../tools/progressbar.coffee')
logException = require('./../tools/logException.coffee')
alert = require('./../tools/alert.coffee')
trans = require('./../tools/translate.coffee')
addInodeFigure = require('./actions/addInodeFigure.coffee')
omenApi = require('./../omenApi.coffee')
resetFilters = require('./actionEvents.coffee').resetFilters
applySort = require('./actionEvents.coffee').applySort
Base64 = require('js-base64').Base64

lazyLoadImage = null
currentViewMode = 0 # 0 crop Mode, 1 resize Mode
currentInode = null
cropperImage = null
currentRotation = 0
currentZoom = 1
vFlip = true #false = inverted
hFlip = true #false = inverted
originalHeight = null
originalWidth = null

# Dom Elements
imageEditorModal = $('#imageEditorModal')
imageEditorCropPannel = $('#imageCropper')
imageEditorResizePannel = $('#imageResizer')
imageCropElement = $('#imageEditorModal img').first()
imageResizeElement = $('#imageEditorModal img').last()
imageShowCrop = $('#imageEditorCrop')
imageShowResize = $('#imageEditorResize')
imageEditorRatioInputs = $('#imageEditorRatioInputGroup input')
imageEditorCropModeButtons = $('#imageEditorCropModeInputGroup button')
imageResizerHeightOriginal = $('#imageResizerHeightOriginal')
imageResizerWidthOriginal = $('#imageResizerWidthOriginal')

# INPUTS
rotateLeftButton = $('#imageEditorRotateInputGroup button').first()
rotateRightButton = $('#imageEditorRotateInputGroup button').last()
rotateInput = $('#imageEditorRotateInputGroup input')
flipVerticalButton = $('#imageEditorFlipV')
flipHorizontalButton = $('#imageEditorFlipH')
resetButton = $('#imageEditorReset')
zoomOutButton = $('#imageEditorZoomInputGroup button').first()
zoomInButton = $('#imageEditorZoomInputGroup button').last()
zoomInput = $('#imageEditorZoomInputGroup input')
imageResizerHeightInput = $('#imageResizerHeightInput')
imageResizerWidthInput = $('#imageResizerWidthInput')
imageEditorSaveButton = $('#imageEditorSave')
imageEditorSaveAsNewButton = $('#imageEditorSaveNew')

#! Methods
limit = (int, min, max)->
    while int < min or int > max
        int = if int > 0 then int - Math.abs(min) else int + Math.abs(max)
    return int
getRotationAngle = ->
    if  isNaN Number.parseInt(rotateInput.val())
        rotateInput.val(0)
    currentRotation = limit(Number.parseInt(rotateInput.val()), -360, 360)
    rotateInput.val(currentRotation)
    return currentRotation
setRotationAngle = (angle)->
    currentRotation = limit(currentRotation + angle, -360, 360)
    rotateInput.val(currentRotation)
    cropperImage.rotateTo(currentRotation)
getZoomValue = ->
    if  isNaN Number.parseInt(zoomInput.val())
        zoomInput.val(100)
    currentZoom = Number.parseInt(zoomInput.val()) / 100
    if currentZoom < 0 then currentZoom = 0
    zoomInput.val(Number.parseInt(Math.round(currentZoom * 100)))
    return currentZoom
setZoomValue = (zoom)->
    currentZoom = getZoomValue() + zoom
    zoomInput.val(Number.parseInt(Math.round(currentZoom * 100)))
    cropperImage.zoomTo(currentZoom)
saveImage = (saveAsNew)->
    (->
        if currentViewMode == 0 # save crop mode
            lockUi.lock()
            progressbar.run(0.3)
            boxData = cropperImage.getData()
            params = {
                filepath: currentInode.path
            }
            $.extend(params, boxData)
            if saveAsNew then params.new = true
            ajax(cropAction.method, cropAction.url, params,
            ((inode)->
                lockUi.unlock()
                progressbar.end()
                omenApi.updateImageToken()
                if saveAsNew
                    setTimeout (->
                        addInodeFigure({ path: inode.path }).then(->
                            resetFilters()
                            applySort()
                            $("figure[data-path='#{Base64.encode(inode.path)}'")[0].scrollIntoView({ behavior: 'smooth' });
                        )
                        inodes = omenApi.getProp('inodes')
                        inodes[Base64.encode(inode.path)] = inode
                        omenApi.setProp('inodes', inodes)
                        imageEditorModal.modal('hide')
                        alert('success', trans('File cropped'), trans("${filename} has been cropped", { 'filename': inode.baseName }))
                        return
                    ), 10
                else
                    alert('success', trans('File cropped'), trans("${filename} has been cropped", { 'filename': currentInode.baseName }))
            ),
            ((error)->
                lockUi.unlock()
                progressbar.end()
                alert('danger', trans('Action failure'), trans("Could not crop file ${filename}, server said no", { 'filename': currentInode.baseName }))
                logException("Error Occured on crop  #{error.status} #{error.statusText} INODE => #{currentInode.path} URL => #{resizeAction.url}")
            ))
        else # save resize mode
            lockUi.lock()
            progressbar.run(0.3)
            params = {
                filepath: currentInode.path,
                fileheight: Math.round(imageResizeElement.height()),
                filewidth: Math.round(imageResizeElement.width())
            }
            if saveAsNew then params.new = true
            ajax(resizeAction.method, resizeAction.url, params,
            ((inode)->
                lockUi.unlock()
                progressbar.end()
                omenApi.updateImageToken()
                setTimeout (->
                    addInodeFigure({ path: inode.path }).then(->
                        resetFilters()
                        applySort()
                        $("figure[data-path='#{Base64.encode(inode.path)}'")[0].scrollIntoView({ behavior: 'smooth' });
                    )
                    inodes = omenApi.getProp('inodes')
                    inodes[Base64.encode(inode.path)] = inode
                    omenApi.setProp('inodes', inodes)
                    imageEditorModal.modal('hide')
                    alert('success', trans('File resized'), trans("${filename} has been resized", { 'filename': inode.baseName }))
                    return
                ), 10
            ),
            ((error)->
                lockUi.unlock()
                progressbar.end()
                alert('danger', trans('Action failure'), trans("Could not resize file ${filename}, server said no", { 'filename': currentInode.baseName }))
                logException("Error Occured on resize  #{error.status} #{error.statusText} INODE => #{currentInode.path} URL => #{resizeAction.url}")
            ))
    )

#! EVENTS
#* ROTATION
rotateLeftButton.on('click', -> setRotationAngle(-25))
rotateRightButton.on('click', -> setRotationAngle(+25))
rotateInput.on('input', ->
    getRotationAngle()
    setRotationAngle(0)
)
#* FLIP
flipVerticalButton.on('click', ->
    vFlip = !vFlip
    cropperImage.scale((if vFlip then 1 else -1), (if hFlip then 1 else -1))
)
flipHorizontalButton.on('click', ->
    hFlip = !hFlip
    cropperImage.scale((if vFlip then 1 else -1), (if hFlip then 1 else -1))
)
#* RESET
resetButton.on('click', ->
    if currentViewMode == 0
        cropperImage.reset()
    else
        imageResizeElement.css('height', originalHeight)
        imageResizeElement.parent().css('height', originalHeight)
        imageResizeElement.css('width', originalWidth)
        imageResizeElement.parent().css('width', originalWidth)
        imageResizerHeightInput.val(Math.round(imageResizeElement.height()))
        imageResizerWidthInput.val(Math.round(imageResizeElement.width()))
)
#* Change View
imageShowCrop.on('click', ->
    currentViewMode = 0
    imageEditorCropPannel.show()
    imageEditorResizePannel.hide()
)
imageShowResize.on('click', ->
    currentViewMode = 1
    imageEditorCropPannel.hide()
    imageEditorResizePannel.show()
)
#* Ratio Change
imageEditorRatioInputs.on('click', -> cropperImage.setAspectRatio Number.parseFloat($(this).val()))
#* Crop Mode Change
imageEditorCropModeButtons.first().on('click', ->
    cropperImage.setDragMode('crop')
    $(this).addClass('active')
    imageEditorCropModeButtons.last().removeClass('active')
)
imageEditorCropModeButtons.last().on('click', ->
    cropperImage.setDragMode('move')
    $(this).addClass('active')
    imageEditorCropModeButtons.first().removeClass('active')
)
#* Zoom
zoomOutButton.on('click', -> setZoomValue(-0.1))
zoomInButton.on('click', -> setZoomValue(0.1))
zoomInput.on('change', -> 
    getZoomValue()
    setZoomValue(0)
)
#* Image Resize
imageResizeStop = ((e)->
    imageResizerHeightInput.val(Math.round(imageResizeElement.height()))
    imageResizerWidthInput.val(Math.round(imageResizeElement.width()))
)
imageResizerHeightInput.on('input', ->
    imageResizeElement.css('height', $(this).val())
    imageResizeElement.parent().css('height', $(this).val())
)
imageResizerWidthInput.on('input', ->
    imageResizeElement.css('width', $(this).val())
    imageResizeElement.parent().css('width', $(this).val())
)
#* SAVE image
imageEditorSaveButton.on('click', saveImage())
imageEditorSaveAsNewButton.on('click', saveImage(true))

#! Init
imageEditorResizePannel.hide()
imageEditorCropModeButtons.first().addClass('active')
currentRotation = getRotationAngle()

imageEditorModal.on 'shown.bs.modal', ((e)->
    console.log 'shown'
)
imageEditorModal.on 'hidden.bs.modal', ((e)->
    lazyLoadImage.destroy()
    imageResizeElement.resizable('destroy')
    cropperImage.destroy()
)
imageCropElement.on('load', (e)->
    window.cropper = cropperImage = new CropperJS(imageCropElement[0])
)
imageResizeElement.on('load', (e)->
    originalWidth = Math.round($(this)[0].naturalWidth)
    originalHeight = Math.round($(this)[0].naturalHeight)
    imageResizerWidthOriginal.text(originalWidth + ' px')
    imageResizerHeightOriginal.text(originalHeight + ' px')
    imageResizerHeightInput.val(Math.round(imageResizeElement.height()))
    imageResizerWidthInput.val(Math.round(imageResizeElement.width()))

    imageResizeElement.resizable({
        stop: imageResizeStop
    })
)


module.exports = ((inode)->
    currentInode = inode
    inodeUrl = "#{downloadActionEvent.url}#{inode.path}"

    # reset inputs
    imageResizerHeightInput.val('')
    imageResizerWidthInput.val('')

    imageCropElement.attr('data-src', inodeUrl)
    lazyLoadImage = new lazyload document.querySelectorAll('#imageEditorModal img'), {
        root: null,
        rootMargin: '0px',
        threshold: 0
    }
    imageEditorModal.find('h5').text(inode.baseName)
    imageEditorModal.find('img').attr('data-src', inodeUrl)

    imageEditorModal.modal('show')
)