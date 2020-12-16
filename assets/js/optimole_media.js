jQuery(document).ready( function($){

    const getDefaultMenuItems = function() {
        let l10n = wp.media.view.l10n,
            items = {
                upload: {
                    text: l10n.uploadFilesTitle,
                    priority: 20
                },
                browse: {
                    text: l10n.mediaLibraryTitle,
                    priority: 40
                }
            };
        return items;
    };
    var media = wp.media,
        l10n = media.view.l10n = typeof _wpMediaViewsL10n === 'undefined' ? {} : _wpMediaViewsL10n;

    media.view.MediaFrame.Select.prototype.browseRouter = function( routerView ) {
        let items =getDefaultMenuItems();
        items ['optimole_cloud'] = {
            text:     "Optimole Cloud",
            priority: 60
        }
        routerView.set( items );
    };

    var bindHandlers = media.view.MediaFrame.Select.prototype.bindHandlers;
    media.view.MediaFrame.Select.prototype.bindHandlers = function () {
        bindHandlers.apply( this, arguments );
        this.on( 'content:create:optimole_cloud', this.optimoleContent, this );
        this.on(
            'content:render:optimole_cloud', function(){
                $( document ).find( '.media-button-select' ).attr('disabled', 'disabled').removeAttr( 'enabled', 'enabled').removeClass('optml_enabled');
            }, this
        );
        this.on(
            'content:render:browse content:render:upload', function(){
                $( document ).find( '.media-button-select' ).attr('disabled', 'disabled').removeAttr( 'enabled', 'enabled').removeClass('optml_enabled');
            }, this
        );
    };
    media.view.MediaFrame.Select.prototype.optimoleContent = function ( contentRegion ) {
        var state = this.state();

        // this.$el.removeClass( 'hide-toolbar' );

        contentRegion.view = new wp.media.view.RemotePhotos(
            {
                controller: this,
                collection: state.get( 'library' ),
                selection:  state.get( 'selection' ),
                model:      state,
                sortable:   state.get( 'sortable' ),
                search:     state.get( 'searchable' ),
                filters:    state.get( 'filterable' ),
                date:       state.get( 'date' ),
                display:    state.has( 'display' ) ? state.get( 'display' ) : state.get( 'displaySettings' ),
                dragInfo:   state.get( 'dragInfo' ),

                idealColumnWidth: state.get( 'idealColumnWidth' ),
                suggestedWidth:   state.get( 'suggestedWidth' ),
                suggestedHeight:  state.get( 'suggestedHeight' ),

                AttachmentView: state.get( 'AttachmentView' )
            }
        );
    };

    // ensure only one scroll request is sent at one time.
    var scroll_called = false;
    media.view.RemotePhotos = media.View.extend(
        {
            tagName: 'div',
            className: 'attachments-browser',

            initialize: function () {
                // _.defaults(this.options, {});
                var container = this.$el;
                this.loadContent( container,this );
                this.selectItem();
                this.deselectItem();
                this.handleRequest();
            },

            // showSpinner: function(container) {
            //     $( container ).find( '.obfx-image-list' ).addClass( 'obfx_loading' );
            //     $( container ).find( '.obfx_spinner' ).show();
            //
            // },
            // hideSpinner: function(container) {
            //     $( container ).find( '.obfx-image-list' ).removeClass( 'obfx_loading' );
            //     $( container ).find( '.obfx_spinner' ).hide();
            // },
            loadContent: function(container, frame){
                console.log("content:create:optimole_cloud");
                // this.showSpinner( container );
                $.ajax(
                    {
                        type : 'POST',
                        data : {
                            action: 'get_optimole_cloud_content',
                            security : optimole_cloud.nonce
                        },
                        url : optimole_cloud.ajaxurl,
                        success : function(response) {
                            var template = wp.template('my-template');
                            container.html( template( JSON.parse(response) ) );
                            // frame.infiniteScroll( container, frame );
                        }
                    }
                );
            },

            selectItem : function(){

                $( document ).on(
                            'click', '.optml_image', function (e) {
                                e.stopPropagation();
                                $( document ).find( 'li.selected.optml_checked' ).removeClass( 'selected details optml_checked' ).addClass( 'optml_image' );
                                $( this ).addClass( 'selected details optml_checked' );
                                $( this ).removeClass( 'optml_image' );

                                $( document ).find( '.media-button-select' ).removeAttr( 'disabled', 'disabled');
                                $( document ).find( '.media-button-select' ).attr( 'enabled', 'enabled').addClass( 'optml_enabled' );
                            }
                        );
            },

            deselectItem :function () {
                $( document ).on(
                    'click', '.optml_checked', function (e) {
                        e.stopPropagation();
                        $( this ).removeClass( 'selected details optml_checked' );
                        $( this ).addClass( 'optml_image' );
                        $( document ).find( '.media-button-select' ).removeAttr( 'enabled', 'enabled').removeClass('optml_enabled');
                        $(document).find( '.media-button-select' ).attr('disabled', 'disabled');

                    }
                );
            },
            handleRequest : function () {
                        $( document ).on(
                            'click','.media-button-select.optml_enabled', function () {
                                // $( document ).find( '.media-button-select' ).attr( 'disabled', 'disabled' ).html( optimole_cloud.l10n.add_image);
                                $.ajax(
                                    {
                                        method : 'POST',
                                        data : {
                                            'action': 'add_image_to_page',
                                            'url' : $( 'li.optml_checked' ).attr( 'data-url' ),
                                            'post_id' : optimole_cloud.post_id,
                                            'security' : optimole_cloud.nonce
                                        },
                                        url : optimole_cloud.ajaxurl,
                                        success : function(res) {
                                            //after I close this I would like the image to be instead of the block not sure how to make it tried with the ajax route
                                            wp.media.frame.close();
                                            console.log(res);

                                        }
                                    }
                                );
                            }
                        );
                    },
        }
    );

});
