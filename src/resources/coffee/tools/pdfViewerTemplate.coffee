trans = require('./translate.coffee')

module.exports = (jsCode, pdfJS, scale, pdfJSWorker, pdfCSS, pdfWebViewerJSUrl, pdfWebViewerCSSUrl, cspToken)->

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
        <div id="toolbar">
            <button id="zoomOut">-</button>
            <button id="zoomIn">+</button>
            <div id="zoomSelectWrapper">
                <span id="zoomValue">100%</span>
                <div>
                    <select id="zoomSelect">
                        <option value="50">50%</option> 
                        <option value="75">75%</option> 
                        <option value="90">90%</option> 
                        <option value="100" selected>100%</option> 
                        <option value="110">110%</option>
                        <option value="150">150%</option>
                        <option value="200">200%</option>
                    </select>
                </div>
            </div>
            <button id="print">#{trans('Print')}</button>
            <button id="view">#{trans('View in browser')}</button>
        </div>
        <div id="viewport"></div>
        <script nonce="#{cspToken}">
        (function() {
            document.getElementById('viewport').style.width = "#{scale}%";
            #{jsCode}
         })();
        </script>
    </body>
    </html>


    """