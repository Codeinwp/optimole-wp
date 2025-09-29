jQuery(document).ready(function($) {
    if (!$('body').hasClass('upload-php')) {
        return;
    }

    var originalAttachmentDetails = wp.media.view.Attachment.Details;
    
    wp.media.view.Attachment.Details = originalAttachmentDetails.extend({
        initialize: function() {
            originalAttachmentDetails.prototype.initialize.apply(this, arguments);
        },
        
        render: function() {
            originalAttachmentDetails.prototype.render.apply(this, arguments);
            
            this.addReplaceRenameButton();
            
            return this;
        },
        
        addReplaceRenameButton: function() {
            var self = this;
            
            var $el = self.$el;
            var $actions = $el.find('.actions');
            
            if ($actions.find('.optml-replace-rename-link').length) {
                return;
            }
            
            var attachmentId = self.model.get('id');
            
            if (attachmentId && $actions.length) {
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
        }
    });
    
    if (wp.media.view.Attachment.Details.TwoColumn) {
        var originalTwoColumn = wp.media.view.Attachment.Details.TwoColumn;
        
        wp.media.view.Attachment.Details.TwoColumn = originalTwoColumn.extend({
            initialize: function() {
                originalTwoColumn.prototype.initialize.apply(this, arguments);
            },
            
            render: function() {
                originalTwoColumn.prototype.render.apply(this, arguments);
                
                this.addReplaceRenameButton();
                
                return this;
            },
            
            addReplaceRenameButton: function() {
                var self = this;
                
                var $el = self.$el;
                var $actions = $el.find('.actions');
                
                if ($actions.find('.optml-replace-rename-link').length) {
                    return;
                }
                
                var attachmentId = self.model.get('id');
                
                if (attachmentId && $actions.length) {
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
            }
        });
    }
    
    $(document).on('click', '.optml-replace-rename-link.thickbox', function() {
        tb_init('a.thickbox');
    });
});