timeoutEvent = require './../tools/timeoutEvent.coffee'
config = require('./../omenApi.coffee').config

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
	$(window).on 'resize',()-> timeoutEvent(breadcrumbEllipsis, 500)

module.exports = ->
	if(config('omen.breadcrumbEllipsis', true)) then breadcrumbEllipsis()