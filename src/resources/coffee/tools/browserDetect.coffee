module.exports = (->
    # https://stackoverflow.com/questions/9847580/how-to-detect-safari-chrome-ie-firefox-and-opera-browser
    # duck typing detection

    # Opera 8.0+
    isOpera = (!!window.opr and !!opr.addons) or !!window.opera or navigator.userAgent.indexOf(' OPR/') >= 0

    # Firefox 1.0+
    isFirefox = typeof InstallTrigger != 'undefined'

    # Safari 3.0+ "[object HTMLElementConstructor]" 
    isSafari = /constructor/i.test(window.HTMLElement) or ((p) ->
        p.toString() == '[object SafariRemoteNotification]'
    )(!window['safari'] or typeof safari != 'undefined' and safari.pushNotification)

    # Internet Explorer 6-11
    isIE = `/*@cc_on!@*/false || !!document.documentMode`

    # Edge 20+
    isEdge = !isIE and !!window.StyleMedia

    # Chrome 1 - 79
    isChrome = !!window.chrome and (!!window.chrome.webstore or !!window.chrome.runtime)

    # Edge (based on chromium) detection
    isEdgeChromium = isChrome and navigator.userAgent.indexOf('Edg') != -1

    # Blink engine detection
    isBlink = (isChrome or isOpera) and !!window.CSS

    switch true
          
        # Opera 8.0+
        when isOpera
            'opera'

        # Firefox 1.0+
        when isFirefox
            'firefox'

        # Safari 3.0+ "[object HTMLElementConstructor]" 
        when isSafari
            'safari'

        # Internet Explorer 6-11
        when isIE
            'internetexplorer'

        # Edge 20+
        when isEdge
            'edge'

        # Chrome 1 - 79
        when isChrome
            'chrome'

        # Edge (based on chromium) detection
        when isEdgeChromium
            'chromium'

        # Blink engine detection
        when isBlink
            'blink'

        else
            false
)()