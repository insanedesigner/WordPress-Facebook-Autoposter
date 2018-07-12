<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * LinkedIn category selection fields
 *
 * The html markup for the LinkedIn accounts in dropdown.
 *
 * @package Social Auto Poster
 * @since 2.3.1
 */
global $wpw_auto_poster_li_posting;

//linkedin posting class
$liposting = $wpw_auto_poster_li_posting;
$cat_id = "";

//Get linkedin Profiles Data
$li_profile_data = $liposting->wpw_auto_poster_get_profiles_data();

if (isset($_GET['tag_ID']) && !empty($_GET['tag_ID']))
    $cat_id = $_GET['tag_ID'];

$li_selected_acc = array();
$li_account_details = get_option('wpw_auto_poster_li_account_details', array());
$selected_acc = get_option('wpw_auto_poster_category_posting_acct');

$li_selected_acc = ( isset($selected_acc[$cat_id]['li']) && !empty($selected_acc[$cat_id]['li']) ) ? $selected_acc[$cat_id]['li'] : $li_selected_acc;
?>
<tr class="form-field term-wpw-auto-poster-li-wrap">
    <th for="tag-description"><?php _e('Post To This Linkedin Account(s):', 'wpwautoposter'); ?></th>
    <td>
        <select name="wpw_auto_category_poster_options[li][]" id="wpw_auto_poster_li_type_post_method" style="width:300px;" multiple>
            <?php
            if (!empty($li_profile_data)) {

                foreach ($li_profile_data as $li_key => $li_value) {
                    echo '<option value="' . $li_key . '" ' . selected(in_array($li_key, $li_selected_acc), true, true) . '>' . $li_value . '</option>';
                }
            } //end if to check there is user connected to LinkedIn or not
            ?>
        </select>
        <script type="text/javascript">
            jQuery("#wpw_auto_poster_li_type_post_method").chosen({search_contains:true});
        </script>
        <p class="description"><?php _e( 'Post belongs to this category will be posted to selected account(s). This setting overrides the global default, but can be overridden by a post. Leave it it empty to use the global defaults.', 'wpwautoposter' ); ?></p>
    </td>
</tr>