jQuery(document).ready( function($){
    // var Library = wp.media.controller.Library;
    var oldMediaFrame = wp.media.view.MediaFrame.Select;
    // Extending the current media library frame to add a new tab
    wp.media.view.MediaFrame.Select = oldMediaFrame.extend({
        initialize: function() {

            oldMediaFrame.prototype.initialize.apply( this, arguments );
            this.states.add([
                new wp.media.controller.State({
                    id: 'optimole',
                    search: false,
                    title: 'Optimole'
                }),
            ]);
            this.on(
                    'content:render:browse content:render:upload', function(){
                        $( document ).find( '.media-button-select' ).attr('disabled', 'disabled').removeAttr( 'enabled', 'enabled');
                        $('#media-search-input').off();
                    }, this
                );
            let currentSearch = '';
            this.on( 'content:create:optimole',  this.renderOptimole, this );
        },
        browseRouter: function( routerView ) {
            const l10n = wp.media.view.l10n;
            routerView.set({
                upload: {
                    text: l10n.uploadFilesTitle,
                    priority: 20
                },
                browse: {
                    text: l10n.mediaLibraryTitle,
                    priority: 40
                },
                optimole: {
                    text: 'Optimole',
                    priority: 60
                }
            });
        },
        renderOptimole: function( search = '' ) {

            $( document ).find( '.media-button-select' ).attr('disabled', 'disabled').removeAttr( 'enabled', 'enabled');

            var state = this.state();

            let options = this.options.library;
            if ( typeof search === "string" ) {
                options.s=search;
            }
            options.type = ['optml_cloud'];
            const view = new wp.media.view.AttachmentsBrowser({
                controller: this,
                collection: wp.media.query( options ),
                selection: state.get( 'selection' ),
                model: state,
                sortable:   false,
                search:     true,
                filters:    false,
                date:       false,
                display:    false,
                dragInfo:   state.get('dragInfo'),
                idealColumnWidth: state.get( 'idealColumnWidth' ),
                suggestedWidth: state.get( 'suggestedWidth' ),
                suggestedHeight: state.get( 'suggestedHeight' ),
                AttachmentView: state.get( 'AttachmentView' )
            });
            this.content.set( view );
            let scope = this;
            function setSearch ( timeoutRef ) {
                if ( timeoutRef ) {
                    clearTimeout(timeoutRef);
                }
                //if nothing is written for 2 seconds query for images with current input
                let timeout = setTimeout( function () {
                    searchCurrent = $('#media-search-input').val();
                    scope.renderOptimole(searchCurrent);
                }, 2000 );
                return timeout;
            }
            let timeoutRef = false;
            //this will add the previos search text to the input after it renders
            let interval = setInterval(function () {
                    if ( $('#media-search-input').length > 0 ) {
                        $('#media-search-input').off();
                        $( '#media-search-input' ).bind(
                            'input',
                            function (e) {
                                timeoutRef = setSearch( timeoutRef );
                            }
                        );
                        $("#media-search-input").val(options.s);
                        clearInterval(interval);
                    }
                }, 500
            );
        }
    });
});
