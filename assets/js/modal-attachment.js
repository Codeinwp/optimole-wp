jQuery(document).ready(function($) {
    if (!$('body').hasClass('upload-php')) {
        return;
    }

    /**
     * Helper function to add Replace or Rename button to attachment actions
     * @param {Object} view - The attachment view instance
     */
    function addReplaceRenameButton(view) {
        var $el = view.$el;
        var $actions = $el.find('.actions');
        
        if (!$actions.length || $actions.find('.optml-replace-rename-link').length) {
            return;
        }
        
        var attachmentId = view.model.get('id');
        
        if (!attachmentId) {
            return;
        }
        
        var editUrl = OptimoleModalAttachment.editPostURL + '?post=' + attachmentId + 
                     '&action=edit&TB_iframe=true&width=90%&height=90%';
        
        var $editLink = $actions.find('a[href*="post.php"]');
        
        if ($editLink.length) {
            $editLink.after(
                ' <span class="links-separator">|</span>' +
                '<a href="' + editUrl + '" class="optml-replace-rename-link thickbox" title="' + 
                OptimoleModalAttachment.i18n.replaceOrRename + '"> ' +
                OptimoleModalAttachment.i18n.replaceOrRename + '</a>'
            );
        }
    }

    /**
     * Extend a WordPress media view with Replace/Rename functionality
     * @param {Object} OriginalView - The original view to extend
     * @returns {Object} Extended view
     */
    function extendMediaView(OriginalView) {
        return OriginalView.extend({
            initialize: function() {
                OriginalView.prototype.initialize.apply(this, arguments);
            },
            
            render: function() {
                OriginalView.prototype.render.apply(this, arguments);
                addReplaceRenameButton(this);
                return this;
            }
        });
    }

    var originalAttachmentDetails = wp.media.view.Attachment.Details;
    wp.media.view.Attachment.Details = extendMediaView(originalAttachmentDetails);
    
    if (wp.media.view.Attachment.Details.TwoColumn) {
        var originalTwoColumn = wp.media.view.Attachment.Details.TwoColumn;
        wp.media.view.Attachment.Details.TwoColumn = extendMediaView(originalTwoColumn);
    }
    
    $(document).on('click', '.optml-replace-rename-link.thickbox', function() {
        tb_init('a.thickbox');
    });
});