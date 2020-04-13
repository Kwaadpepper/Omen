module.exports = (jsCode, pdfJS, pdfJSWorker, pdfCSS, pdfWebViewerJSUrl, pdfWebViewerCSSUrl, cspToken)->

    """
    
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Omen PDF viewer</title>
        <link nonce="#{cspToken}" rel="stylesheet" href="#{pdfCSS}">
        <link nonce="#{cspToken}" rel="stylesheet" href="#{pdfWebViewerCSSUrl}">
        <script nonce="#{cspToken}" src=#{pdfJS}></script>
        <script nonce="#{cspToken}" src=#{pdfJSWorker}></script>
        <script nonce="#{cspToken}" src=#{pdfWebViewerJSUrl}></script>
    </head>
    <body>
        <div id="viewport"></div>
        <script nonce="#{cspToken}">
        (function() {
            #{jsCode}
         })();
        </script>
    </body>
    </html>


    """