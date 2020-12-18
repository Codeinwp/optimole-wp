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
                })
            ]);
            this.on( 'content:render:optimole', this.renderOptimole, this );
            this.on(
                    'content:render:browse content:render:upload', function(){
                        $( document ).find( '.media-button-select' ).attr('disabled', 'disabled').removeAttr( 'enabled', 'enabled');
                    }, this
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
        renderOptimole: function() {

            $( document ).find( '.media-button-select' ).attr('disabled', 'disabled').removeAttr( 'enabled', 'enabled');

            var state = this.state();

            let options = this.options.library;
            options.type = ['optml_cloud'];
            wp.media.query( options ).each(function(v){
                console.log( v.toJSON() );
            });
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
        }
    });
});
