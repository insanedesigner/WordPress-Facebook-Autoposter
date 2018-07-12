<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Instagram category selection fields
 *
 * The html markup for the Instagram accounts in dropdown.
 *
 * @package Social Auto Poster
 * @since 2.6.0
 */
$cat_id = "";
global $wpw_auto_poster_options;


if (isset($_GET['tag_ID']) && !empty($_GET['tag_ID']))
    $cat_id = $_GET['tag_ID'];

// Getting all instagram accounts
$instagram_keys = isset( $wpw_auto_poster_options['instagram_keys'] ) ? $wpw_auto_poster_options['instagram_keys'] : array();
$ins_accounts = $instagram_keys;

$ins_selected_acc = array();
$selected_acc = get_option('wpw_auto_poster_category_posting_acct');
$ins_selected_acc = ( isset($selected_acc[$cat_id]['ins']) && !empty($selected_acc[$cat_id]['ins']) ) ? $selected_acc[$cat_id]['ins'] : $ins_selected_acc;
?>

<tr class="form-field term-wpw-auto-poster-ins-wrap">
    <th for="tag-description"><?php _e('Post To This Instagram Account(s):', 'wpwautoposter'); ?></th>
    <td>       
        <select name="wpw_auto_category_poster_options[ins][]" id="wpw_auto_poster_ins_type_post_method" style="width:300px;"  multiple>
            <?php
                if( !empty($ins_accounts) && is_array($ins_accounts) ) {
                    
                    foreach( $ins_accounts as $aid => $aval ) {

                        if( is_array( $aval ) ) { 
                                $value = $aval['username']."|".$aval['password'];
                                ?>
                                <option value="<?php echo $value; ?>" <?php selected( in_array( $value, $ins_selected_acc ), true, true ); ?>><?php echo $aval['username']; ?></option>
                            
                            </optgroup>
                            
                <?php   } else { ?>
                                <option value="<?php echo $aid; ?>" <?php selected( in_array( $aid, $wpw_auto_poster_ins_type_user ), true, true ); ?> ><?php echo $aval; ?></option>
                <?php   }
                    
                    } // End of foreach
                } // End of main if
            ?>
        </select>
        <script type="text/javascript">
            jQuery("#wpw_auto_poster_ins_type_post_method").chosen({search_contains:true});
        </script>
        <p class="description"><?php _e( 'Post belongs to this category will be posted to selected account(s). This setting overrides the global default, but can be overridden by a post. Leave it it empty to use the global defaults.', 'wpwautoposter' ); ?></p>
    </td>
</tr>
