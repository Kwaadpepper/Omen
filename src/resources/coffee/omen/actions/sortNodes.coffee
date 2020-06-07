omenApi = require('./../../omenApi.coffee')

alphaWay = null
dateWay = null
sizeWay = null
typeWay = null
inodes = null
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
    fileSortButtonWay = document.getElementById('fileSortButton').children[0]
    fileSortButtonType = document.getElementById('fileSortButton').children[1]
    sortType = document.getElementById(elementSorter)
    if way
        fileSortButtonWay.className = sortType.children[0].className
        sortType.children[0].classList.add('d-none')
        sortType.children[1].classList.remove('d-none')
    else
        fileSortButtonWay.className = sortType.children[1].className
        sortType.children[1].classList.add('d-none')
        sortType.children[0].classList.remove('d-none')
    fileSortButtonWay.classList.remove('d-none')
    fileSortButtonType.className = sortType.children[2].className

# init sort type way Display
for k,sortType of ['Alpha', 'Date', 'Size', 'Type']
    storSortType = localStorage.getItem("sortFilesWay#{sortType.toLowerCase()}")
    if storSortType is not null then updateFilter(storSortType, "sort#{sortType}")

sortFunctions = {
    alpha: [sortAlphaAscending, sortAlphaDescending],
    date: [sortDateAscending, sortDateDescending],
    size: [sortSizeAscending, sortSizeDescending],
    type: [sortTypeAscending, sortTypeDescending],
}

module.exports = (sortType, clickEvent = false)->
    (event)->

        # True is Descending, False is Ascending
        alphaWay = localStorage.getItem('sortFilesWayalpha') == "true" 
        dateWay = localStorage.getItem('sortFilesWaydate') == "true" 
        sizeWay = localStorage.getItem('sortFilesWaysize') == "true" 
        typeWay = localStorage.getItem('sortFilesWaytype') == "true" 

        # this is the default natural sort (provided by server)
        if localStorage.getItem('sortFilesWayalpha') is null then alphaWay = true
        if localStorage.getItem('sortFilesWaydate') is null then dateWay = true
        if localStorage.getItem('sortFilesWaysize') is null then sizeWay = true
        if localStorage.getItem('sortFilesWaytype') is null then typeWay = true

        inodes = omenApi.getProp('inodes')
        list = document.getElementById('inodesContainer')
        items = list.children
        
        itemsArr = []
        for item in items
            if item.classList.contains('Root') or item.id is 'viewListTopBar' then continue
            itemsArr.push item

        switch sortType
            when 'alpha' then way = alphaWay
            when 'date' then way = dateWay
            when 'size' then way = sizeWay
            when 'type' then way = typeWay
        
        if clickEvent then way = !way # inverted sort way on click event
        # apply sort functions
        if way then itemsArr.sort sortFunctions[sortType][0] # asccending
        else itemsArr.sort sortFunctions[sortType][1] # descending
        # update UI
        updateFilter(way, "sort#{sortType[0].toUpperCase()}#{sortType.substring(1)}")
        localStorage.setItem "sortFiles", sortType # save applied sort type
        localStorage.setItem "sortFilesWay#{sortType}", way # save applied sort way

        for item in itemsArr
            list.appendChild item
        
        $('#fileSortButton').dropdown('hide')
        if event
            event.preventDefault()
            false