uploadModal = $('#uploadModal')
uploadForm = $('#uploadForm')

uploadForm.on('submit', (e)->
    # do not submit form
    e.preventDefault()
    false
)

module.exports = (action)->
    (event)->

        uploadModal.modal('show')
