/* 
 * @plugin  Ajax Shortcode
 */
var data = {
         action: 'query_acive_plugin',
         security: wp_ajax.ajaxnonce,
         plugin_name:''
     };

function switchFunction(event) 
    {
        console.log(jQuery(this).attr('plugin-name'))
        data.plugin_name = jQuery(jQuery(event.target)).attr('plugin-name');
        jQuery.post( 
            wp_ajax.ajaxurl, 
            data,                   
            function( response )
            {
                // ERROR HANDLING
                if( !response.success )
                {
                    // No data came back, maybe a security error
                    if( !response.data )
                      alert( 'AJAX ERROR: no response' );
                    else
                        alert( response.data.error );
                }
                else
                    alert( response.data.msg );
            }
        ); 
    };
