<?php
/**
 * Newfangled Gravity Forms Act-On Integration
 *
 * @package   Newfangled Gravity Forms Act-On Integration
 * @author    Newfangled
 * @link      http://newfangled.com/plugin-support-policy
 * @copyright Newfangled 2016
 */

//*********************************************************************************************************
/*
Plugin Name: Newfangled Gravity Forms / Act-On Integration
Plugin URI: http://www.gravityforms.com
Description: Integrates Gravity Forms with ActOn allowing form submissions to be automatically sent to your ActOn account
Version: 2.0.4
Author: Newfangled
Author URI: http://newfangled.com
Copyright: Newfangled 2016
*/
//*********************************************************************************************************

add_action('init',  array('GFActOn', 'init'));
register_activation_hook( __FILE__, array("GFActOn", "add_permissions"));

//  Provide plugin updates
require 'plugin_update_check.php';
$MyUpdateChecker = new PluginUpdateChecker_2_0 (
    'https://kernl.us/api/v1/updates/58925025869345767c41ee3c/',
    __FILE__,
    'gfacton',
    1
);

class GFActOn {

    private static $path = "gravityformsacton/acton.php";
    private static $url = "http://www.newfangled.com/plugin-support-policy";
    private static $slug = "gravityformsacton";
    private static $version = "2.0.4";
    private static $min_gravityforms_version = "1.7.6.11";
    private static $supported_fields = array("checkbox", "radio", "select", "text", "website", "textarea", "email", "hidden", "number", "phone", "multiselect", "post_title",
                                    "post_tags", "post_custom_field", "post_content", "post_excerpt");

    /**
     * Verify the Newfangled Logging plugin is active
     */
    function verifyLogging( $transient ) {
        
        global $nflogging;

        if (!method_exists($nflogging, 'verifyLogging') OR (VERIFY_FAILED == $nflogging->verifyLogging())) {
            
            deactivate_plugins( plugin_basename( __FILE__ ) );    

        }

        return $transient;
    }
    
    /**
     *
     * Function: init
     * 
     */
    public static function init(){

        add_filter('pre_set_site_transient_update_plugins', array( 'GFActOn', 'verifyLogging' ), 10, 1);

        //  Add the tracking code to the footer
        add_action('wp_footer', array('GFActOn', 'add_tracking_code'));

        if(basename($_SERVER['PHP_SELF']) == "plugins.php") {

            add_action('after_plugin_row_' . self::$path, array('GFActOn', 'plugin_row') );

        }

        if(!self::is_gravityforms_supported()){
           return;
        }

        if(is_admin()){
            //  Creates a new Settings page on Gravity Forms' settings screen
            if(self::has_access("gravityforms_acton")){
                RGForms::add_settings_page("ActOn", array("GFActOn", "settings_page"), self::get_base_url() . "/images/acton_wordpress_icon_32.png");
            }
        }

        //  Creates the subnav left menu
        add_filter("gform_addon_navigation", array('GFActOn', 'create_menu'));

        if(self::is_acton_page()){

            //  Enqueueing sack for AJAX requests
            wp_enqueue_script(array("sack"));

            //  Loading data lib
            require_once(self::get_base_path() . "/data.php");

            //  Loading Gravity Forms tooltips
            require_once(GFCommon::get_base_path() . "/tooltips.php");
            add_filter('gform_tooltips', array('GFActOn', 'tooltips'));

            //  Runs the setup when version changes
            self::setup();

         }
         else if(in_array(RG_CURRENT_PAGE, array("admin-ajax.php"))){

            //  Loading data class
            require_once(self::get_base_path() . "/data.php");

            add_action('wp_ajax_rg_update_feed_active', array('GFActOn', 'update_feed_active'));
            add_action('wp_ajax_gf_select_acton_form', array('GFActOn', 'select_acton_form'));

        }
        else{
             // Handling post submission.
            add_action("gform_after_submission", array('GFActOn', 'export'), 10, 2);

        }
    }

    /**
     *
     * Function: update_feed_active
     * 
     */
    public static function update_feed_active(){
        check_ajax_referer('rg_update_feed_active','rg_update_feed_active');
        $id = $_POST["feed_id"];
        $feed = GFActOnData::get_feed($id);
        GFActOnData::update_feed($id, $feed["form_id"], $_POST["is_active"], $feed["meta"]);
    }

    /**
     *
     * Function: plugin_row
     * 
     */
    public static function plugin_row(){
        if(!class_exists("RGActOnUpgrade"))
            require_once("plugin-upgrade.php");
            
        if(!self::is_gravityforms_supported()){
            $message = sprintf(__("Gravity Forms " . self::$min_gravityforms_version . " is required. Activate it now or %spurchase it today!%s"), "<a href='http://www.gravityforms.com'>", "</a>");
            RGActOnUpgrade::display_plugin_message($message, true);
        }
        else{
            $version_info = RGActOnUpgrade::get_version_info(self::$slug, self::get_key(), self::$version);

            if(!$version_info["is_valid_key"]){
                $new_version = version_compare(self::$version, $version_info["version"], '<') ? __('There is a new version of Gravity Forms ActOn Add-On available.', 'gravityformsacton') .' <a class="thickbox" title="Gravity Forms ActOn Add-On" href="plugin-install.php?tab=plugin-information&plugin=' . self::$slug . '&TB_iframe=true&width=640&height=808">'. sprintf(__('View version %s Details', 'gravityformsacton'), $version_info["version"]) . '</a>. ' : '';
                $message = $new_version . sprintf(__('%sRegister%s your copy of Gravity Forms to receive access to automatic upgrades and support. Need a license key? %sPurchase one now%s.', 'gravityformsacton'), '<a href="admin.php?page=gf_settings">', '</a>', '<a href="http://www.gravityforms.com">', '</a>') . '</div></td>';
                RGActOnUpgrade::display_plugin_message($message);
            }
        }
    }

    /**
     *
     * Function: check_update
     * 
     */
    public static function check_update($update_plugins_option){
        if(!class_exists("RGActOnUpgrade"))
            require_once("plugin-upgrade.php");

        return RGActOnUpgrade::check_update(self::$path, self::$slug, self::$url, self::$slug, self::get_key(), self::$version, $update_plugins_option);
    }

    /**
     *
     * Function: get_key
     * 
     */
    private static function get_key(){
        if(self::is_gravityforms_supported())
            return GFCommon::get_key();
        else
            return "";
    }
    
    /**
     *
     * Function: is_acton_page
     * 
     */
    private static function is_acton_page(){
        $current_page = trim(strtolower(rgget("page")));
        $acton_pages = array("gf_acton");

        return in_array($current_page, $acton_pages);
    }

    /**
     *
     * Function: setup
     * 
     */
    private static function setup(){

        if(get_option("gf_acton_version") != self::$version)
            GFActOnData::update_table();

        update_option("gf_acton_version", self::$version);
    }

    /**
     *
     * Function: tooltips
     * 
     */
     public static function tooltips($tooltips){
        $acton_tooltips = array(
            "acton_contact_list" => "<h6>" . __("ActOn List", "gravityformsacton") . "</h6>" . __("Select the Act-On list you would like to add your contacts to.", "gravityformsacton"),
            "acton_gravity_form" => "<h6>" . __("Gravity Form", "gravityformsacton") . "</h6>" . __("Select the Gravity Form you would like to integrate with ActOn. Contacts generated by this form will be automatically added to your ActOn account.", "gravityformsacton"),
            "acton_welcome" => "<h6>" . __("Send Welcome Email", "gravityformsacton") . "</h6>" . __("When this option is enabled, users will receive an automatic welcome email from ActOn upon being added to your Act-On list.", "gravityformsacton"),
            "acton_map_fields" => "<h6>" . __("Map Fields", "gravityformsacton") . "</h6>" . __("Associate your Act-On merge variables to the appropriate Gravity Form fields by selecting.", "gravityformsacton"),
            "acton_optin_condition" => "<h6>" . __("Opt-In Condition", "gravityformsacton") . "</h6>" . __("When the opt-in condition is enabled, form submissions will only be exported to ActOn when the condition is met. When disabled all form submissions will be exported.", "gravityformsacton"),
            "acton_double_optin" => "<h6>" . __("Double Opt-In", "gravityformsacton") . "</h6>" . __("When the double opt-in option is enabled, Act-On will send a confirmation email to the user and will only add them to your Act-On list upon confirmation.", "gravityformsacton")
        );
        return array_merge($tooltips, $acton_tooltips);
    }

