<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Twitter category selection fields
 *
 * The html markup for the Twitter accounts in dropdown.
 *
 * @package Social Auto Poster
 * @since 2.3.1
 */
$cat_id = "";

if (isset($_GET['tag_ID']) && !empty($_GET['tag_ID']))
    $cat_id = $_GET['tag_ID'];

$tw_selected_acc = array();
$tw_account_details = get_option('wpw_auto_poster_tw_account_details', array());
$selected_acc = get_option('wpw_auto_poster_category_posting_acct');

$tw_selected_acc = ( isset($selected_acc[$cat_id]['tw']) && !empty($selected_acc[$cat_id]['tw']) ) ? $selected_acc[$cat_id]['tw'] : $tw_selected_acc;
?>
<tr class="form-field term-wpw-auto-poster-tw-wrap">
    <th for="tag-description"><?php _e('Post To This Twitter Account(s):', 'wpwautoposter'); ?></th>
    <td>
        <select name="wpw_auto_category_poster_options[tw][]" id="wpw_auto_poster_tw_type_post_method" style="width:300px;" multiple>
            <?php
            if (!empty($tw_account_details) && count($tw_account_details) > 0) {

                foreach ($tw_account_details as $tw_key => $tw_value) {
                    echo '<option value="' . $tw_key . '" ' . selected(in_array($tw_key, $tw_selected_acc), true, true) . '>' . $tw_value . '</option>';
                }
            } //end if to check there is user connected to twitter or not
            ?>
        </select>
        <script type="text/javascript">
            jQuery("#wpw_auto_poster_tw_type_post_method").chosen({search_contains:true});
        </script>
        <p class="description"><?php _e( 'Post belongs to this category will be posted to selected account(s). This setting overrides the global default, but can be overridden by a post. Leave it it empty to use the global defaults.', 'wpwautoposter' ); ?></p>
    </td>
</tr>