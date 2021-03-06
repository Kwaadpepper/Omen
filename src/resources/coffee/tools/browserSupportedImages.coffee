browser = require './browserDetect.coffee'

# https://developer.mozilla.org/fr/docs/Web/HTML/Element/Img
#APNG  	image/apng 	.apng 	Chrome, Edge, Firefox, Opera, Safari
#BMP  	image/bmp 	.bmp 	Chrome, Edge, Firefox, Internet Explorer, Opera, Safari
#GIF  	image/gif 	.gif 	Chrome, Edge, Firefox, Internet Explorer, Opera, Safari
#ICO 	image/x-icon 	.ico, .cur 	Chrome, Edge, Firefox, Internet Explorer, Opera, Safari
#JPEG 	image/jpeg 	.jpg, .jpeg, .jfif, .pjpeg, .pjp 	Chrome, Edge, Firefox, Internet Explorer, Opera, Safari
#PNG 	image/png 	.png 	Chrome, Edge, Firefox, Internet Explorer, Opera, Safari
#SVG  	image/svg+xml 	.svg 	Chrome, Edge, Firefox, Internet Explorer, Opera, Safari
#TIFF 	image/tiff 	.tif, .tiff 	None built-in; add-ons required
#WebP 	image/webp 	.webp 	Chrome, Edge, Firefox, Opera
# 
# https://en.wikipedia.org/wiki/Comparison_of_browser_engines_(graphics_support)

module.exports = (->
    imageFormats = {
        'image/apng': 1, 
        'image/bmp': 2, 
        'image/gif': 3, 
        'image/x-icon': 4, 
        'image/jpeg': 5, 
        'image/png': 6, 
        'image/svg+xml': 7, 
        'image/webp': 8,
        'image/vnd.ms-photo': 9,
        'image/jxr': 10,
        'image/tiff': 11,
        'image/tiff-fx': 12
    }

    switch browser
        
        # Firefox 1.0+
        # Chrome 1 - 79
        # Edge (based on chromium) detection
        # Edge 20+
        # Blink engine detection
        # Opera 8.0+
        # Safari 3.0+ "[object HTMLElementConstructor]" 
        when 'firefox'
        ,'chrome'
        ,'edgechromium'
        ,'edge'
        ,'blink'
        ,'opera'
        ,'safari'
            delete imageFormats['image/vnd.ms-photo']
            delete imageFormats['image/jxr']
            delete imageFormats['image/tiff']
            delete imageFormats['image/tiff-fx']
            imageFormats
        
        # Internet Explorer 6-11
        when 'internetexplorer'
            delete imageFormats['image/apng']
            delete imageFormats['image/webp']

        # unkonwn
        else
            imageFormats
)()