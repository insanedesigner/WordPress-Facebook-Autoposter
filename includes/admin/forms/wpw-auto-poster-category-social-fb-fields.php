<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Facebook category selection fields
 *
 * The html markup for the Facebook accounts in dropdown.
 *
 * @package Social Auto Poster
 * @since 2.3.1
 */
$cat_id = "";


if (isset($_GET['tag_ID']) && !empty($_GET['tag_ID']))
    $cat_id = $_GET['tag_ID'];

// Getting facebook all accounts
$fb_accounts = wpw_auto_poster_get_fb_accounts('all_app_users_with_name');
$wpw_auto_poster_fb_sess_data = get_option('wpw_auto_poster_fb_sess_data'); // Getting facebook app grant data

$fb_selected_acc = array();
$selected_acc = get_option('wpw_auto_poster_category_posting_acct');
$fb_selected_acc = ( isset($selected_acc[$cat_id]['fb']) && !empty($selected_acc[$cat_id]['fb']) ) ? $selected_acc[$cat_id]['fb'] : $fb_selected_acc;
?>

<tr class="form-field term-wpw-auto-poster-fb-wrap">
    <th for="tag-description"><?php _e('Post To This Facebook Account(s):', 'wpwautoposter'); ?></th>
    <td>       
        <select name="wpw_auto_category_poster_options[fb][]" id="wpw_auto_poster_fb_type_post_method" style="width:300px;"  multiple>
            <?php
            if (!empty($fb_accounts) && is_array($fb_accounts)) {

                foreach ($fb_accounts as $aid => $aval) {

                    if (is_array($aval)) {
                        $fb_app_data = isset($wpw_auto_poster_fb_sess_data[$aid]) ? $wpw_auto_poster_fb_sess_data[$aid] : array();
                        $fb_user_data = isset($fb_app_data['wpw_auto_poster_fb_user_cache']) ? $fb_app_data['wpw_auto_poster_fb_user_cache'] : array();
                        $fb_opt_label = !empty($fb_user_data['name']) ? $fb_user_data['name'] . ' - ' : '';
                        $fb_opt_label = $fb_opt_label . $aid;
                        ?>
                        <optgroup label="<?php echo $fb_opt_label; ?>">

                            <?php foreach ($aval as $aval_key => $aval_data) { ?>
                                <option value="<?php echo $aval_key; ?>" <?php selected(in_array($aval_key, $fb_selected_acc), true, true); ?>><?php echo $aval_data; ?></option>
                            <?php } ?>

                        </optgroup>

                    <?php } else {
                        ?>
                        <option value="<?php echo $aid; ?>" <?php selected(in_array($aid, $wpw_auto_poster_fb_type_user), true, true); ?> ><?php echo $aval; ?></option>
                    <?php
                    }
                } // End of foreach
            } // End of main if
            ?>
        </select>
        <script type="text/javascript">
            jQuery("#wpw_auto_poster_fb_type_post_method").chosen({search_contains:true});
        </script>
        <p class="description"><?php _e( 'Post belongs to this category will be posted to selected account(s). This setting overrides the global default, but can be overridden by a post. Leave it it empty to use the global defaults.', 'wpwautoposter' ); ?></p>
    </td>
</tr>
