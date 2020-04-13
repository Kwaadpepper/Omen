module.exports = (alert, title, message)->
    alertComponent = $(".alert#{alert}Toast")
    alertComponent.find('.alertTitle').text(title)
    alertComponent.find('.alertBody').text(message)
    alertComponent.toast('show')

    switch alert
        when 'success'
            # do nothing
            undefined
        when 'warning'
            console.warn "OMEN: #{title},  #{message}"
        when 'info'
            console.info "OMEN: #{title},  #{message}"
        when 'danger'
            console.error "OMEN: #{title},  #{message}"
        else
            console.exception "OMEN: Unknown alert type #{alert} can't display popup,  =>  #{title},  #{message}"