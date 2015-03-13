/*
 * Bolt module: App
 */
var BoltApp = (function (bolt, $) {
    /*
     * Legacy stuff from start.js
     *
     * @returns {undefined}
     */
    function legacy() {
        // Get passed in data from Twig function data()
        bolt.data = $('script[data-jsdata]').first().data('jsdata');

        // Initialize objects
        bolt.files = new Files();
        bolt.folders = new Folders();
        bolt.stack = new Stack();
        bolt.sidebar = new Sidebar();
        bolt.navpopups = new Navpopups();
        bolt.moments = new Moments();
        bolt.imagelist = [];
        bolt.filelist = [];

        // Initialisation
        bolt.datetimes.init();
        //
        if (typeof CKEDITOR !== 'undefined') {
            init.ckeditor();
        }
        init.confirmationDialogs();
        init.magnificPopup();
        init.dataActions();
        window.setTimeout(function () {
            init.keyboardShortcuts();
        }, 1000);
        init.dropZone();
        init.popOvers();
        init.activityWidget();
        init.dropDowns();
        init.deferredWidgets();
        init.passwordInput();
        init.dashboardCheckboxes();
        init.sortables();
        init.omnisearch();
        init.uploads();
        init.geolocation();
        init.focusStatusSelect();
        init.depublishTracking();

        $('[data-bind]').each(function () {
            var data = $(this).data('bind');
            //console.log('Binding: ' + data.bind);

            switch (data.bind) {
                case 'editcontent': init.bindEditContent(data); break;
                case 'editfile': init.bindEditFile(data); break;
                case 'editlocale': init.bindEditLocale(data); break;
                case 'filebrowser': init.bindFileBrowser(); break;
                case 'ckfileselect': init.bindCkFileSelect(); break;
                case 'prefill': init.bindPrefill(); break;
                case 'slug': init.bindSlug(data); break;
                case 'video': init.bindVideo(data); break;
                default: console.log('Binding ' + data.bind + ' failed!');
            }
        });
    }

    /*
     * Initialize the Bolt module
     */
    bolt.initApp = function () {
        bolt.initConf();
        legacy();
    };

    /*
     * Start when ready
     */
    $(document).ready(bolt.initApp);

    return bolt;
})(Bolt || {}, jQuery);
