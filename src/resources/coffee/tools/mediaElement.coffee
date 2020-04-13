omen = require('../omenApi.coffee')

# media element setup
mejs.Renderers.order = [
    'html5',
    'native_hls',
    'native_dash',
    'native_flv',
    'flash_video',
    'flash_audio',
    'flash_audio_ogg',
    'flash_hls', 
    'flash_dash'
]

# setup language
mejs.i18n.language(omen.config('omen.locale'))

module.exports = {
    element: null,
    player: null,
    inject:(element, url)->


        # init player
        this.player = new MediaElementPlayer(element, {
            renderers: [
                'html5',
                'native_hls',
                'native_dash',
                'native_flv',
                'flash_video',
                'flash_audio',
                'flash_audio_ogg',
                'flash_hls', 
                'flash_dash'
            ],
            pluginPath: "#{omen.config('omen.urlPrefix')}/js/vendor/mediaelement/",
            hls: {
                debug: true
            },
            success: (media, node, instance)->
                # Use the conditional to detect if you are using `native_hls` renderer for that given media;
                # otherwise, you don't need it
                # if Hls != undefined
                #     media.addEventListener(Hls.Events.MEDIA_ATTACHED, ->
                #         # All the code when this event is reached...
                #         console.log('Media attached!');
                #     )

                #     # Manifest file was parsed, invoke loading method
                #     media.addEventListener(Hls.Events.MANIFEST_PARSED, ->
                #         # All the code when this event is reached...
                #         console.log('Manifest parsed!');
                #     )

                #     media.addEventListener(Hls.Events.FRAG_PARSING_METADATA, (event, data)->
                #         # All the code when this event is reached...
                #         console.log(data);
                #     )
        })

        this.player.setSrc(url)
        this.player.load()

    destroy: ->

        if not this.player.paused
            this.player.pause()
        
        this.player.remove()
}