    /**
     *
     * Function: create_menu
     * 
     */
    public static function create_menu($menus){

        // Adding submenu if user has access
        $permission = self::has_access("gravityforms_acton");
        if(!empty($permission))
            $menus[] = array("name" => "gf_acton", "label" => __("Act-On Integration", "gravityformsacton"), "callback" =>  array("GFActOn", "acton_page"), "permission" => $permission);

        return $menus;
    }

    /**
     *
     * Function: settings_page
     * 
     */
    public static function settings_page(){

        if(!class_exists("RGActOnUpgrade"))
            require_once("plugin-upgrade.php");

        if(rgpost("uninstall")){
            check_admin_referer("uninstall", "gf_acton_uninstall");
            self::uninstall();

            ?>
            <div class="updated fade" style="padding:20px;"><?php _e(sprintf("Gravity Forms Act-On Add-On have been successfully uninstalled. It can be re-activated from the %splugins page%s.", "<a href='plugins.php'>","</a>"), "gravityformsacton")?></div>
            <?php
            return;
        }
        else if(rgpost("gf_acton_submit")){
            check_admin_referer("update", "gf_acton_update");
           
            $settings = array(  "ao_a"          => stripslashes($_POST["gf_ao_a"]), 
                                "form_url"      => stripslashes($_POST["gf_form_url"]), 
                                "username"      => stripslashes($_POST["gf_acton_username"]), 
                                "password"      => stripslashes($_POST["gf_acton_password"]), 
                                "client_id"     => stripslashes($_POST["gf_acton_client_id"]),
                                "client_secret" => stripslashes($_POST["gf_acton_client_secret"]) );
            
            update_option("gf_acton_settings", $settings);
        }
        else{
            $settings = get_option("gf_acton_settings");
        }

        $feedback_image = "";
        $is_valid_client_id = false;
        
        $is_valid_client_id = self::is_valid_login($settings);
        $icon = $is_valid_client_id ? self::get_base_url() . "/images/tick.png" : self::get_base_url() . "/images/stop.png";
        $feedback_image = "<img src='{$icon}' />";


        ?>
        <style>
            .valid_credentials{color:green;}
            .invalid_credentials{color:red;}
        </style>

        <form method="post" action="">
            <?php wp_nonce_field("update", "gf_acton_update") ?>
            <h3><?php _e("Act-On Account Information", "gravityformsacton") ?></h3>
            <p style="text-align: left;">
            </p>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="gf_ao_a"><?php _e("ActOn AO_A Code", "gravityformsacton"); ?></label> </th>
                    <td>
                        <input type="text" id="gf_ao_a" name="gf_ao_a" value="<?php echo empty($settings["ao_a"]) ? "" : esc_attr($settings["ao_a"]) ?>" size="50"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="gf_form_url"><?php _e("ActOn Form URL", "gravityformsacton"); ?></label> </th>
                    <td>
                        <input type="text" id="gf_form_url" name="gf_form_url" value="<?php echo empty($settings["form_url"]) ? "" : esc_attr($settings["form_url"]) ?>" size="50"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="gf_acton_username"><?php _e("ActOn Username", "gravityformsacton"); ?></label> </th>
                    <td>
                        <input type="text" id="gf_acton_username" name="gf_acton_username" value="<?php echo empty($settings["username"]) ? "" : esc_attr($settings["username"]) ?>" size="50"/>
                        <?php echo $feedback_image?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="gf_acton_password"><?php _e("ActOn Password", "gravityformsacton"); ?></label> </th>
                    <td>
                        <input type="password" id="gf_acton_password" name="gf_acton_password" value="<?php echo empty($settings["password"]) ? "" : esc_attr($settings["password"]) ?>" size="50"/>
                        <?php echo $feedback_image?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="gf_acton_client_id"><?php _e("ActOn Client ID", "gravityformsacton"); ?></label> </th>
                    <td>
                        <input type="password" id="gf_acton_client_id" name="gf_acton_client_id" value="<?php echo empty($settings["client_id"]) ? "" : esc_attr($settings["client_id"]) ?>" size="50"/>
                        <?php echo $feedback_image?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="gf_acton_client_secret"><?php _e("ActOn Client Secret", "gravityformsacton"); ?></label> </th>
                    <td>
                        <input type="password" id="gf_acton_client_secret" name="gf_acton_client_secret" value="<?php echo empty($settings["client_secret"]) ? "" : esc_attr($settings["client_secret"]) ?>" size="50"/>
                        <?php echo $feedback_image?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" ><input type="submit" name="gf_acton_submit" class="button-primary" value="<?php _e("Save Settings", "gravityformsacton") ?>" /></td>
                </tr>
            </table>
        </form>

        <form action="" method="post">
            <?php wp_nonce_field("uninstall", "gf_acton_uninstall") ?>
            <?php if(GFCommon::current_user_can_any("gravityforms_acton_uninstall")){ ?>
                <div class="hr-divider"></div>

                <h3><?php _e("Uninstall Act-On Add-On", "gravityformsacton") ?></h3>
                <div class="delete-alert"><?php _e("Warning! This operation deletes ALL Act-On Feeds.", "gravityformsacton") ?>
                    <?php
                    $uninstall_button = '<input type="submit" name="uninstall" value="' . __("Uninstall Act-On Add-On", "gravityformsacton") . '" class="button" onclick="return confirm(\'' . __("Warning! ALL Act-On Feeds will be deleted. This cannot be undone. \'OK\' to delete, \'Cancel\' to stop", "gravityformsacton") . '\');"/>';
                    echo apply_filters("gform_acton_uninstall_button", $uninstall_button);
                    ?>
                </div>
            <?php } ?>
        </form>
        <br/><br/>
        <hr/>
        <br/>
        <?php

        ?>
        <p>
            <strong><?php _e( 'Version:', 'gravityformsacton' ); ?></strong> <?php print self::$version; ?>
        </p>
        <p>
            For more information and support details, please view our <a href="https://www.newfangled.com/plugin-support-policy" target="_blank">plugin support policy</a>. 
        </p>
        <p>
            &copy; <?php date('Y') ?> <a href="https://www.newfangled.com" target="_blank">Newfangled.com</a>.
        </p>
        <?php

    }

    /**
     *
     * Function: acton_page
     * 
     */
    public static function acton_page(){
        $view = rgar($_GET,"view");
        if($view == "edit")
            self::edit_page($_GET["id"]);
        else
            self::list_page();
    }

