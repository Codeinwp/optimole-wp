jQuery(document).ready( function($){
    // var Library = wp.media.controller.Library;
    var oldMediaFrame = wp.media.view.MediaFrame.Select;
    // Extending the current media library frame to add a new tab
    wp.media.view.MediaFrame.Select = oldMediaFrame.extend({
        initialize: function() {
            this.page = 1;
            oldMediaFrame.prototype.initialize.apply( this, arguments );
            this.states.add([
                new wp.media.controller.State({
                    id: 'optimole',
                    search: false,
                    title: 'Optimole'
                })
            ]);
            this.on( 'content:create:optimole',  this.renderOptimole, this );
            this.on(
                    'content:render:browse content:render:upload', function(){
                        $( document ).find( '.media-button-select' ).attr('disabled', 'disabled').removeAttr( 'enabled', 'enabled');
                    }, this
                );
            let scope = this;

            $( document ).on(
                'click', '.optml-load-more', function (e) {
                    e.stopPropagation();
                    scope.page ++;
                    scope.renderOptimole();

                }
            );
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
        addToolbar : function () {
            let check = setInterval(function(){
                let toolbar = $('.attachments-browser>.media-toolbar');
                if ( toolbar.length ) {
                    if ( $('.media-toolbar-secondary>span.is-active').length === 0 ) {
                        toolbar.append("<button type=\"button\" class=\"button media-button button-primary button-large optml-load-more\" style='top: 30%; left: 50%; position: absolute;'>Load More Images</button>");
                        clearInterval(check);
                    }
                };
            }, 500);

        },
        renderOptimole: function( ) {

            $( document ).find( '.media-button-select' ).attr('disabled', 'disabled').removeAttr( 'enabled', 'enabled');

            var state = this.state();

            let options = this.options.library;
            options.type = ['optml_cloud'];
            options.page = this.page;

            const view = new wp.media.view.AttachmentsBrowser({
                controller: this,
                collection: wp.media.query( options ),
                selection: state.get( 'selection' ),
                model: state,
                sortable: false,
                search: false,
                filters: false,
                date: false,
                display: false,
                dragInfo: false,
                idealColumnWidth: state.get( 'idealColumnWidth' ),
                suggestedWidth: state.get( 'suggestedWidth' ),
                suggestedHeight: state.get( 'suggestedHeight' ),
                AttachmentView: state.get( 'AttachmentView' )
            });
            this.content.set( view );
            this.addToolbar();

        }
    });
});
