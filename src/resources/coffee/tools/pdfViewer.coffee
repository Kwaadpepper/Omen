module.exports = (url, cspToken)->
    o = ->
    
        renderPDF = ((renderCallback)->

            renderCallback = if not renderCallback then (->) else renderCallback
            # === PDF JS Code ===
    
            currentPageIndex = 0
            pageMode = 1
            cursorIndex = Math.floor(currentPageIndex / pageMode)
            pdfInstance = null
            totalPagesCount = 0
            pdfDocument = null
    
            try
                pdfDocument = pdfjsLib.getDocument({ url: '###url###' })

                # reset Doc
                # pdfDocument.setDocument(null)
                viewport = document.getElementById("viewport")
                viewport.innerHTML = ""

                pdfDocument.promise.then(
                    ((pdfInstance)->
        
                        totalPagesCount = pdfInstance.numPages
                        viewport = document.getElementById("viewport")

                        viewport.innerHTML = ""
        
                        pagesContainerCollection = []
                        pagesRenderPromises = []
        
                        #Inject a canvas for each page
                        (for pageNumber in [1..totalPagesCount]
    
                            # Inject canvas
                            viewport.innerHTML += """
                                <div id='#{pageNumber}'>
                                    <canvas></canvas>
                                    <div class="annotationLayer"></div>
                                    <div class="textLayer"></div>
                                </div>
                            """;
    
                            # store references  to DOM
                            pagesContainerCollection[pageNumber - 1] = document.getElementById("#{pageNumber}")
    
                            # create render Promises
                            pagesRenderPromises[pageNumber - 1] = pdfInstance.getPage(pageNumber)
                        )
        
                        # render all pages
                        Promise.all(pagesRenderPromises).then((pages)->
        
                            pages.forEach((page)->
    
                                # get Page viewport in pixel at scale 1
                                pdfViewport = page.getViewport({ scale: 1})
                                
                                # get page container
                                pageContainer = viewport.children[page.pageNumber - 1]
    
                                # get page canvas
                                canvas = pageContainer.children[0]
    
                                # Calculate pdf container viewport at scale (pageContainer.offsetWidth / pdfViewport.width)
                                pdfPageContainerViewport = page.getViewport({scale: (pageContainer.offsetWidth / pdfViewport.width)})
        
                                # force page container width
                                pageContainer.style.width = pdfPageContainerViewport.width + 'px'
                                pageContainer.style.height = pdfPageContainerViewport.height + 'px'
    
                                # set canvas size to the page view port
                                canvas.height = pdfPageContainerViewport.height
                                canvas.width = pdfPageContainerViewport.width
        
                                try
                                    renderPromise = page.render({
                                        canvasContext: canvas.getContext('2d'),
                                        viewport: pdfPageContainerViewport
                                    })
    
                                    # Annotations layer render
                                    renderPromise.promise.then(->
                                        # Returns a promise, on resolving it will return annotation data of page
                                        return page.getAnnotations()
                                    ).then((annotationData)->
    
                                        # used resources
                                        # https://usefulangle.com/post/94/javascript-pdfjs-enable-annotation-layer
                                        # https://github.com/mozilla/pdf.js/blob/master/examples/components/simpleviewer.js
                                        # https://github.com/mozilla/pdf.js/issues/7779
    
                                        annotationLayer = pageContainer.children[1]
        
                                        # Canvas offset
                                        rect = canvas.getBoundingClientRect()
                                        canvas_offset = {
                                            top: rect.top + document.body.scrollTop,
                                            left: rect.left + document.body.scrollLeft
                                        }
                                    
                                        # CSS for annotation layer
                                        annotationLayer.style.height = canvas.height + 'px'
                                        annotationLayer.style.width = canvas.width + 'px'
    
                                        # Render the annotation layer
                                        pdfjsLib.AnnotationLayer.render({
                                            viewport: pdfPageContainerViewport.clone({ dontFlip: true }),
                                            div: annotationLayer,
                                            annotations: annotationData,
                                            page: page,
                                            eventBus: new pdfjsViewer.EventBus(),
                                            linkService:  new pdfjsViewer.PDFLinkService({
                                                eventBus: new pdfjsViewer.EventBus(),
                                                externalLinkTarget: 2
                                            })
                                        })
                                    )
    
                                    # text layer render
                                    renderPromise.promise.then(->
                                        # Returns a promise, on resolving it will return text contents of the page
                                        return page.getTextContent()
                                    ).then((textContent)->
    
                                        # used resources
                                        # https://usefulangle.com/post/90/javascript-pdfjs-enable-text-layer
    
                                        textLayer = pageContainer.children[2]
    
                                        # Canvas offset
                                        rect = canvas.getBoundingClientRect()
                                        canvas_offset = {
                                            top: rect.top + document.body.scrollTop,
                                            left: rect.left + document.body.scrollLeft
                                        }
    
                                        # CSS for text layer
                                        textLayer.style.height = canvas.height + 'px'
                                        textLayer.style.width = canvas.width + 'px'
    
                                        # Pass the data to the method for rendering of text over the pdf canvas.
                                        pdfjsLib.renderTextLayer({
                                            textContent: textContent,
                                            container: textLayer,
                                            eventBus: new pdfjsViewer.EventBus(),
                                            viewport: pdfPageContainerViewport,
                                            textDivs: []
                                        });
                                    )
    
                                catch Error
                                    console.log('An error occured in PDF rendering page => ' + Error, '120')
                            )
                        ).then(renderCallback)
                    ),
                    ((reason)->
                        console.log('An error occured in PDF getting document => ' + reason, '121')
                    )
                )
    
            catch error
                console.log('An unknown error occured in PDF rendering code => ' + error, '122')
    
    
            # === END PDF JS CODE ===
        )

        # render on load
        renderPDF()

        viewPort = document.getElementById('viewport')
        zoomValue = document.getElementById('zoomValue')
        zoomSelect = document.getElementById('zoomSelect')
        zoomIn = document.getElementById('zoomIn')
        zoomOut = document.getElementById('zoomOut')
        print = document.getElementById('print')
        view = document.getElementById('view')
        fileName = '###url###'.split('/')
        fileName = fileName[fileName.length - 1]


        zoomIn.addEventListener('click', ->
            pdfjsLib.getDocument({ url: '###url###' }).destroy().then(->
                width = Number.parseInt(viewPort.style.width) + 10
                viewPort.style.width = width + '%'
                zoomValue.innerHTML = width + '%'
                renderPDF(->
                    console.log 'zoomed IN'
                )
            )
        )

        zoomOut.addEventListener('click', ->
            pdfjsLib.getDocument({ url: '###url###' }).destroy().then(->
                width = Number.parseInt(viewPort.style.width) - 10
                viewPort.style.width = width + '%'
                zoomValue.innerHTML = width + '%'
                renderPDF(->
                    console.log 'zoomed Out'
                )
            )
        )

        zoomSelect.addEventListener("change", ->
            pdfjsLib.getDocument({ url: '###url###' }).destroy().then(->
                width = zoomSelect.selectedOptions[0].value
                viewPort.style.width = width + '%'
                zoomValue.innerHTML = width + '%'
                renderPDF(->
                    console.log 'zoomed Out'
                )
            )
        )

        resizeTimer = null

        window.addEventListener('resize', ->
            clearTimeout(resizeTimer)
            resizeTimer = setTimeout (->
                pdfjsLib.getDocument({ url: '###url###' }).destroy().then(->
                    width = Number.parseInt(viewPort.style.width)
                    viewPort.style.width = width + '%'
                    zoomValue.innerHTML = width + '%'
                    renderPDF(->
                        console.log 'resized'
                    )
                )
                return
            ), 400
        )

        view.addEventListener('click', ->
            wnd = window.open('###url###' + '?view')
        )
        print.addEventListener('click', ->
            wnd = window.open('')
            wnd.document.write("""<!DOCTYPE html><html><head><title>#{fileName}</title><style nonce="###cspToken###">@page {
                size: 21cm 29.7cm;
                margin: 20mm 35mm 20mm 35mm;
            }</style></head><body>""")
            document.querySelectorAll('canvas').forEach((el)->
                img = new Image()
                img.src = el.toDataURL()
                wnd.document.write(img.outerHTML)
            )
            wnd.document.write('</body></html>')
            setTimeout((->
                wnd.print()
                wnd.close()
                return
            ), 1000)
        )

    # Return Code to string injection
    '(' + o.toString().replaceAll("###url###", url).replace('###cspToken###', cspToken) + ')();'
