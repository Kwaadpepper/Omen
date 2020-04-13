### Hide browser Url tooltips

Removes href to prevent url tooltip
adds url to data-href

###

# console.log 'toto'

# $('body').on 'mouseover', 'a', (e)->
#     console.log e
#     $link = $(this)
#     href = $link.attr('href') || $link.data("href")

#     $link.off 'click.chrome'
#     $link.on 'click.chrome', ()->
#         window.location.href = href
#     .attr 'data-href', href  #keeps track of the href value
#     .removeAttr 'href'  # <- this is what stops Chrome to display status bar
