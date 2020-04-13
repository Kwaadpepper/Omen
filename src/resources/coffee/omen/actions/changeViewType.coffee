module.exports = (action)->
    (event)->
        if action is 'icon'
            document.getElementById('viewIcon').classList.add('active')
            document.getElementById('viewList').classList.remove('active')
            document.getElementById('inodesContainer').classList.add('viewIcon')
            document.getElementById('inodesContainer').classList.remove('viewList')
        if action is 'list'
            document.getElementById('viewIcon').classList.remove('active')
            document.getElementById('viewList').classList.add('active')
            document.getElementById('inodesContainer').classList.remove('viewIcon')
            document.getElementById('inodesContainer').classList.add('viewList')
        false