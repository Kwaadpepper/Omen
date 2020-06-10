logException = require('./logException.coffee')
ln = require('./getLine.coffee')

module.exports = (url)->
    o = ->
    
        # === PDF JS Code ===
        
        currentPageIndex = 0
        pageMode = 1
        cursorIndex = Math.floor(currentPageIndex / pageMode)
        pdfInstance = null
        totalPagesCount = 0
        pdfDocument = null

        try
            pdfDocument = pdfjsLib.getDocument({ url: '###url###' })        
            pdfDocument.promise.then(
                ((pdfInstance)->
    
                    totalPagesCount = pdfInstance.numPages
                    viewport = document.getElementById("viewport")
    
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
                                    annotationLayer.style.left = canvas_offset.left + 'px'
                                    annotationLayer.style.top = canvas_offset.top + 'px'
                                    annotationLayer.style.height = canvas.height + 'px'
                                    annotationLayer.style.width = canvas.width + 'px'

                                    # Render the annotation layer
                                    pdfjsLib.AnnotationLayer.render({
                                        viewport: pdfPageContainerViewport.clone({ dontFlip: true }),
                                        div: annotationLayer,
                                        annotations: annotationData,
                                        page: page,
                                        linkService:  new pdfjsViewer.PDFLinkService({
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
                                    textLayer.style.left = canvas_offset.left + 'px'
                                    textLayer.style.top = canvas_offset.top + 'px'
                                    textLayer.style.height = canvas.height + 'px'
                                    textLayer.style.width = canvas.width + 'px'

                                    # Pass the data to the method for rendering of text over the pdf canvas.
                                    pdfjsLib.renderTextLayer({
                                        textContent: textContent,
                                        container: textLayer,
                                        viewport: pdfPageContainerViewport,
                                        textDivs: []
                                    });
                                )

                            catch Error
                                logException('An error occured in PDF rendering page => ' + Error, '99'+ln())
                        )
                    )
                ),
                ((reason)->
                    logException('An error occured in PDF getting document => ' + reason, '99'+ln())
                )
            )

        catch error
            logException('An unknown error occured in PDF rendering code => ' + error, '99'+ln())


        # === END PDF JS CODE ===

    # Return Code to string injection
    '(' + o.toString().replace("###url###", url) + ')();'



#     (function() {
#   let currentPageIndex = 0;
#   let pageMode = 1;
#   let cursorIndex = Math.floor(currentPageIndex / pageMode);
#   let pdfInstance = null;
#   let totalPagesCount = 0;

#   const viewport = document.querySelector("#viewport");
#   window.initPDFViewer = function(pdfURL) {
#     pdfjsLib.getDocument(pdfURL).then(pdf => {
#       pdfInstance = pdf;
#       totalPagesCount = pdf.numPages;
#       initPager();
#       initPageMode();
#       render();
#     });
#   };

#   function onPagerButtonsClick(event) {
#     const action = event.target.getAttribute("data-pager");
#     if (action === "prev") {
#       if (currentPageIndex === 0) {
#         return;
#       }
#       currentPageIndex -= pageMode;
#       if (currentPageIndex < 0) {
#         currentPageIndex = 0;
#       }
#       render();
#     }
#     if (action === "next") {
#       if (currentPageIndex === totalPagesCount - 1) {
#         return;
#       }
#       currentPageIndex += pageMode;
#       if (currentPageIndex > totalPagesCount - 1) {
#         currentPageIndex = totalPagesCount - 1;
#       }
#       render();
#     }
#   }
#   function initPager() {
#     const pager = document.querySelector("#pager");
#     pager.addEventListener("click", onPagerButtonsClick);
#     return () => {
#       pager.removeEventListener("click", onPagerButtonsClick);
#     };
#   }

#   function onPageModeChange(event) {
#     pageMode = Number(event.target.value);
#     render();
#   }
#   function initPageMode() {
#     const input = document.querySelector("#page-mode input");
#     input.setAttribute("max", totalPagesCount);
#     input.addEventListener("change", onPageModeChange);
#     return () => {
#       input.removeEventListener("change", onPageModeChange);
#     };
#   }

#   function render() {
#     cursorIndex = Math.floor(currentPageIndex / pageMode);
#     const startPageIndex = cursorIndex * pageMode;
#     const endPageIndex =
#       startPageIndex + pageMode < totalPagesCount
#         ? startPageIndex + pageMode - 1
#         : totalPagesCount - 1;

#     const renderPagesPromises = [];
#     for (let i = startPageIndex; i <= endPageIndex; i++) {
#       renderPagesPromises.push(pdfInstance.getPage(i + 1));
#     }

#     Promise.all(renderPagesPromises).then(pages => {
#       const pagesHTML = `<div style="width: ${
#         pageMode > 1 ? "50%" : "100%"
#       }"><canvas></canvas></div>`.repeat(pages.length);
#       viewport.innerHTML = pagesHTML;
#       pages.forEach(renderPage);
#     });
#   }

#   function renderPage(page) {
#     let pdfViewport = page.getViewport(1);

#     const container =
#       viewport.children[page.pageIndex - cursorIndex * pageMode];
#     pdfViewport = page.getViewport(container.offsetWidth / pdfViewport.width);
#     const canvas = container.children[0];
#     const context = canvas.getContext("2d");
#     canvas.height = pdfViewport.height;
#     canvas.width = pdfViewport.width;

#     page.render({
#       canvasContext: context,
#       viewport: pdfViewport
#     });
#   }
# })();
