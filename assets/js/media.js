jQuery(document).ready(function($) {
    jQuery('.move-image-optml').click(function() {
        //get the id and send a jquery rest request to the server
        var id = jQuery(this).data('id');
        var action = jQuery(this).data('action'); 
        moveImage(id, action, jQuery(this));
    }); 
});

function moveImage(id, action, element, is_retry = false) {
    //add a loading indicator
    element.parent().find('.spinner').addClass('is-active');
    element.parent().addClass('is-loading');
    jQuery.ajax({
        url:  optimoleMediaListing.rest_url,
        type: 'POST',
        headers: {
            'X-WP-Nonce': optimoleMediaListing.nonce
        },
        data: {
            action: action,
            status: is_retry ? 'check' : 'start',
            id: id  
        },
        success: function(response) {
            if(response.code === 'moved') {
                element.parent().find('.spinner').removeClass('is-active');
                element.parent().removeClass('is-loading'); 
                element.parent().find('.move-image-optml').toggleClass('hidden'); 

            }else if(response.code === 'error'){
                element.parent().find('.spinner').removeClass('is-active');
                element.parent().removeClass('is-loading'); 
                element.parent().text(response.data);
            }else{
                setTimeout(function() {
                    moveImage(id, action, element, true);
                }, 1000);
            }
        }
    });
}