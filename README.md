<img align="left" width="80" height="80" src="https://user-images.githubusercontent.com/6019313/82305874-8ad0d800-99be-11ea-8655-6d3ab5deb43f.png" alt="Omen project icon">

# &nbsp;&nbsp;&nbsp; Omen file manager

---

### **This is a Work in progress**

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

If you are using local storage you must link the public storage to your public folder

    sudo php artisan omen:link

---

**TODO:**

-   [x] multiple row figure display
-   [x] upload files
-   [x] loading screen
-   [x] remember view disposition and redo on page reload
-   [x] make directory change ajax
-   [x] fix directory create in coffee
-   [x] check sanitize path in omencontroller
-   [x] deactivate filters on ajax directory change
-   [x] handle breadcrumb click directory change
-   [x] unifiy click or double click on breadcrumb navigation
-   [ ] make button reset filters
-   [x] make upload to put files in correct path
-   [x] inject uploaded inode
-   [x] fix uploaded filerename increment
-   [x] ~create a Loading Toast, and display on ajax queries~ progressbar created
-   [ ] create lock ui function to, freeze ui while navigating
-   [ ] drag and drop
-   [ ] handle 419 error session timeout => CSRF token mismatch
-   [ ] global search file
-   [ ] copy and paste function
-   [ ] select appears on figure hover for icon view and stay showed if one is selected
-   [ ] edit text files
-   [ ] right click menu
-   [ ] work on left panel fancy tree
-   [ ] correct file delete error not handled properly need ajax response and error handle
-   [ ] correct bu file and folder create upper case
-   [ ] support zoom in image viewer
-   [ ] add error message on text viewer ?
-   [ ] prevent share links if storage is private and url not accessible
-   [ ] polish PDF viewer
-   [ ] rework exception error codes
-   [ ] extensive test on mobile
-   [ ] unit tests ?
-   [ ] rework sort logic
-   [ ] implement configurable shortcuts
-   [ ] file uploaded size return to ajax is null => size sort error
-   [ ] fix sort error when only 1 inode to display
