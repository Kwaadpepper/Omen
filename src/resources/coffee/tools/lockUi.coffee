module.exports = {
    locked: false,
    uiElements: ->
        [
            $('#actionButtonGroup button')
            $('#viewButtonGroup button')
            $('#filterButtonGroup button')
            $('#filterInputText'),
            $('#leftPanelMenuBar button'),
            $('#fileSortButton'),
            $('#inodesContainer input[type="checkbox"]')
        ]
    lock: ->
        this.locked = true
        $('#inodesContainer').addClass('notransitions-force')
        for uiElement,k in this.uiElements()
            uiElement.prop('disabled', true)
        return
    unlock: ->
        this.locked = false
        $('#inodesContainer').removeClass('notransitions-force')
        for uiElement,k in this.uiElements()
            uiElement.prop('disabled', false)
        return
}