    /**
     *
     * Function: list_page
     * 
     */
    private static function list_page(){
        if(!self::is_gravityforms_supported()){
            die(__(sprintf("Act-On Add-On requires Gravity Forms %s. Upgrade automatically on the %sPlugin page%s.", self::$min_gravityforms_version, "<a href='plugins.php'>", "</a>"), "gravityformsacton"));
        }

        if(rgpost("action") == "delete"){
            check_admin_referer("list_action", "gf_acton_list");

            $id = absint($_POST["action_argument"]);
            GFActOnData::delete_feed($id);
            ?>
            <div class="updated fade" style="padding:6px"><?php _e("Feed deleted.", "gravityformsacton") ?></div>
            <?php
        }
        else if (!empty($_POST["bulk_action"])){
            check_admin_referer("list_action", "gf_acton_list");
            $selected_feeds = $_POST["feed"];
            if(is_array($selected_feeds)){
                foreach($selected_feeds as $feed_id)
                    GFActOnData::delete_feed($feed_id);
            }
            ?>
            <div class="updated fade" style="padding:6px"><?php _e("Feeds deleted.", "gravityformsacton") ?></div>
            <?php
        }

        ?>
        <div class="wrap">
            <h2><?php _e("Act-On Feeds", "gravityformsacton"); ?>
            <a class="button add-new-h2" href="admin.php?page=gf_acton&view=edit&id=0"><?php _e("Add New", "gravityformsacton") ?></a>
            </h2>


            <form id="feed_form" method="post">
                <?php wp_nonce_field('list_action', 'gf_acton_list') ?>
                <input type="hidden" id="action" name="action"/>
                <input type="hidden" id="action_argument" name="action_argument"/>

                <div class="tablenav">
                    <div class="alignleft actions" style="padding:8px 0 7px 0;">
                        <label class="hidden" for="bulk_action"><?php _e("Bulk action", "gravityformsacton") ?></label>
                        <select name="bulk_action" id="bulk_action">
                            <option value=''> <?php _e("Bulk action", "gravityformsacton") ?> </option>
                            <option value='delete'><?php _e("Delete", "gravityformsacton") ?></option>
                        </select>
                        <?php
                        echo '<input type="submit" class="button" value="' . __("Apply", "gravityformsacton") . '" onclick="if( jQuery(\'#bulk_action\').val() == \'delete\' && !confirm(\'' . __("Delete selected feeds? ", "gravityformsacton") . __("\'Cancel\' to stop, \'OK\' to delete.", "gravityformsacton") .'\')) { return false; } return true;"/>';
                        ?>
                    </div>
                </div>
                <table class="widefat fixed" cellspacing="0">
                    <thead>
                        <tr>
                            <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
                            <th scope="col" id="active" class="manage-column check-column"></th>
                            <th scope="col" class="manage-column"><?php _e("Site Form", "gravityformsacton") ?></th>
                            <th scope="col" class="manage-column"><?php _e("Act-On Form", "gravityformsacton") ?></th>
                        </tr>
                    </thead>

                    <tfoot>
                        <tr>
                            <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
                            <th scope="col" id="active" class="manage-column check-column"></th>
                            <th scope="col" class="manage-column"><?php _e("Site Form", "gravityformsacton") ?></th>
                            <th scope="col" class="manage-column"><?php _e("Act-On Form", "gravityformsacton") ?></th>
                        </tr>
                    </tfoot>

                    <tbody class="list:user user-list">
                        <?php

                        $settings = GFActOnData::get_feeds();
                        if(is_array($settings) && sizeof($settings) > 0){
                            foreach($settings as $setting){
                                ?>
                                <tr class='author-self status-inherit' valign="top">
                                    <th scope="row" class="check-column"><input type="checkbox" name="feed[]" value="<?php echo $setting["id"] ?>"/></th>
                                    <td><img src="<?php echo self::get_base_url() ?>/images/active<?php echo intval($setting["is_active"]) ?>.png" alt="<?php echo $setting["is_active"] ? __("Active", "gravityformsacton") : __("Inactive", "gravityformsacton");?>" title="<?php echo $setting["is_active"] ? __("Active", "gravityformsacton") : __("Inactive", "gravityformsacton");?>" onclick="ToggleActive(this, <?php echo $setting['id'] ?>); " /></td>
                                    <td class="column-title">
                                        <a href="admin.php?page=gf_acton&view=edit&id=<?php echo $setting["id"] ?>" title="<?php _e("Edit", "gravityformsacton") ?>"><?php echo $setting["form_title"] ?></a>
                                        <div class="row-actions">
                                            <span class="edit">
                                            <a href="admin.php?page=gf_acton&view=edit&id=<?php echo $setting["id"] ?>" title="<?php _e("Edit", "gravityformsacton") ?>"><?php _e("Edit", "gravityformsacton") ?></a>
                                            |
                                            </span>

                                            <span class="trash">
                                            <a title="<?php _e("Delete", "gravityformsacton") ?>" href="javascript: if(confirm('<?php _e("Delete this feed? ", "gravityformsacton") ?> <?php _e("\'Cancel\' to stop, \'OK\' to delete.", "gravityformsacton") ?>')){ DeleteSetting(<?php echo $setting["id"] ?>);}"><?php _e("Delete", "gravityformsacton")?></a>

                                            </span>
                                        </div>
                                    </td>
                                    <td class="column-date"><?php echo $setting["meta"]["contact_list_name"] ?></td>
                                </tr>
                                <?php
                            }
                        }
                        else if(self::get_api()){
                            ?>
                            <tr>
                                <td colspan="4" style="padding:20px;">
                                    <?php _e(sprintf("You don't have any Act-On feeds configured. Let's go %screate one%s!", '<a href="admin.php?page=gf_acton&view=edit&id=0">', "</a>"), "gravityformsacton"); ?>
                                </td>
                            </tr>
                            <?php
                        }
                        else{
                            ?>
                            <tr>
                                <td colspan="4" style="padding:20px;">
                                    <?php _e(sprintf("To get started, please configure your %sAct-On Settings%s.", '<a href="admin.php?page=gf_settings&addon=ActOn">', "</a>"), "gravityformsacton"); ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </form>
        </div>
        <script type="text/javascript">
            function DeleteSetting(id){
                jQuery("#action_argument").val(id);
                jQuery("#action").val("delete");
                jQuery("#feed_form")[0].submit();
            }
            function ToggleActive(img, feed_id){
                var is_active = img.src.indexOf("active1.png") >=0
                if(is_active){
                    img.src = img.src.replace("active1.png", "active0.png");
                    jQuery(img).attr('title','<?php _e("Inactive", "gravityformsacton") ?>').attr('alt', '<?php _e("Inactive", "gravityformsacton") ?>');
                }
                else{
                    img.src = img.src.replace("active0.png", "active1.png");
                    jQuery(img).attr('title','<?php _e("Active", "gravityformsacton") ?>').attr('alt', '<?php _e("Active", "gravityformsacton") ?>');
                }

                var mysack = new sack(ajaxurl);
                mysack.execute = 1;
                mysack.method = 'POST';
                mysack.setVar( "action", "rg_update_feed_active" );
                mysack.setVar( "rg_update_feed_active", "<?php echo wp_create_nonce("rg_update_feed_active") ?>" );
                mysack.setVar( "feed_id", feed_id );
                mysack.setVar( "is_active", is_active ? 0 : 1 );
                mysack.encVar( "cookie", document.cookie, false );
                mysack.onError = function() { alert('<?php _e("Ajax error while updating feed", "gravityformsacton" ) ?>' )};
                mysack.runAJAX();

                return true;
            }
        </script>
        <?php
    }

    /**
     *
     * Function: is_valid_login
     * 
     */
    private static function is_valid_login( $settings ){

        if (!trim($settings['username']) || 
            !trim($settings['password']) || 
            !trim($settings['client_id']) ||
            !trim($settings['client_secret']))
        {
            return false;
        }
        if(!class_exists("AOAPI")){
            require_once("api/AOAPI.class.php");
        }

        $api = new AOAPI(   $settings['username'], 
                            $settings['password'], 
                            $settings['client_id'], 
                            $settings['client_secret']);
        
        if ($api->errorCode)
        {            
            self::log_error("Login valid: false. Error " . $api->errorCode . " - " . $api->errorMessage);
        }
        else
        {
            self::log_debug("Login valid: true");
        }

        return $api->errorCode ? false : true;
    }

    /**
     *
     * Function: get_api
     * 
     */
    private static function get_api(){

        //global acton settings
        $settings = get_option("gf_acton_settings");
        $api      = null;

        if (!trim($settings['username']) || 
            !trim($settings['password']) || 
            !trim($settings['client_id']) ||
            !trim($settings['client_secret']))
        {
            return $api;
        }
        if(!class_exists("AOAPI")){
            require_once("api/AOAPI.class.php");
        }

        $api = new AOAPI(   $settings['username'], 
                            $settings['password'], 
                            $settings['client_id'], 
                            $settings['client_secret']);
        
        if ($api->errorCode)
        {
            self::log_error("Login valid: false. Error " . $api->errorCode . " - " . $api->errorMessage);
        }
        else
        {
            self::log_debug("Login valid: true");
        }

        return $api;
    }

    /**
     *
     * Function: edit_page
     * 
     */
    private static function edit_page(){

        global $_POST;

        ?>
        <style>
            .acton_col_heading{padding-bottom:2px; border-bottom: 1px solid #ccc; font-weight:bold;}
            .acton_field_cell {padding: 6px 17px 0 0; margin-right:15px;}
            .gfield_required{color:red;}

            .feeds_validation_error{ background-color:#FFDFDF;}
            .feeds_validation_error td{ margin-top:4px; margin-bottom:6px; padding-top:6px; padding-bottom:6px; border-top:1px dotted #C89797; border-bottom:1px dotted #C89797}

            .left_header{float:left; width:200px;}
            .margin_vertical_10{margin: 10px 0;}
            #acton_doubleoptin_warning{padding-left: 5px; padding-bottom:4px; font-size: 10px;}
            .acton_group_condition{padding-bottom:6px; padding-left:20px;}
        </style>
        <script type="text/javascript">
            var form = Array();
        </script>
        <div class="wrap">
            <h2><?php _e("Act-On Feed", "gravityformsacton") ?></h2>

        <?php
        //getting ActOn API
        $api = self::get_api();

        //ensures valid credentials were entered in the settings page
        if(!$api){
            ?>
            <div><?php echo sprintf(__("We are unable to login to Act-On with the provided credentials. Please make sure they are valid in the %sSettings Page%s", "gravityformsacton"), "<a href='?page=gf_settings&addon=ActOn'>", "</a>"); ?></div>
            <?php
            return;
        }

        //getting setting id (0 when creating a new one)
        $id = !empty($_POST["acton_setting_id"]) ? $_POST["acton_setting_id"] : absint($_GET["id"]);
        $config = empty($id) ? array("meta" => array("double_optin" => true), "is_active" => true) : GFActOnData::get_feed($id);

        if(!isset($config["meta"]))
            $config["meta"] = array();

        //getting merge vars from selected list (if one was selected)
        if (rgempty("contact_list_id", $config["meta"]))
        {
            $merge_vars = array();
        }
        else
        {
            $merge_vars = $api->getFormFields($config["meta"]["contact_list_id"]);
        }

        //updating meta information
        if(rgpost("gf_acton_submit")){

            list($list_id, $list_name) = explode("|:|", stripslashes($_POST["gf_acton_remoteform"]));
            $config["meta"]["contact_list_id"] = $list_id;
            $config["meta"]["contact_list_name"] = $list_name;
            $config["form_id"] = absint($_POST["gf_gravity_form"]);

            $is_valid = true;
            $merge_vars = $api->getFormFields($config["meta"]["contact_list_id"]);

            $field_map = array();
            foreach($merge_vars as $var){
                $field_name = "acton_map_field_" . str_replace( ' ', '_', $var["tag"] );
                
                $mapped_field = stripslashes($_POST[$field_name]);
                if(!empty($mapped_field)){
                    $field_map[$var["tag"]] = $mapped_field;
                }
                else{
                    unset($field_map[$var["tag"]]);
                    if($var["req"] == "Y")
                    $is_valid = false;
                }
            }

            $config["meta"]["field_map"] = $field_map;
           
            if($is_valid){

                $id = GFActOnData::update_feed($id, $config["form_id"], $config["is_active"], $config["meta"]);
                ?>
                <div class="updated fade" style="padding:6px"><?php echo sprintf(__("Feed Updated. %sback to list%s", "gravityformsacton"), "<a href='?page=gf_acton'>", "</a>") ?></div>
                <input type="hidden" name="acton_setting_id" value="<?php echo $id ?>"/>
                <?php
            }
            else{
                ?>
                <div class="error" style="padding:6px"><?php echo __("Feed could not be updated. Please enter all required information below.", "gravityformsacton") ?></div>
                <?php
            }
        }



        ?>
        <form method="post" action="">
            <input type="hidden" name="acton_setting_id" value="<?php echo $id ?>"/>
            <div class="margin_vertical_10">
                <label for="gf_acton_remoteform" class="left_header"><?php _e("Act-On Form", "gravityformsacton"); ?> </label>
                <?php

                //global acton settings
                $settings = get_option("gf_acton_settings");
                $api_key = $settings["client_id"];

                //getting all contact lists
                $acton_forms = $api->getForms();

                
                
                if (!$acton_forms){
                    echo __("Could not load Act-On forms. <br/>Error: ", "gravityformsacton") . $api->errorMessage;
                }
                else{
                    ?>
                    <select id="gf_acton_remoteform" name="gf_acton_remoteform" onchange="SelectList(jQuery(this).val());">
                        <option value=""><?php _e("Select a Act-On Form", "gravityformsacton"); ?></option>
                    <?php
                    foreach ($acton_forms as $folder){

                        if ($folder['entries'])
                        {
                            foreach( $folder['entries'] as $acton_form )
                            {
                                $selected = $acton_form["id"] == $config["meta"]["contact_list_id"] ? "selected='selected'" : "";
                                 ?>
                                <option value="<?php echo esc_attr($acton_form['id']) . "|:|" . esc_attr($acton_form['title']) ?>" <?php echo $selected ?>><?php echo esc_html($acton_form['title']) ?></option>
                                <?php
                            }
                        }
                    }
                    ?>
                  </select>
                <?php
                }
                ?>
            </div>

            <div id="acton_form_container" valign="top" class="margin_vertical_10" <?php echo empty($config["meta"]["contact_list_id"]) ? "style='display:none;'" : "" ?>>
                <label for="gf_acton_form" class="left_header"><?php _e("Site Form", "gravityformsacton"); ?></label>

                <select id="gf_acton_form" name="gf_gravity_form" onchange="SelectForm(jQuery('#gf_acton_remoteform').val(), jQuery(this).val());">
                <option value=""><?php _e("Select a form", "gravityformsacton"); ?> </option>
                <?php
                $forms = RGFormsModel::get_forms();
                foreach($forms as $form){
                    $selected = absint($form->id) == rgar($config,"form_id") ? "selected='selected'" : "";
                    ?>
                    <option value="<?php echo absint($form->id) ?>"  <?php echo $selected ?>><?php echo esc_html($form->title) ?></option>
                    <?php
                }
                ?>
                </select>
                &nbsp;&nbsp;
                <img src="<?php echo GFActOn::get_base_url() ?>/images/loading.gif" id="acton_wait" style="display: none;"/>
            </div>
            <div id="acton_field_group" valign="top" <?php echo empty($config["meta"]["contact_list_id"]) || empty($config["form_id"]) ? "style='display:none;'" : "" ?>>
                <div id="acton_field_container" valign="top" class="margin_vertical_10" >
                    <label for="acton_fields" class="left_header"><?php _e("Map Fields", "gravityformsacton"); ?> </label>
                    <?php

                    
?>
                    <div id="acton_field_list">
                    <?php
                    if(!empty($config["form_id"])){

                        //getting list of all ActOn merge variables for the selected contact list
                        if(empty($merge_vars))
                        {
                            $merge_vars = $api->getFormFields($config["meta"]["contact_list_id"]);
                        }
                        //getting field map UI
                        echo self::get_field_mapping($config, $config["form_id"], $merge_vars);

                        //getting list of selection fields to be used by the optin
                        $form_meta = RGFormsModel::get_form_meta($config["form_id"]);
                    }
                    ?>
                    </div>
                </div>
                <div id="acton_submit_container" class="margin_vertical_10">
                    <input type="submit" name="gf_acton_submit" value="<?php echo empty($id) ? __("Save", "gravityformsacton") : __("Update", "gravityformsacton"); ?>" class="button-primary"/>
                    <input type="button" value="<?php _e("Cancel", "gravityformsacton"); ?>" class="button" onclick="javascript:document.location='admin.php?page=gf_acton'" />
                </div>
            </div>
        </form>
        </div>
        <script type="text/javascript">

            function SelectList(listId){
                if(listId){
                    jQuery("#acton_form_container").slideDown();
                    jQuery("#gf_acton_form").val("");
                }
                else{
                    jQuery("#acton_form_container").slideUp();
                    EndSelectForm("");
                }
            }

            function SelectForm(listId, formId){
                if(!formId){
                    jQuery("#acton_field_group").slideUp();
                    return;
                }

                jQuery("#acton_wait").show();
                jQuery("#acton_field_group").slideUp();

                var mysack = new sack(ajaxurl);
                mysack.execute = 1;
                mysack.method = 'POST';
                mysack.setVar( "action", "gf_select_acton_form" );
                mysack.setVar( "gf_select_acton_form", "<?php echo wp_create_nonce("gf_select_acton_form") ?>" );
                mysack.setVar( "list_id", listId);
                mysack.setVar( "form_id", formId);
                mysack.encVar( "cookie", document.cookie, false );
                mysack.onError = function() {jQuery("#acton_wait").hide(); alert('<?php _e("Ajax error while selecting a form", "gravityformsacton") ?>' )};
                mysack.runAJAX();

                return true;
            }

            function SetOptin(selectedField, selectedValue){

                //load form fields
                jQuery("#acton_optin_field_id").html(GetSelectableFields(selectedField, 20));
                var optinConditionField = jQuery("#acton_optin_field_id").val();

                if(optinConditionField){
                    jQuery("#acton_optin_condition_message").hide();
                    jQuery("#acton_optin_condition_fields").show();
                    jQuery("#acton_optin_value_container").html(GetFieldValues(optinConditionField, selectedValue, 20));
                    jQuery("#acton_optin_value").val(selectedValue);
                }
                else{
                    jQuery("#acton_optin_condition_message").show();
                    jQuery("#acton_optin_condition_fields").hide();
                }
            }

            function SetGroupCondition(groupingName, groupname, selectedField, selectedValue){

                //load form fields
                jQuery("#acton_group_"+groupingName+"_"+groupname+"_field_id").html(GetSelectableFields(selectedField, 20));
                var groupConditionField = jQuery("#acton_group_"+groupingName+"_"+groupname+"_field_id").val();

                if(groupConditionField){
                    jQuery("#acton_group_"+groupingName+"_"+groupname+"_condition_message").hide();
                    jQuery("#acton_group_"+groupingName+"_"+groupname+"_condition_fields").show();
                    jQuery("#acton_group_"+groupingName+"_"+groupname+"_container").html(GetFieldValues(groupConditionField, selectedValue, 20, "acton_group_" + groupingName + "_" + groupname + "_value"));
                }
                else{
                    jQuery("#acton_group_"+groupingName+"_"+groupname+"_condition_message").show();
                    jQuery("#acton_group_"+groupingName+"_"+groupname+"_condition_fields").hide();
                }
            }


            function EndSelectForm(fieldList, form_meta, grouping, groups){
                //setting global form object
                form = form_meta;
                if(fieldList){

                    SetOptin("","");

                    jQuery("#acton_field_list").html(fieldList);
                    jQuery("#acton_groupings").html(grouping);

                    for(var i in groups)
                        SetGroupCondition(groups[i]["main"], groups[i]["sub"],"","");

                        jQuery( '.tooltip_acton_groups' ).tooltip({
                            show: 500,
                            hide: 1000,
                            content: function () {
                                return jQuery(this).prop('title');
                            }
                        });

                    jQuery("#acton_field_group").slideDown();

                }
                else{
                    jQuery("#acton_field_group").slideUp();
                    jQuery("#acton_field_list").html("");
                }
                jQuery("#acton_wait").hide();
            }

            function GetFieldValues(fieldId, selectedValue, labelMaxCharacters, inputName){
                if(!inputName){
                    inputName = 'acton_optin_value';
                }

                if(!fieldId)
                    return "";

                var str = "";
                var field = GetFieldById(fieldId);
                if(!field)
                    return "";

                var isAnySelected = false;

                if(field["type"] == "post_category" && field["displayAllCategories"]){
                    str += '<?php $dd = wp_dropdown_categories(array("class"=>"optin_select", "orderby"=> "name", "id"=> "acton_optin_value", "name"=> "acton_optin_value", "hierarchical"=>true, "hide_empty"=>0, "echo"=>false)); echo str_replace("\n","", str_replace("'","\\'",$dd)); ?>';
                }
                else if(field.choices){
                    str += '<select id="' + inputName +'" name="' + inputName +'" class="optin_select">';

                    for(var i=0; i<field.choices.length; i++){
                        var fieldValue = field.choices[i].value ? field.choices[i].value : field.choices[i].text;
                        var isSelected = fieldValue == selectedValue;
                        var selected = isSelected ? "selected='selected'" : "";
                        if(isSelected)
                            isAnySelected = true;

                        str += "<option value='" + fieldValue.replace(/'/g, "&#039;") + "' " + selected + ">" + TruncateMiddle(field.choices[i].text, labelMaxCharacters) + "</option>";
                    }

                    if(!isAnySelected && selectedValue){
                        str += "<option value='" + selectedValue.replace(/'/g, "&#039;") + "' selected='selected'>" + TruncateMiddle(selectedValue, labelMaxCharacters) + "</option>";
                    }
                    str += "</select>";
                }
                else
                {
                    selectedValue = selectedValue ? selectedValue.replace(/'/g, "&#039;") : "";
                    //create a text field for fields that don't have choices (i.e text, textarea, number, email, etc...)
                    str += "<input type='text' placeholder='<?php _e("Enter value", "gravityforms"); ?>' id='" + inputName + "' name='" + inputName +"' value='" + selectedValue.replace(/'/g, "&#039;") + "'>";
                }

                return str;
            }

            function GetFieldById(fieldId){
                for(var i=0; i<form.fields.length; i++){
                    if(form.fields[i].id == fieldId)
                        return form.fields[i];
                }
                return null;
            }

            function TruncateMiddle(text, maxCharacters){
                if(text.length <= maxCharacters)
                    return text;
                var middle = parseInt(maxCharacters / 2);
                return text.substr(0, middle) + "..." + text.substr(text.length - middle, middle);
            }

            function GetSelectableFields(selectedFieldId, labelMaxCharacters){
                var str = "";
                var inputType;

                for(var i=0; i<form.fields.length; i++){
                    fieldLabel = form.fields[i].adminLabel ? form.fields[i].adminLabel : form.fields[i].label;
                    inputType = form.fields[i].inputType ? form.fields[i].inputType : form.fields[i].type;
                    if (IsConditionalLogicField(form.fields[i])) {
                        var selected = form.fields[i].id == selectedFieldId ? "selected='selected'" : "";
                        str += "<option value='" + form.fields[i].id + "' " + selected + ">" + TruncateMiddle(fieldLabel, labelMaxCharacters) + "</option>";
                    }
                }
                return str;
            }

            function IsConditionalLogicField(field){
                inputType = field.inputType ? field.inputType : field.type;
                var supported_fields = ["checkbox", "radio", "select", "text", "website", "textarea", "email", "hidden", "number", "phone", "multiselect", "post_title",
                                        "post_tags", "post_custom_field", "post_content", "post_excerpt"];

                var index = jQuery.inArray(inputType, supported_fields);

                return index >= 0;
            }

        </script>

        <?php

    }

    /**
     *
     * Function: add_permissions
     * 
     */
    public static function add_permissions(){
        global $wp_roles;
        $wp_roles->add_cap("administrator", "gravityforms_acton");
        $wp_roles->add_cap("administrator", "gravityforms_acton_uninstall");
    }

    /**
     *
     * Function: get_groupings
     * 
     */
    public static function get_groupings($config,$selected_list_id,$selection_fields,&$group_condition,&$group_names){
       
    }

    /**
     *
     * Function: selected
     * 
     */
    public static function selected($selected, $current){
        return $selected === $current ? " selected='selected'" : "";
    }

    /**
     *
     * Function: members_get_capabilities
     * 
     */
    public static function members_get_capabilities( $caps ) {
        return array_merge($caps, array("gravityforms_acton", "gravityforms_acton_uninstall"));
    }

    /**
     *
     * Function: disable_acton
     * 
     */
    public static function disable_acton(){
        delete_option("gf_acton_settings");
    }

    /**
     *
     * Function: select_acton_form
     * 
     */
    public static function select_acton_form(){

        check_ajax_referer("gf_select_acton_form", "gf_select_acton_form");
        $form_id =  intval(rgpost("form_id"));
        list($list_id, $list_name) =  explode("|:|", rgpost("list_id"));
        $setting_id =  intval(rgpost("setting_id"));

        $api = self::get_api();
        if(!$api)
            die("EndSelectForm();");

        //getting list of all ActOn merge variables for the selected contact list
        self::log_debug("Retrieving Merge_Vars for list {$list_id}");
        $merge_vars = $api->getFormFields($list_id);
        self::log_debug("Merge_Vars retrieved: " . print_r($merge_vars,true));

        //getting configuration
        $config = GFActOnData::get_feed($setting_id);

        //getting field map UI
        $str = self::get_field_mapping($config, $form_id, $merge_vars);

        //getting list of selection fields to be used by the optin
        $form_meta = RGFormsModel::get_form_meta($form_id);
        $selection_fields = GFCommon::get_selection_fields($form_meta, rgars($config, "meta/optin_field_id"));
        $group_condition = array();
        $group_names = array();
        $grouping = self::get_groupings($config,$list_id,$selection_fields,$group_condition,$group_names);

        //fields meta
        $form = RGFormsModel::get_form_meta($form_id);
        die("EndSelectForm('" . str_replace("'", "\'", $str) . "', " . GFCommon::json_encode($form) . ", '" . str_replace("'", "\'", $grouping) . "', " . json_encode($group_names) . " );");
    }

    /**
     *
     * Function: get_field_mapping
     * 
     */
    private static function get_field_mapping($config, $form_id, $merge_vars){

        //getting list of all fields for the selected form
        $form_fields = self::get_form_fields($form_id);

        $str = "<table cellpadding='0' cellspacing='10'><tr><td class='acton_col_heading'>" . __("Act-On Form Fields", "gravityformsacton") . "</td><td class='acton_col_heading'>" . __("Site Form Fields", "gravityformsacton") . "</td></tr>";
        if(!isset($config["meta"]))
            $config["meta"] = array("field_map" => "");

        foreach($merge_vars as $var){
            $selected_field = rgar($config["meta"]["field_map"], $var["tag"]);
            $required = $var["req"] == "Y" ? "<span class='gfield_required'>*</span>" : "";
            $error_class = $var["req"] == "Y" && empty($selected_field) && !empty($_POST["gf_acton_submit"]) ? " feeds_validation_error" : "";
            $str .= "<tr class='$error_class'><td class='acton_field_cell'>" . $var["name"]  . " $required</td><td class='acton_field_cell'>" . self::get_mapped_field_list($var["tag"], $selected_field, $form_fields) . "</td></tr>";
        }
        $str .= "</table>";

        return $str;
    }

    /**
     *
     * Function: get_form_fields
     * 
     */
    public static function get_form_fields($form_id){
        $form = RGFormsModel::get_form_meta($form_id);
        $fields = array();

        //Adding default fields
        array_push($form["fields"],array("id" => "date_created" , "label" => __("Entry Date", "gravityformsacton")));
        array_push($form["fields"],array("id" => "ip" , "label" => __("User IP", "gravityformsacton")));
        array_push($form["fields"],array("id" => "source_url" , "label" => __("Source Url", "gravityformsacton")));
        array_push($form["fields"],array("id" => "form_title" , "label" => __("Form Title", "gravityformsacton")));
        $form = self::get_entry_meta($form);
        if(is_array($form["fields"])){
            foreach($form["fields"] as $field){
                if(is_array(rgar($field, "inputs"))){

                    //If this is an address field, add full name to the list
                    if(RGFormsModel::get_input_type($field) == "address")
                        $fields[] =  array($field["id"], GFCommon::get_label($field) . " (" . __("Full" , "gravityformsacton") . ")");

                    //If this is a name field, add full name to the list
                    if(RGFormsModel::get_input_type($field) == "name")
                        $fields[] =  array($field["id"], GFCommon::get_label($field) . " (" . __("Full" , "gravityformsacton") . ")");

                    // foreach($field["inputs"] as $input)
                    //     $fields[] =  array($input["id"], GFCommon::get_label($field, $input["id"]));

                    $fields[] =  array($field["id"], GFCommon::get_label($field));
                }
                else if(!rgar($field,"displayOnly")){
                    $fields[] =  array($field["id"], GFCommon::get_label($field));
                }
            }
        }
        return $fields;
    }

    /**
     *
     * Function: get_entry_meta
     * 
     */
    private static function get_entry_meta($form){
        $entry_meta = GFFormsModel::get_entry_meta($form["id"]);
        $keys = array_keys($entry_meta);
        foreach ($keys as $key){
            array_push($form["fields"],array("id" => $key , "label" => $entry_meta[$key]['label']));
        }
        return $form;
    }

    /**
     *
     * Function: get_address
     * 
     */
    private static function get_address($entry, $field_id){
        $street_value = str_replace("  ", " ", trim($entry[$field_id . ".1"]));
        $street2_value = str_replace("  ", " ", trim($entry[$field_id . ".2"]));
        $city_value = str_replace("  ", " ", trim($entry[$field_id . ".3"]));
        $state_value = str_replace("  ", " ", trim($entry[$field_id . ".4"]));
        $zip_value = trim($entry[$field_id . ".5"]);
        $country_value = GFCommon::get_country_code(trim($entry[$field_id . ".6"]));

        $address = $street_value;
        $address .= !empty($address) && !empty($street2_value) ? "  $street2_value" : $street2_value;
        $address .= !empty($address) && (!empty($city_value) || !empty($state_value)) ? "  $city_value" : $city_value;
        $address .= !empty($address) && !empty($city_value) && !empty($state_value) ? "  $state_value" : $state_value;
        $address .= !empty($address) && !empty($zip_value) ? "  $zip_value" : $zip_value;
        $address .= !empty($address) && !empty($country_value) ? "  $country_value" : $country_value;

        return $address;
    }

    /**
     *
     * Function: get_name
     * 
     */
    private static function get_name($entry, $field_id){

        //If field is simple (one input), simply return full content
        $name = rgar($entry,$field_id);
        if(!empty($name))
            return $name;

        //Complex field (multiple inputs). Join all pieces and create name
        $prefix = trim(rgar($entry,$field_id . ".2"));
        $first = trim(rgar($entry,$field_id . ".3"));
        $last = trim(rgar($entry,$field_id . ".6"));
        $suffix = trim(rgar($entry,$field_id . ".8"));

        $name = $prefix;
        $name .= !empty($name) && !empty($first) ? " $first" : $first;
        $name .= !empty($name) && !empty($last) ? " $last" : $last;
        $name .= !empty($name) && !empty($suffix) ? " $suffix" : $suffix;
        return $name;
    }

    /**
     *
     * Function: get_mapped_field_list
     * 
     */
    public static function get_mapped_field_list($variable_name, $selected_field, $fields){
        $field_name = "acton_map_field_" . $variable_name;
        $str = "<select name='$field_name' id='$field_name'><option value=''></option>";
        foreach($fields as $field){
            $field_id = $field[0];
            $field_label = esc_html(GFCommon::truncate_middle($field[1], 40));

            $selected = $field_id == $selected_field ? "selected='selected'" : "";
            $str .= "<option value='" . $field_id . "' ". $selected . ">" . $field_label . "</option>";
        }
        $str .= "</select>";
        return $str;
    }

    /**
     *
     * Function: add_paypal_settings
     * 
     */
    public static function add_paypal_settings($config, $form) {
    }

    /**
     *
     * Function: save_paypal_settings
     * 
     */
    public static function save_paypal_settings($config) {
    }

    /**
     *
     * Function: paypal_fulfillment
     * 
     */
    public static function paypal_fulfillment($entry, $config, $transaction_id, $amount) {
    }

    /**
     *
     * Function: export
     * 
     */
    public static function export($entry, $form, $is_fulfilled = false){

        //loading data class
        require_once(self::get_base_path() . "/data.php");

        //getting all active feeds
        $feeds = GFActOnData::get_feed_by_form($form["id"], true);
        foreach($feeds as $feed){
            self::export_feed($entry, $form, $feed);
        }
    }

    /**
     *
     * Function: get_payment_amount
     * 
     */
    public static function get_payment_amount($form, $entry, $paypal_config){
    }

    /**
     *
     * Function: has_acton
     * 
     */
    public static function has_acton($form_id){
        if(!class_exists("GFActOnData"))
            require_once(self::get_base_path() . "/data.php");

        //Getting Mail Chimp settings associated with this form
        $config = GFActOnData::get_feed_by_form($form_id);

        if(!$config)
            return false;

        return true;
    }

    /**
     *
     * Function: get_paypal_config
     * 
     */
    private static function get_paypal_config($form_id, $entry){
        if(!class_exists('GFPayPal'))
            return false;

        if(method_exists("GFPayPal", "get_config_by_entry")){
            return GFPayPal::get_config_by_entry($entry);
        }
        else{
            return GFPayPal::get_config($form_id);
        }
    }

    /**
     *
     * Function: add_tracking_code
     * 
     */
    public static function add_tracking_code()
    {
        //return; 
        
        $settings = get_option("gf_acton_settings");

        $acton_post_url     = $settings['form_url'];
        $acton_ao_a         = $settings['ao_a'];

        if (!$acton_post_url ||
            !$acton_ao_a )
        {
            return;
        }

        $url_parts              = parse_url( $acton_post_url );
        $base_url_parts         = explode( '.', $url_parts['host'] );
        $cookiedomain                 = '.' . $base_url_parts[1] . '.' . $base_url_parts[2];

        ?>
        <script>/*<![CDATA[*/(function(w,a,b,d,s){w[a]=w[a]||{};w[a][b]=w[a][b]||{q:[],track:function(r,e,t){this.q.push({r:r,e:e,t:t||+new Date});}};var e=d.createElement(s);var f=d.getElementsByTagName(s)[0];e.async=1;e.src='<?php print $acton_post_url ?>/cdnr/30/acton/bn/tracker/<?php print $acton_ao_a ?>';f.parentNode.insertBefore(e,f);})(window,'ActOn','Beacon',document,'script');ActOn.Beacon.track();/*]]>*/</script>
        <?php
    }

    /**
     *
     * Function: nflog_error
     * 
     */
    function nflog_error($msg){

        global $nflogging;

        if (!is_object($nflogging)) {
            return;
        }

        if (!method_exists( $nflogging, 'logError')) {
            return;
        }

        $nflogging->logError( $msg );
    }
    
    /**
     *
     * Function: export_feed
     * 
     */
    public static function export_feed($entry, $form, $feed){

        $form = gf_apply_filters( array( 'gform_pre_render', $form_id ), $form, null, null );

        $settings = get_option("gf_acton_settings");
        
        $acton_post_fields  = Array();
        $acton_post_data    = '';
        $acton_post_url     = $settings['form_url'];
        $acton_ao_a         = $settings['ao_a'];

        $dynamic_acton_id = '';

        if ($form['fields'])
        {
            foreach( $form['fields'] as $field )
            {
                if ($field['label'] == 'ActOn Form ID' || 
                    $field['label'] == 'Act-On ID')
                {
                    if ( $actonid_field_id = $field['id'] )
                    {
                        if ( trim( $entry[ $actonid_field_id ] ))
                        {
                            $dynamic_acton_id = trim( $entry[ $actonid_field_id ] );
                        }
                    }
                }
            }
        }

        $acton_post_fields['ao_a'] = $acton_ao_a;

        if ($dynamic_acton_id)
        {
            $acton_post_fields['ao_f'] = $dynamic_acton_id;
            $acton_post_fields['ao_d'] = $dynamic_acton_id . ':d-0001';
        }

        else
        {
            $acton_post_fields['ao_f'] = $feed['meta']['contact_list_id'];
            $acton_post_fields['ao_d'] = $feed['meta']['contact_list_id'] . ':d-0001';
        }

        if ($field_map = $feed['meta']['field_map'])
        {
            foreach( $field_map as $acton_name => $field_id )
            {

                if (strpos($field_id, '.') !== FALSE && trim($entry[ $field_id ]))
                {
                    $acton_post_fields[ $acton_name ] = 1;
                }

                else if (array_key_exists( $field_id . '.1', $entry ))
                {
                    $this_value_items = Array();
                    $this_value = '';

                    foreach( $entry as $entry_field_key => $entry_field )
                    {
                        if ($entry_field_key_parts = explode( '.', $entry_field_key ))
                        {
                            if ($entry_field_key_parts[0] == $field_id)
                            {
                                if (trim($entry_field))
                                {
                                    $this_value_items[] = $entry_field;
                                }
                            }
                        }
                    }

                    $this_value = implode( ', ', $this_value_items );
                    $acton_post_fields[ $acton_name ] = $this_value;
                }

                else
                {
                    $acton_post_fields[ $acton_name ] = $entry[ $field_id ];
                }
            }
        }

        if ($acton_post_fields)
        {
            foreach( $acton_post_fields as $name => $value )
            {
                $acton_post_data   .= urlencode( stripslashes( $name ) ) . '=' . urlencode( stripslashes( $value ) ) . '&';
            }
        }

        if (!$acton_post_data ||
            !$acton_post_url ||
            !$acton_ao_a ||
            !$feed['meta']['contact_list_id'])
        {
            return $form;
        }

        // check the spam flag and axe it if need be
        if( isset( $entry['status'] ) && strtolower( $entry['status'] ) == 'spam' ) 
        {
        //    return $form;
        }

        $acton_post_url .= '/acton/forms/userSubmit.jsp';
        //$acton_post_url  = str_replace( 'http://', 'https://', $acton_post_url );

        //  Settings
        $cookiename     = 'wp' . $acton_ao_a;
        $remoteip       = $GLOBALS['_SERVER']['REMOTE_ADDR'];
        $curlcookies    = $cookiename."=".$_COOKIE[$cookiename].";";

        $user_agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5)".
            " Gecko/20041107 Firefox/1.0";

        //  Do the intial post
        $ch = curl_init();

        curl_setopt(    $ch,    CURLOPT_COOKIE,             $curlcookies );
        curl_setopt(    $ch,    CURLOPT_POST,               true );
        curl_setopt(    $ch,    CURLOPT_POSTFIELDS,         $acton_post_data );
        curl_setopt(    $ch,    CURLOPT_URL,                $acton_post_url );
        curl_setopt(    $ch,    CURLOPT_RETURNTRANSFER,     1 );
        curl_setopt(    $ch,    CURLOPT_HEADER,             1 );
        curl_setopt(    $ch,    CURLOPT_REFERER,            '' );
        curl_setopt(    $ch,    CURLINFO_HEADER_OUT,        1 );
        curl_setopt(    $ch,    CURLOPT_FOLLOWLOCATION,     false );
        curl_setopt(    $ch,    CURLOPT_HTTPHEADER,         array("REMOTE_ADDR: $remoteip", "HTTP_X_FORWARDED_FOR: $remoteip"));
        curl_setopt(    $ch,    CURLOPT_USERAGENT,          $user_agent );


        //  Execute the post        
        $result = curl_exec ($ch);
        $info = curl_getinfo($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        //  Was there a response
        if (!$result) {
            self::nflog_error( 'Error posting to Act-On - no response from Act-On' );
            return;
        }

        //  Was it a 200 response?
        if ($code != 200) {
            self::nflog_error( 'Error posting to Act-On - response code ' . $code );
            return;
        }

        //  Process and store all the response cookies
        $curlcookies = '';

        preg_match_all('/Set-Cookie: (.*)\b/', $result, $found_cookies);

        foreach( $found_cookies[1] as $found_cookie )
        {           
            $url_parts              = parse_url( $_SERVER['HTTP_HOST'] );
            $base_url_parts         = explode( '.', $url_parts['path'] );
            $domain                 = '.' . $base_url_parts[1] . '.' . $base_url_parts[2];
        
            $cookie_parts           = explode( ';', $found_cookie );
            $cookie_parts1          = $cookie_parts[0];
            $cookie_parts1_parts    = explode( '=', $cookie_parts1 );
                    
            $cookie_name            = trim($cookie_parts1_parts[0]);
            $cookie_value           = trim(urldecode( $cookie_parts1_parts[1] ));
                        
            setrawcookie($cookie_name, $cookie_value, (time() + 31536000), '/', $domain, FALSE);
            $curlcookies .= $cookie_name.'='.$cookie_value.';';
        }

        //  Look for a 302 redirect
        //  The returned headers
        $headers    = explode("\n",$result);
        
        //  If there is no redirection this will be the final url
        $redir      = $url;
        
        //  Loop through the headers and check for a Location: str
        $j          = count($headers);
        
        for($i = 0; $i < $j; $i++)
        {
            //  If we find the Location header strip it and fill the redir var       
            if(strpos($headers[$i],"Location:") !== false)
            {
                $redir = trim(str_replace("Location:","",$headers[$i]));
                break;
            }
        }

        //  Post the redirect to trigger the subsequent action
        if ($redir)
        {
            $ch = curl_init();

            curl_setopt(    $ch,    CURLOPT_COOKIE,             $curlcookies );
            curl_setopt(    $ch,    CURLOPT_URL,                $redir );
            curl_setopt(    $ch,    CURLOPT_RETURNTRANSFER,     true );
            curl_setopt(    $ch,    CURLOPT_FOLLOWLOCATION,     false );
            curl_setopt(    $ch,    CURLOPT_HEADER,             false );
            curl_setopt(    $ch,    CURLOPT_REFERER,            $acton_post_url );
            curl_setopt(    $ch,    CURLOPT_HTTPHEADER,         array("REMOTE_ADDR: $remoteip", "HTTP_X_FORWARDED_FOR: $remoteip"));
            curl_setopt(    $ch,    CURLOPT_USERAGENT,          $user_agent );

            //  Execute the post        
            $result = curl_exec ($ch);
        }
        
        //  Done
        curl_close ($ch);    
    }

    /**
     *
     * Function: append_groups
     * 
     */
    public static function append_groups($merge_vars, $current_groups){

        if(!isset($merge_vars["GROUPINGS"]))
            return $merge_vars;

        foreach($merge_vars["GROUPINGS"] as &$main_group){
            $existing_subgroups = self::get_existing_subgroups( $main_group["name"], $current_groups);

            if( !empty($main_group["groups"]) && !empty($existing_subgroups) )
                $main_group["groups"] .= ",";

            $main_group["groups"] .= $existing_subgroups;
        }

        return $merge_vars;
    }

    /**
     *
     * Function: get_existing_subgroups
     * 
     */
    public static function get_existing_subgroups($name, $groups){
        foreach($groups as $group){
            if(strtolower($group["name"]) == strtolower($name)){
                return $group["groups"];
            }
        }
        return array();
    }

    /**
     *
     * Function: uninstall
     * 
     */
    public static function uninstall(){

        //loading data lib
        require_once(self::get_base_path() . "/data.php");

        if(!GFActOn::has_access("gravityforms_acton_uninstall"))
            die(__("You don't have adequate permission to uninstall ActOn Add-On.", "gravityformsacton"));

        //droping all tables
        GFActOnData::drop_tables();

        //removing options
        delete_option("gf_acton_settings");
        delete_option("gf_acton_version");

        //Deactivating plugin
        $plugin = "gravityformsacton/acton.php";
        deactivate_plugins($plugin);
        update_option('recently_activated', array($plugin => time()) + (array)get_option('recently_activated'));
    }

    /**
     *
     * Function: assign_group_allowed
     * 
     */
    public static function assign_group_allowed($form, $settings, $grouping, $group, $entry){
        $config = $settings["meta"];
        $operator = $config["groups"][$grouping][$group]["operator"];
        $decision = $config["groups"][$grouping][$group]["decision"];


        $field = RGFormsModel::get_field($form, $config["groups"][$grouping][$group]["field_id"]);
        $field_value = RGFormsModel::get_lead_field_value($entry,$field);
        $is_value_match = RGFormsModel::is_value_match($field_value, $config["groups"][$grouping][$group]["value"], $operator, $field);

        if(!$config["groups"][$grouping][$group]["enabled"]){
            return false;
        }
        else if($decision == "always" || empty($field)){
            return true;
        }
        else{
            return $is_value_match;
        }

    }

    /**
     *
     * Function: is_optin
     * 
     */
    public static function is_optin($form, $settings, $entry){
        $config = $settings["meta"];

        $field = RGFormsModel::get_field($form, $config["optin_field_id"]);

        if(empty($field) || !$config["optin_enabled"])
            return true;

        $operator = isset($config["optin_operator"]) ? $config["optin_operator"] : "";
        $field_value = RGFormsModel::get_lead_field_value($entry, $field);
        $is_value_match = RGFormsModel::is_value_match($field_value, $config["optin_value"], $operator);
        $is_visible = !RGFormsModel::is_field_hidden($form, $field, array(), $entry);

        $is_optin = $is_value_match && $is_visible;

        return $is_optin;

    }

    /**
     *
     * Function: is_gravityforms_installed
     * 
     */
    private static function is_gravityforms_installed(){
        return class_exists("RGForms");
    }

    /**
     *
     * Function: is_gravityforms_supported
     * 
     */
    private static function is_gravityforms_supported(){
        if(class_exists("GFCommon")){
            $is_correct_version = version_compare(GFCommon::$version, self::$min_gravityforms_version, ">=");
            return $is_correct_version;
        }
        else{
            return false;
        }
    }

    /**
     *
     * Function: has_access
     * 
     */
    protected static function has_access($required_permission){
        $has_members_plugin = function_exists('members_get_capabilities');
        $has_access = $has_members_plugin ? current_user_can($required_permission) : current_user_can("level_7");
        if($has_access)
            return $has_members_plugin ? $required_permission : "level_7";
        else
            return false;
    }

    /**
     *
     * Function: get_base_url
     * 
     */
    protected static function get_base_url(){
        return plugins_url(null, __FILE__);
    }

    /**
     *
     * Function: get_base_path
     * 
     */
    protected static function get_base_path(){
        $folder = basename(dirname(__FILE__));
        return WP_PLUGIN_DIR . "/" . $folder;
    }

    /**
     *
     * Function: set_logging_supported
     * 
     */
    public static function set_logging_supported($plugins)
    {
        $plugins[self::$slug] = "ActOn";
        return $plugins;
    }

    /**
     *
     * Function: log_error
     * 
     */
    private static function log_error($message){
        if(class_exists("GFLogging"))
        {
            GFLogging::include_logger();
            GFLogging::log_message(self::$slug, $message, KLogger::ERROR);
        }
    }

    /**
     *
     * Function: log_debug
     * 
     */
    private static function log_debug($message){
        if(class_exists("GFLogging"))
        {
            GFLogging::include_logger();
            GFLogging::log_message(self::$slug, $message, KLogger::DEBUG);
        }
    }


    public static function getActOnCookieValue() {

        global $_COOKIE;
        
        $settings = get_option("gf_acton_settings");
        $acton_ao_a = $settings['ao_a'];

        $cookiename     = 'wp' . $acton_ao_a;
        
        if (isset( $_COOKIE[$cookiename] )) {
            return $_COOKIE[$cookiename];
        } else {
            return '';
        }

    }

    public static function getActOnTrackingIDJavascript() {

        $settings = get_option("gf_acton_settings");
        $acton_ao_a = $settings['ao_a'];

        $getExternalIDJS = 'if ("undefined" !== typeof ActOn.Beacon.cookie) { var externalID = ActOn.Beacon.cookie[\'' . $acton_ao_a . '\'];}';


        if ($getExternalIDJS) {
            return $getExternalIDJS;
        } else {
            return '';
        }

    }
}

/**
 *
 * Function: rgget
 * 
 */
if(!function_exists("rgget")){
    function rgget($name, $array=null){
        if(!isset($array))
            $array = $_GET;

        if(isset($array[$name]))
            return $array[$name];

        return "";
    }
}

/**
 *
 * Function: rgpost
 * 
 */
if(!function_exists("rgpost")){
    function rgpost($name, $do_stripslashes=true){
        if(isset($_POST[$name]))
            return $do_stripslashes ? stripslashes_deep($_POST[$name]) : $_POST[$name];

        return "";
    }
}

/**
 *
 * Function: rgar
 * 
 */
if(!function_exists("rgar")){
    function rgar($array, $name){
        if(isset($array[$name]))
            return $array[$name];

        return '';
    }
}


/**
 *
 * Function: rgempty
 * 
 */
if(!function_exists("rgempty")){
    function rgempty($name, $array = null){
        if(!$array)
            $array = $_POST;

        $val = rgget($name, $array);
        return empty($val);
    }
}


/**
 *
 * Function: rgblank
 * 
 */
if(!function_exists("rgblank")){
    function rgblank($text){
        return empty($text) && strval($text) != "0";
    }
}
