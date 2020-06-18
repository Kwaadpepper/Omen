module.exports = (force = false, selectAllStatus = false)->
    $operationsSelectAllInput = $('#operationsSelectAll').find('input')
    $viewListTopBarInput = $('#viewListTopBar input')
    allChecked = true
    for v,k in $('#inodesContainer').find('figure input[type="checkbox"]').toArray()
        if force
            if selectAllStatus
                if $(v).parents('figure').is(':visible') then $(v).prop('checked', true)
            else
                $(v).prop('checked', false)
        if not $(v).is(':checked') then allChecked = false

    if allChecked
        $operationsSelectAllInput.prop('checked', true)
        $viewListTopBarInput.prop('checked', true)
    else
        $viewListTopBarInput.prop('checked', false)
        $operationsSelectAllInput.prop('checked', false)