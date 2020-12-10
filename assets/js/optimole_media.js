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


    const generateImageHtml = function ( imgUrl ) {
        return ' <li tabIndex="0" role="checkbox" aria-label="" aria-checked="false" data-id="1679" className="attachment save-ready">\n'   +
            '<div class="attachment-preview js--select-attachment type-image subtype-jpeg landscape">' +
            '            <div class="thumbnail">\n' +
            '                <div class="centered">\n' +
            '                    <img src="http://b7d3ebacd3a2.ngrok.io/wp-content/uploads/2020/12/50665587368_8930a99caf_b-300x201.jpg" draggable="false" alt="">\n' +
            '                </div>\n' +
            '            </div>\n' +
            '        <button type="button" className="check" tabIndex="-1"><span class="media-modal-icon"></span><span class="screen-reader-text">Deselect</span></button>' +
            '</div>' +
            '    </li>';
    };

   const generateImageList = function () {
       let list = '<ul tabIndex="-1" className="attachments ui-sortable ui-sortable-disabled">';
       list = list + generateImageHtml ( "ffs" )+ '</ul>';

       return list;
   };
   const pullImages = function () {
      let mediaContent = jQuery(".media-frame-content");

      mediaContent.html("<div class=\"attachments-browser\">" + generateImageList() + "</div>");
    };
    const openModalCallback = function() {
        $( "#menu-item-optimole_cloud" ).click( pullImages );
        if( $('#menu-item-optimole_cloud').attr('aria-selected') === "true" ) {
            pullImages();
        }
    };

    wp.media.view.MediaFrame.Select.prototype.browseRouter = function( routerView ) {
        let items =getDefaultMenuItems();
        items ['optimole_cloud'] = {
            text:     "Optimole Cloud",
            priority: 60
        }
        routerView.set( items );

    };
    window.wp.media.view.Modal.prototype.on(
        'open',
        openModalCallback
    );
});
