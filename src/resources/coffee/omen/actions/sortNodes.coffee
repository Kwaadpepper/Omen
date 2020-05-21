omenApi = require('./../../omenApi.coffee')

inodes = null
previousElementSorter = "sortAlpha"
sortAlphaAscending = (a, b)->
    aI = inodes[a.getAttribute('data-path')]
    bI = inodes[b.getAttribute('data-path')]
    aT = "#{aI.type}#{aI.name}"
    bT = "#{bI.type}#{bI.name}"
    if aT == bT then 0 else if aT < bT then -1 else 1

sortAlphaDescending = (a, b)->
    aI = inodes[a.getAttribute('data-path')]
    bI = inodes[b.getAttribute('data-path')]
    aT = "#{aI.type}#{aI.name}"
    bT = "#{bI.type}#{bI.name}"
    if aT == bT then 0 else if aT < bT then 1 else -1

sortDateAscending = (a, b)->
    aI = inodes[a.getAttribute('data-path')]
    bI = inodes[b.getAttribute('data-path')]
    aT = "#{aI.type}#{aI.lastModified}"
    bT = "#{bI.type}#{bI.lastModified}"
    if aT == bT then 0 else if aT < bT then -1 else 1

sortDateDescending = (a, b)->
    aI = inodes[a.getAttribute('data-path')]
    bI = inodes[b.getAttribute('data-path')]
    aT = "#{aI.type}#{aI.lastModified}"
    bT = "#{bI.type}#{bI.lastModified}"
    if aT == bT then 0 else if aT < bT then 1 else -1

# sort with names since folders have no size
sortSizeAscending = (a, b)->
    aI = inodes[a.getAttribute('data-path')]
    bI = inodes[b.getAttribute('data-path')]
    aT = "#{aI.type}#{aI.size.toString().padStart(16, '0')}#{aI.name}"
    bT = "#{bI.type}#{bI.size.toString().padStart(16, '0')}#{bI.name}"
    if aT == bT then 0 else if aT < bT then -1 else 1

# names are inverted to keep alpha descending order
sortSizeDescending = (a, b)->
    aI = inodes[a.getAttribute('data-path')]
    bI = inodes[b.getAttribute('data-path')]
    aT = "#{aI.type}#{aI.size.toString().padStart(16, '0')}#{bI.name}"
    bT = "#{bI.type}#{bI.size.toString().padStart(16, '0')}#{aI.name}"
    if aT == bT then 0 else if aT < bT then 1 else -1

sortTypeAscending = (a, b)->
    aI = inodes[a.getAttribute('data-path')]
    bI = inodes[b.getAttribute('data-path')]
    aT = "#{aI.type}#{aI.fileType}#{aI.name}"
    bT = "#{bI.type}#{bI.fileType}#{bI.name}"
    if aT == bT then 0 else if aT < bT then -1 else 1

# names are inverted to keep alpha descending order
sortTypeDescending = (a, b)->
    aI = inodes[a.getAttribute('data-path')]
    bI = inodes[b.getAttribute('data-path')]
    aT = "#{aI.type}#{aI.fileType}#{bI.name}"
    bT = "#{bI.type}#{bI.fileType}#{aI.name}"
    if aT == bT then 0 else if aT < bT then 1 else -1

updateFilter = (way, elementSorter)->
    document.getElementById(previousElementSorter).children[0].classList.add('d-none')
    document.getElementById(previousElementSorter).children[1].classList.remove('d-none')
    if way
        document.getElementById(elementSorter).children[1].classList.add('d-none')
        document.getElementById(elementSorter).children[0].classList.remove('d-none')
        document.getElementById('fileSortButton').children[0].className = document.getElementById(elementSorter).children[0].className
    else
        document.getElementById(elementSorter).children[0].classList.add('d-none')
        document.getElementById(elementSorter).children[1].classList.remove('d-none')
        document.getElementById('fileSortButton').children[0].className = document.getElementById(elementSorter).children[1].className
    document.getElementById('fileSortButton').children[1].className = document.getElementById(elementSorter).children[2].className
    previousElementSorter = elementSorter

# True is Descending, False is Ascending
alphaWay = true # this is the default natural sort (provided by server)
dateWay = false
sizeWay = false
typeWay = false

module.exports = (sortType, forceWay)->
    (event)->

        inodes = omenApi.getProp('inodes')
        list = document.getElementById('inodesContainer')
        items = list.children
        
        itemsArr = []
        for item in items
            if item.classList.contains('Root') or item.id is 'viewListTopBar' then continue
            itemsArr.push item

        switch sortType

            when 'alpha'
                if alphaWay or !!forceWay
                    itemsArr.sort sortAlphaAscending
                else
                    itemsArr.sort sortAlphaDescending
                    
                updateFilter(alphaWay or !!forceWay, 'sortAlpha')
                if forceWay is not undefined then alphaWay = forceWay
                else alphaWay = !alphaWay
                dateWay = false
                sizeWay = false
                typeWay = false
                
            when 'date'
                if dateWay or !!forceWay
                    itemsArr.sort sortDateAscending
                else
                    itemsArr.sort sortDateDescending

                updateFilter(dateWay or !!forceWay, 'sortDate')
                if forceWay is not undefined then dateWay = forceWay
                else dateWay = !dateWay
                alphaWay = false
                sizeWay = false
                typeWay = false

            when 'size'
                if sizeWay != !!forceWay
                    itemsArr.sort sortSizeAscending
                else
                    itemsArr.sort sortSizeDescending

                updateFilter(sizeWay or !!forceWay, 'sortSize')
                if forceWay is not undefined then sizeWay = forceWay
                else sizeWay = !sizeWay
                alphaWay = false
                dateWay = false
                typeWay = false

            when 'type'
                if typeWay != !!forceWay
                    itemsArr.sort sortTypeAscending
                else
                    itemsArr.sort sortTypeDescending

                updateFilter(typeWay or !!forceWay, 'sortType')
                if forceWay is not undefined then typeWay = forceWay
                else typeWay = !typeWay
                alphaWay = false
                dateWay = false
                sizeWay = false


        for item in itemsArr
            list.appendChild item
        
        # $('#fileSortButton').dropdown('hide')
        if event
            event.preventDefault()
            false