{
"name": "{{ config('omen.package.name') }}",
"short_name": "Omen FileManager",
"description": "{{ config('omen.package.description') }}",
"display": "standalone",
"lang": "{{ config('omen.locale') }}",
"theme_color": "#2b5797",
"icons": [
{
"src": "/{{ config('omen.assetPath') }}/images/favicon/android-chrome-36x36.png",
"sizes": "36x36",
"type": "image/png"
},
{
"src": "/{{ config('omen.assetPath') }}/images/favicon/android-chrome-48x48.png",
"sizes": "48x48",
"type": "image/png"
},
{
"src": "/{{ config('omen.assetPath') }}/images/favicon/android-chrome-72x72.png",
"sizes": "72x72",
"type": "image/png"
},
{
"src": "/{{ config('omen.assetPath') }}/images/favicon/android-chrome-96x96.png",
"sizes": "96x96",
"type": "image/png"
},
{
"src": "/{{ config('omen.assetPath') }}/images/favicon/android-chrome-144x144.png",
"sizes": "144x144",
"type": "image/png"
},
{
"src": "/{{ config('omen.assetPath') }}/images/favicon/android-chrome-192x192.png",
"sizes": "192x192",
"type": "image/png"
},
{
"src": "/{{ config('omen.assetPath') }}/images/favicon/android-chrome-256x256.png",
"sizes": "256x256",
"type": "image/png"
}
],
"theme_color": "#ffffff",
"background_color": "#ffffff",
"start_url": "{{ url(config('omen.urlPrefix')) }}"
}
