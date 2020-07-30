omenApi = require('./../omenApi.coffee')
config = omenApi.config

resizeTimeout = null

##
# Shorten the breadcrumb at screen size
#* If it fails because a folder name is too long
#* a css overflow-x makes the breadcrumb readable
# 
breadcrumbEllipsis = ->
	$subFolders = $ '#pathBreadcrumbList > li'
	$breadCrumbList = $ '#pathBreadcrumbList'
	dotSpawned = false
	ellipsisWord = 2
	breadCrumbWidth = $('#pathBreadcrumb').width()
	ellipsisWord = 1 if breadCrumbWidth < 680 # cut at folder #1 on 680px
	ellipsisWord = 0 if breadCrumbWidth < 480 # cut at folder #0 on 480px
	if $subFolders.length > 4
		dotSpawned = false
		# reset tags
		$subFolders.each (k,v)->
			$(v).removeClass 'breadcrumbEllipsed'
			$(v).css 'display', ''
		$subFolders.each (k,v)->
			# Is breadcrumb not overflowing ?
			if not ($breadCrumbList[0].offsetWidth < $breadCrumbList[0].scrollWidth)
				return false
			if k > ellipsisWord and k < $subFolders.length - 1
				if not dotSpawned
					$(v).addClass 'breadcrumbEllipsed'
					dotSpawned = true
					return
				$(v).css 'display', 'none'
				return

if(config('omen.breadcrumbEllipsis', true))
	breadcrumbEllipsis()
	$(window).on('resize',->
		clearTimeout(resizeTimeout)
		resizeTimeout = setTimeout(breadcrumbEllipsis, 500)
	)

window.updateCounters = ->
	inodes = omenApi.getProp('inodes')
	dirs = 0
	files = 0
	for key,inode of inodes
		if inode.type == 'directory' then dirs += 1
		if inode.type == 'file' then files += 1
	$('#folderCounter').text(dirs)
	$('#fileCounter').text(files)

updateCounters()
module.exports = ->
	updateCounters()
	if(config('omen.breadcrumbEllipsis', true)) then breadcrumbEllipsis()
	