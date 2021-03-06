<img align="left" width="80" height="80" src="https://user-images.githubusercontent.com/6019313/82305874-8ad0d800-99be-11ea-8655-6d3ab5deb43f.png" alt="Omen project icon">

# &nbsp;&nbsp;&nbsp; Omen file manager

---

[Project website](https://omen.jeremydev.ovh)<br>
[Project wiki](https://github.com/Kwaadpepper/Omen/wiki)<br>
[Contact at jeremydev.ovh](https://jeremydev.ovh/contact)

### **This is a Work in progress**

![Capture d’écran - 2020-06-09 à 08 51 18](https://user-images.githubusercontent.com/6019313/85106214-1e8b0380-b20c-11ea-927f-65e542a87b2b.png)

### A To compile

just run `yarn install` and then `yarn dev` or `yarn prod`

### B To install test within laravel

for now :

1.  add this to a laravel project with composer,
    you can use this as a local project, in your composer.json :

            "repositories": {
                "kwaadpepper/laravel-omen": {
                    "type": "path",
                    "url": "pathTo/laravel-omen-git-pull-from-github.git",
                    "options": {
                        "symlink": true
                    }
                }
            },

2.  then `composer require kwaadpepper/laravel-omen`

3.  navigate to vendor/kwaadpepper/laravel-omen and `run yarn prod`,
    then access the url `/omenfilemanager` within your laravel project

You can override `--tag=config, --tag=assets, --tag=views, --tag=lang` :

    php artisan vendor:publish --provider="Kwaadpepper\Omen\Providers\OmenServiceProvider" --tag=assets

If you are using local storage and dont need to set files to private you can use this

    sudo php artisan omen:link

**Notes**

-   About contributions

    Please report any vulnerabilities found or make a PR at your convenience. Everyone is welcome.
    If you wish, you can appear on a list of contributors visible on the about page. Your GitHub information visible on the commit will be used (name and email, or just the name) If you explicitly declared it in your PR. You must agree to publish with the MIT license because the project license is defined as is.
    Your contribution will be reviewed first before the merger. Please refer to the PR section.
    If you want to report something, just open an issue ticket.

-   About CSRF

    https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html

    Use is made of the Laravel built-in CSRF system token. In addition, an Ajax CSRF token is used for every request (even if only write operation should require it).

-   About CSP policies

    https://cheatsheetseries.owasp.org/cheatsheets/Content_Security_Policy_Cheat_Sheet.html

    This lib uses a CSP strategy on all its assets requirements (CSS, JS) using nonce.
    This is configurable through the config file ('omen.csp')

-   About X-Frame-Options

    X-Frame-Options is sent to the client to prevent click jacking (iframe embed)
    https://www.keycdn.com/blog/x-frame-options
    false => X-Frame-Options: deny
    true => X-Frame-Options: sameorigin

-   About X-Content-Type-Options

    Used againts mime sniffing to prevent cross site scripting.
    Thus, any file served will be treated with the type declared by the server
    https://www.keycdn.com/support/x-content-type-options
    This is always set to no sniff, and is not configurable

-   About Referrer-Policy

    https://openweb.eu.org/articles/referrer-policy

    No tracking is needed by Omen, and it dont want to be tracked also BTW.
    So Referrer-Policy is set to no-referrer.
    This is not configurable since there is no need to.

-   About Feature-Policy

    https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy

    All enabled features are set to self, since Omen provides all the libs. No CDN is used for security purpose. Alose to enable the use on offlines networks. Only down side of this is Google Api to display
    docs wont be available.
    Only use is made of:

    -   autoplay for video
    -   fullscreen for multiple files type view
    -   layout-animations for Omen interface to display
    -   legacy-image-formats

        This policy controls the ability of the document to render images in legacy image formats. These are defined as any format other than JPEG, PNG, GIF, WEBP, or SVG.
        TODO Inspect Display possibilities on images formats

    -   midi

        TODO can midi file be played ?

    -   navigation-override

        https://github.com/w3c/webappsec-feature-policy/issues/175

    -   oversized-images
    -   picture-in-picture for video play
    -   sync-xhr For ajax queries

    accelerometer 'none'; ambient-light-sensor 'none'; autoplay 'self'; battery 'none'; camera 'none';
    display-capture 'none'; document-domain 'none'; encrypted-media 'none'; execution-while-not-rendered 'none'; execution-while-out-of-viewport 'none'; fullscreen 'self'; geolocation 'none'; gyroscope 'none';
    layout-animations 'self'; legacy-image-formats 'self'; magnetometer 'none'; microphone 'none';
    midi 'self'; navigation-override 'self'; oversized-images 'self; payment 'none'; picture-in-picture 'self';
    publickey-credentials-get 'none'; sync-xhr 'self'; usb 'none'; vr 'none'; wake-lock 'none' xr-spatial-tracking 'none';

-   About max upload size

    Because upload is ajax handled yt has to be changed on `php.ini` or `.htaccess`, two values needs to be changed:
    'upload_max_filesize' and 'post_max_size'

    You can put this in `public/.htaccess`

        php_value post_max_size 2M
        php_value upload_max_filesize 2M

    Because upload is ajax handled, it sends chunks of 2M size. Then if you want to set a limit
    just change maxUploadSize to something like `'3M'` or `3145728`

-   About file names

    File names are always lower cased on created, this is for Windows compatibility since windows file names
    are not case sensible which means windows will threat 'file.pdf' 'File.pdf' as the same file
    whereas on a Unix based it will not.

-   About unit tests

    Include unit tests using Orchestra, just run `phpunit` from project folder after `composer install`

-   About Tinymce

    To use with tinymce you will have to set useXFrameOptions to true, in order to allow the display
    omen in a iframe

    Here is an example, you must set `external_filemanager_path` with directive `@omenPath()` and
    register the omen plugin `external_plugins` with `{ "omen": "@tinymcePluginPath()" }`

        tinymce.init({
            selector: "#tinymce"
            , plugins: [
                "advlist autolink link image lists charmap print preview hr anchor pagebreak"
                , "searchreplace wordcount visualblocks visualchars insertdatetime media nonbreaking"
                , "table directionality emoticons paste omen code"
            ]
            , toolbar1: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | styleselect"
            , toolbar2: "| omen | link unlink anchor | image media | forecolor backcolor  | print preview code "
            , image_advtab: true
            , external_filemanager_path: "@omenPath()"
            , external_plugins: {
                "omen": "@tinymcePluginPath()"
            }
        });

-   About CKEditor

    To use with CKEditor no need for iframe autorization. However you will have to set it as the default filemanager.
    You will have to set `filebrowserImageBrowseUrl` with this directive `@omenPath(type=image&editor=ckeditor)`
    and eventually `filebrowserBrowseUrl` with this directive @omenPath(editor=ckeditor)

        CKEDITOR.editorConfig = function(config) {
            config.language = "en";
            config.uiColor = "#F7B42C";
            config.height = 300;
            config.toolbarCanCollapse = true;
            config.extraPlugins = "omen";
            config.filebrowserImageBrowseUrl = "@omenPath(type=image&editor=ckeditor)";
            config.filebrowserBrowseUrl = "@omenPath(editor=ckeditor)";
        };
        CKEDITOR.replace("ckeditor");

Some thing good to know is that it is possible to restrict file types to be used with url directive:
eg: add url GET parameter `type=image` to use image only or `type=archives`

-   About standalone button

    You can use a standalone button to call the file manager if you want to.

    First insert the plugin (requires `jquery`) in you html file

        <html>
            ...
            <head>
                ...
                @omenButton()
            </head>
            ...
        </html>

    Create an input on your page

        <div class="input-group">
            <span class="input-group-btn">
                <a id="omenButton" data-input="textInput" class="btn btn-primary">
                <i class="fa fa-picture-o"></i> Open file </a>
            </span>
            <input id="textInput" class="form-control" type="text" name="filepath">
        </div>

    Call the plugin on your button

        $(document).ready(function() {
            $('#omenButton').omenButton('image');
        });

    `omenButton` function accepts file, image, audio, video, archive in case you want to restrict file types

    You may want to look at `resources/js/plugins/omen.Button.jquery.plugin.min.js` if you want to call with js the file manager via popup

---

**Whishlit**

-   [ ] cross domain support for tinymce and ckeditor ?
-   [ ] Video stream, with optionnal ffmepg => Mpeg-Dash or HLS ?
        https://github.com/pascalbaljetmedia/laravel-ffmpeg
-   [ ] Resize image keep ratio function
-   [ ] global search file
-   [ ] change visibility
-   [ ] more global actions such as mass delete
-   [ ] use thumbnails for images
-   [ ] allow delete non empty dir, confirm delete non empty dirs
-   [ ] more front end unit test with Dusk
-   [ ] test functions with chromium
-   [ ] extensive test on mobile
-   [ ] make button reset filters

Jérémy Munsch<br>
[https://jereymdev.ovh/contact](https://jereymdev.ovh/contact)<br>
[gitub@jeremydev.ovh](github@jeremydev.ovh)
