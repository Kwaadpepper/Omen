pdfJSCode = require('./pdfViewer.coffee')
pdfTemplate = require('./pdfViewerTemplate.coffee')
iframePDF = $ '#pdfViewerModalIFrame'
pdfJSUrl = iframePDF.data 'script-pdf'
pdfJSWorkerUrl = iframePDF.data 'script-worker'
pdfCSSUrl = iframePDF.data 'script-css'
cspToken = iframePDF.data 'csp'
pdfWebViewerJSUrl = iframePDF.data 'script-js-web'
pdfWebViewerCSSUrl = iframePDF.data 'script-css-web'

# reset iframe
iframePDF.attr('src', 'about:blank')

module.exports = (pdfUrl)->
  
    doc = document.getElementById('pdfViewerModalIFrame').contentWindow.document
    doc.open()
    doc.write(pdfTemplate(pdfJSCode(pdfUrl), pdfJSUrl, pdfJSWorkerUrl, pdfCSSUrl, pdfWebViewerJSUrl, pdfWebViewerCSSUrl, cspToken))
    doc.close()
