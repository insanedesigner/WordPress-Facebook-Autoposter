<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Pinterest category selection fields
 *
 * The html markup for the Pinterest accounts in dropdown.
 *
 * @package Social Auto Poster
 * @since 2.6.0
 */
$cat_id = "";


if (isset($_GET['tag_ID']) && !empty($_GET['tag_ID']))
    $cat_id = $_GET['tag_ID'];

// Getting all pinterest account/boards
$pin_accounts = wpw_auto_poster_get_pin_accounts( 'all_app_users_with_boards' );
$wpw_auto_poster_pin_sess_data = get_option( 'wpw_auto_poster_pin_sess_data' ); // Getting pinterest app grant data

$pin_selected_acc = array();
$selected_acc = get_option('wpw_auto_poster_category_posting_acct');
$pin_selected_acc = ( isset($selected_acc[$cat_id]['pin']) && !empty($selected_acc[$cat_id]['pin']) ) ? $selected_acc[$cat_id]['pin'] : $pin_selected_acc;
?>

<tr class="form-field term-wpw-auto-poster-pin-wrap">
    <th for="tag-description"><?php _e('Post To This Pinterest Account(s):', 'wpwautoposter'); ?></th>
    <td>       
        <select name="wpw_auto_category_poster_options[pin][]" id="wpw_auto_poster_pin_type_post_method" style="width:300px;"  multiple>
        <?php
            if( !empty($pin_accounts) && is_array($pin_accounts) ) {
                
                foreach( $pin_accounts as $aid => $aval ) {
                    
                    if( is_array( $aval ) ) {

                        $pin_app_data   = isset( $wpw_auto_poster_pin_sess_data[$aid] ) ? $wpw_auto_poster_pin_sess_data[$aid] : array();

                        $pin_opt_label  = !empty( $pin_app_data['wpw_auto_poster_pin_user_name'] ) ? $pin_app_data['wpw_auto_poster_pin_user_name'] .' - ' : '';
                        $pin_opt_label  = $pin_opt_label . $aid;
                ?>
                        <optgroup label="<?php echo $pin_opt_label; ?>">
                        
                        <?php foreach ( $aval as $aval_key => $aval_data ) { ?>
                            <option value="<?php echo $aval_key; ?>" <?php selected( in_array( $aval_key, $pin_selected_acc ), true, true ); ?> ><?php echo $aval_data; ?></option>
                        <?php } ?>
                        
                        </optgroup>
                        
            <?php   } else { ?>
                            <option value="<?php echo $aid; ?>" <?php selected( in_array( $aid, $wpw_auto_poster_pin_type_user ), true, true ); ?> ><?php echo $aval; ?></option>
            <?php   }
                
                } // End of foreach
            } // End of main if
        ?>
        </select>
        <script type="text/javascript">
            jQuery("#wpw_auto_poster_pin_type_post_method").chosen({search_contains:true});
        </script>
        <p class="description"><?php _e( 'Post belongs to this category will be posted to selected account(s). This setting overrides the global default, but can be overridden by a post. Leave it it empty to use the global defaults.', 'wpwautoposter' ); ?></p>
    </td>
</tr>
