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

            }, this
        );
        this.on(
            'content:render:browse content:render:upload', function(){

            }, this
        );
    };
    media.view.MediaFrame.Select.prototype.optimoleContent = function ( contentRegion ) {
        var state = this.state();

        this.$el.removeClass( 'hide-toolbar' );

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
    var selected_images = 0;
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
                // this.handleRequest();
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
                                $( this ).addClass( 'selected details optml_checked' );
                                $( this ).removeClass( 'optml_image' );
                                selected_images ++;
                                $( document ).find( '.media-button-select' ).removeAttr( 'disabled', 'disabled');
                                $( document ).find( '.media-button-select' ).attr( 'enabled', 'enabled');
                            }
                        );
            },

            deselectItem :function () {
                $( document ).on(
                    'click', '.optml_checked', function (e) {
                        e.stopPropagation();
                        $( this ).removeClass( 'selected details optml_checked' );
                        $( this ).addClass( 'optml_image' );
                        selected_images --;
                        if ( selected_images <= 0 ) {
                            $( document ).find( '.media-button-select' ).removeAttr( 'enabled', 'enabled');
                            $(document).find( '.media-button-select' ).attr('disabled', 'disabled');
                        }
                    }
                );
            },
        //
        //     infiniteScroll : function (container, frame) {
        //         $( '#obfx-mystock .obfx-image-list' ).on(
        //             'scroll',function() {
        //                 if ($( this ).scrollTop() + $( this ).innerHeight() + 10 >= $( this )[0].scrollHeight) {
        //                     var current_page = parseInt( $( '#obfx-mystock' ).data( 'pagenb' ) );
        //                     if (parseInt( optimole_cloud.pages ) === current_page) {
        //                         return;
        //                     }
        //                     if (scroll_called) {
        //                         return;
        //                     }
        //                     scroll_called = true;
        //                     frame.showSpinner( container );
        //                     $.ajax(
        //                         {
        //                             type : 'POST',
        //                             data : {
        //                                 'action': 'infinite-' + optimole_cloud.slug,
        //                                 'page' : $( '#obfx-mystock' ).data( 'pagenb' ),
        //                                 'security' : optimole_cloud.nonce
        //                             },
        //                             url : optimole_cloud.ajaxurl,
        //                             success : function(response) {
        //                                 scroll_called = false;
        //                                 if ( response ) {
        //                                     var imageList = $( '.obfx-image-list' );
        //                                     var listWrapper = $( '#obfx-mystock' );
        //                                     var nextPage = parseInt( current_page ) + 1;
        //                                     listWrapper.data( 'pagenb', nextPage );
        //                                     imageList.append( response );
        //                                 }
        //                                 frame.hideSpinner( container );
        //                                 frame.deselectItem();
        //                             }
        //
        //                         }
        //                     );
        //                 }
        //             }
        //         );
        //     },
        //
        //     handleRequest : function () {
        //         $( document ).on(
        //             'click','.obfx-mystock-insert', function () {
        //                 $( document ).find( '.media-button-insert' ).attr( 'disabled', 'disabled' ).html( optimole_cloud.l10n.upload_image );
        //                 $.ajax(
        //                     {
        //                         method : 'POST',
        //                         data : {
        //                             'action': 'handle-request-' + optimole_cloud.slug,
        //                             'url' : $( '.obfx-image.selected' ).attr( 'data-url' ),
        //                             'security' : optimole_cloud.nonce
        //                         },
        //                         url : optimole_cloud.ajaxurl,
        //                         success : function(res) {
        //                             $( document ).find( '.media-button-insert' ).attr( 'disabled', 'disabled' ).html( optimole_cloud.l10n.insert_image_new );
        //                             if ( 'mystock' === wp.media.frame.content.mode() ) {
        //                                 wp.media.frame.content.get( 'library' ).collection.props.set( { '__ignore_force_update': (+ new Date()) } );
        //                                 wp.media.frame.content.mode( 'browse' );
        //                                 $( document ).find( '.media-button-insert' ).attr( 'disabled', 'disabled' );
        //                                 wp.media.frame.state().get( 'selection' ).reset( wp.media.attachment( res.data.attachment.id ) );
        //                                 $( document ).find( '.media-button-insert' ).trigger( 'click' );
        //                             }
        //                         }
        //                     }
        //                 );
        //             }
        //         );
        //
        //         $( document ).on(
        //             'click','.obfx-mystock-featured', function () {
        //                 $( document ).find( '.media-button-select' ).attr( 'disabled', 'disabled' ).html( optimole_cloud.l10n.upload_image );
        //                 $.ajax(
        //                     {
        //                         method : 'POST',
        //                         data : {
        //                             'action': 'handle-request-' + optimole_cloud.slug,
        //                             'url' : $( '.obfx-image.selected' ).attr( 'data-url' ),
        //                             'security' : optimole_cloud.nonce
        //                         },
        //                         url : optimole_cloud.ajaxurl,
        //                         success : function(res) {
        //                             $( document ).find( '.media-button-select' ).attr( 'disabled', 'disabled' ).html( optimole_cloud.l10n.featured_image_new );
        //                             if ( 'mystock' === wp.media.frame.content.mode() ) {
        //                                 wp.media.frame.content.get( 'library' ).collection.props.set( { '__ignore_force_update': (+ new Date()) } );
        //                                 wp.media.frame.content.mode( 'browse' );
        //                                 $( document ).find( '.media-button-select' ).attr( 'disabled', 'disabled' );
        //                                 wp.media.frame.state().get( 'selection' ).reset( wp.media.attachment( res.data.attachment.id ) );
        //                                 $( document ).find( '.media-button-select' ).trigger( 'click' );
        //                             }
        //                         }
        //                     }
        //                 );
        //             }
        //         );
        //     }
        }
    );

});
