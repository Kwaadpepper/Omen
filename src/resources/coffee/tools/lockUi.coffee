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
            $('#operationsToolbar button'),
            $('#inodesContainer input[type="checkbox"]')
        ]
    lock: ->
        this.locked = true
        $('#inodesContainer').addClass('notransitions-force')
        $('#operationsSelectAll').addClass('disabled').find('input').prop('disabled', true)
        for uiElement,k in this.uiElements()
            uiElement.prop('disabled', true)
        return
    unlock: ->
        this.locked = false
        $('#inodesContainer').removeClass('notransitions-force')
        $('#operationsSelectAll').removeClass('disabled').find('input').prop('disabled', false)
        for uiElement,k in this.uiElements()
            uiElement.prop('disabled', false)
        return
}