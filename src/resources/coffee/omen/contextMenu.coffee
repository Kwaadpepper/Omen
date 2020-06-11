require('jquery-contextmenu')
trans = require('./../tools/translate.coffee')
setLocationParameters = require('./../tools/setLocationParameters.coffee')
omenApi = require('./../omenApi.coffee')
clipboard =require('./../tools/clipboard.coffee')

actionEvents = require('./actionEvents.coffee')
renameAction = actionEvents.rename
rename = require('./actions/rename.coffee')
deleteAction = actionEvents.delete
deleteInode = require('./actions/delete.coffee')
viewAction = actionEvents.view
view = require('./actions/view.coffee')
downloadAction = actionEvents.download
download = require('./actions/download.coffee')
copy = require('./actions/copy.coffee')
cut = require('./actions/cut.coffee')
paste = require('./actions/paste.coffee')

#! Dom Elements
aboutModal = $('#aboutModal')

languages = []
$('#leftPanelLocalesList a').each((k,v)->
    lang = $(v).text();
    languages.push({
        name: lang.substr(0, lang.length - $(v).children('span').text().length),
        callback: ->
            window.location.replace(setLocationParameters({
                'locale': $(v).data('locale')
            }))
    })
)

contextMenus = [
    # Global context menu
    {
        selector: "body",
        className: 'globalContextMenu'
        items: {
            paste: {
                name: trans('Paste'),
                className: 'paste mdi mdi-context-item mdi-content-paste',
                callback: paste
            },
            reload: {
                name: trans('Reload'),
                className: 'mdi mdi-context-item mdi-reload'
                callback: -> document.location.reload()
            }
            languages: {
                name: trans('Languages'),
                className: 'mdi mdi-context-item mdi-translate'
                items: languages
            }
            about: {
                name: trans('About'),
                className: 'mdi mdi-context-item mdi-information-outline',
                callback: -> aboutModal.modal('show')
            }
        },
        events: {
            show: ((options)->
                contextMenu = $('.globalContextMenu')
                if clipboard.items.length then contextMenu.find('.paste').removeClass('d-none')
                else contextMenu.find('.paste').addClass('d-none')
            )
        }
    },
    # Inode Directory context menu
    {
        selector: 'figure.figureDirectory',
        className: 'directoryContextMenu',
        items: {
            copy: { name: trans('Copy'), className: 'mdi mdi-context-item mdi-content-copy', callback: (key, opt)-> $(this).find('span.checkmark').trigger('click'); copy() },
            cut: { name: trans('Cut'), className: 'mdi mdi-context-item mdi-content-cut', callback: (key, opt)-> $(this).find('span.checkmark').trigger('click'); cut() },
            paste: { name: trans('Paste'), className: 'mdi mdi-context-item mdi-content-paste', callback: paste },
            rename: {name: trans('Rename'), className: 'mdi mdi-context-item mdi-pencil', callback: (key, opt)-> rename(renameAction).apply($(this).children().first()) },
            delete: {name: trans('Delete'), className: 'mdi mdi-context-item mdi-delete', callback: (key, opt)-> deleteInode(deleteAction).apply($(this).children().first()) },
            separator: '-----',
            visibility: { name: '', className: 'visibility text-body mdi mdi-context-item mdi-file-eye-outline', disabled: true  },
            date: { name: '', className: 'date text-body mdi mdi-context-item mdi-calendar', disabled: true },
        },
        events: {
            show: ((options)->
                inodeFigure = $(this)
                contextMenu = $('.directoryContextMenu')
                inode = omenApi.getProp('inodes')[$(this).data('path')]

                if clipboard.items.length then contextMenu.find('.paste').removeClass('d-none')
                else contextMenu.find('.paste').addClass('d-none')

                # info
                contextMenu.find('.visibility').text(inodeFigure.find('.figVisibility').data('visibility'))
                contextMenu.find('.date').text(inodeFigure.find('.figDate').text())
            )
        }
    },
    # Inode File context menu
    {
        selector: 'figure.figureFile',
        className: 'fileContextMenu',
        items: {
            copy: { name: trans('Copy'), className: 'copy mdi mdi-context-item mdi-content-copy', callback: (key, opt)-> $(this).find('span.checkmark').trigger('click'); copy() },
            cut: { name: trans('Cut'), className: 'cut mdi mdi-context-item mdi-content-cut', callback: (key, opt)-> $(this).find('span.checkmark').trigger('click'); cut() },
            paste: { name: trans('Paste'), className: 'paste mdi mdi-context-item mdi-content-paste', callback: paste },
            download: { name: trans('Download'), className: 'download mdi mdi-context-item mdi-arrow-down-bold', callback: (key, opt)-> download(downloadAction).apply($(this).children().first()) },
            view: { name: trans('View'), className: 'view mdi mdi-context-item mdi-eye', callback: (key, opt)-> view(viewAction).apply($(this).children().first()) },
            rename: { name: trans('Rename'), className: 'rename mdi mdi-context-item mdi-pencil', callback: (key, opt)-> rename(renameAction).apply($(this).children().first()) },
            delete: { name: trans('Delete'), className: 'delete mdi mdi-context-item mdi-delete', callback: (key, opt)-> deleteInode(deleteAction).apply($(this).children().first()) },
            separator: '-----',
            title: { name: trans('File info'), className:'font-weight-bold text-body', disabled: true },
            weight: { name: '', className: 'weight text-body mdi mdi-context-item mdi-weight', disabled: true  },
            visibility: { name: '', className: 'visibility text-body mdi mdi-context-item mdi-file-eye-outline', disabled: true  },
            extension: { name: '', className: 'extension text-body mdi mdi-context-item mdi-tag-text-outline', disabled: true }
            date: { name: '', className: 'date text-body mdi mdi-context-item mdi-calendar', disabled: true }
        }
        events: {
            show: ((options)->
                inodeFigure = $(this)
                contextMenu = $('.fileContextMenu')
                inode = omenApi.getProp('inodes')[$(this).data('path')]
                contextMenu.attr('data-menutitle', inode.name).css('background-color', inodeFigure.css('background-color'))

                if inodeFigure.find('.actionView').length then contextMenu.find('.view').removeClass('d-none')
                else contextMenu.find('.view').addClass('d-none')

                if clipboard.items.length then contextMenu.find('.paste').removeClass('d-none')
                else contextMenu.find('.paste').addClass('d-none')

                # info
                contextMenu.find('.weight').text(inodeFigure.find('.figSize').text())
                contextMenu.find('.visibility').text(inodeFigure.find('.figVisibility').data('visibility'))
                contextMenu.find('.date').text(inodeFigure.find('.figDate').text())
                contextMenu.find('.extension').text(inodeFigure.find('.figExt').text())
            )
        }
    }
]

for contextMenu in contextMenus
    $.contextMenu(contextMenu)