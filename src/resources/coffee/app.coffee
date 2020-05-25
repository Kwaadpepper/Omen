# Dependency loader
localesFiles = []
try
	#! Dont use jquery 3.5.0, it is broken with bootstrap 4
	window.$ = window.jQuery = require('jquery')
	window.Popper = require('popper.js').default
	require 'bootstrap'
	require('bootstrap-fileinput/js/fileinput')
	require("bootstrap-fileinput/themes/explorer/theme")

	# get all bootstrap-fileinput locales
	req = require.context("bootstrap-fileinput/js/locales/", true, /^(.*\.(js$))[^.]*$/im)
	req.keys().forEach((key)-> req(key))

	# Save available locales for plugin bootstrap input
	localesFiles[k] = localeFile.substring(2, localeFile.length - 3) for localeFile,k in req.keys()

	fancyTree = require 'jquery.fancytree'
	Base64 = require('js-base64').Base64


	readyEvent = require('./tools/loadingSplash.coffee').registerWaiting()
	document.addEventListener 'readystatechange', (e)->
		# When window loaded ( external resources are loaded too- `css`,`src`, etc...) 
		if e.target.readyState is "complete" then readyEvent.resolve()
catch e
	console.error e

$(document).ready ()->
	omen = require './omenApi.coffee'
	data = require './tools/laravelDataParser.coffee'
	config = require './tools/configGetter.coffee'

	# tooltip toggle
	$('[data-toggle="tooltip"]').tooltip(
		'delay': { show: 1100, hide: 300 },
		'trigger': 'hover'
	).click(->
		$(this).tooltip("hide")
	)


	# add tools to Api
	omen.setProp('config', config)
	omen.setProp('inodes', data.inodes)
	
	# add bootstrap input locales
	omen.setProp('bootstrapInputLocales', localesFiles)
	
	# register Api
	window.OmenFileManager = omen
	
	require './tools/innerTextJqueryPlugin.coffee'
	require './omen/omenView.coffee'
	require './omen/navbar.coffee'
	require './omen/breadcrumb.coffee'
	require './omen/leftPanel.coffee'
	require './omen/actionEvents.coffee'
	require './omen/uploadSystem.coffee'
	require './omen/dragNDropSystem.coffee'
