<?php
/**
 * Newfangled Progressive Profiling
 *
 * @package   Newfangled Progressive Profiling
 * @author    Newfangled
 * @link      https://bitbucket.org/newfangled_web/newfangled-progressive-profiling
 * @copyright Newfangled 2016
 */

//  Don't load directly
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

//  Define the settings class
if (!class_exists("NFProfiling_Widget")) {

    /**
     *
     * Class: NFProfiling_Widget
     * 
     */
    class NFProfiling_Widget extends WP_Widget {

        /**
         *
         * Function: __construct
         * 
         */
        function __construct() {
        
            parent::__construct(
                
                // Base ID of your widget
                'nfprofiling_widget', 

                // Widget name will appear in UI
                __('Smart CTA', 'nfprofiling_widget'), 

                // Widget description
                array( 'description' => __( 'Dynamically show one CTA at a time, based upon the user\'s past submission history', 'nfprofiling_widget' ), ) 
            );
            
        }

        /**
         * Render the Smart CTA widget
         * 
         * @param  array $args - the instance settings
         * @param  object $instance - the widget instance
         */
         public function widget( $args, $instance ) {

            global $nfprofiling, $post;

            print '<div class="widget" id="smartcta_' . $args['widget_id'] . '">';

            if ($nfprofiling->get_ajax_load_setting()) {

                $doing_ajax = false;

                if (defined('DOING_AJAX') && DOING_AJAX) {
                    $doing_ajax = true;
                }

                if (isset($_POST['gform_submit'])) {
                    $doing_ajax = true;
                }

                if (isset($_GET['ajax'])) {
                    $doing_ajax = true;
                }

                if (isset($_POST['ajax'])) {
                    $doing_ajax = true;
                }

                //  Are we already running an ajax call?
                if (!$doing_ajax) {

                    print '</div>';

                    if ( ! get_option( 'rg_gforms_disable_css' ) ) {

                        wp_enqueue_style( 'gforms_reset_css', GFCommon::get_base_url() . "/css/formreset{$min}.css", null, GFCommon::$version );
                        wp_enqueue_style( 'gforms_datepicker_css', GFCommon::get_base_url() . "/css/datepicker{$min}.css", null, GFCommon::$version );
                        wp_enqueue_style( 'gforms_formsmain_css', GFCommon::get_base_url() . "/css/formsmain{$min}.css", null, GFCommon::$version );
                        wp_enqueue_style( 'gforms_ready_class_css', GFCommon::get_base_url() . "/css/readyclass{$min}.css", null, GFCommon::$version );
                        wp_enqueue_style( 'gforms_browsers_css', GFCommon::get_base_url() . "/css/browsers{$min}.css", null, GFCommon::$version );
                    }
                    
                    wp_enqueue_script( 'gform_conditional_logic' );
                    wp_enqueue_script( 'gform_datepicker_init' );
                    wp_enqueue_script( 'gform_gravityforms' );

                    //  Add the js code for this form
                    $js_string = "<script>(function (NfCTALoader, $) {loadSmartCta('smartcta_" . $args['widget_id'] . "');}(window.NfCTALoader = window.NfCTALoader || {}, jQuery));</script>";

                    print $js_string;
                    return;
                }
            }
            

                
            $prevpost = $post;

            if ($instance_forms = $this->get_instance_forms( $instance )) {

                foreach( $instance_forms as $instance_form )  {
                    if ($instance_form['active'])  {

                        if (isset($instance_form['function'])) {

                            $this_class = $instance_form['function'][0];
                            $this_func = $instance_form['function'][1];
                            
                            if ($this_class && $this_func) {
                                if (is_callable( Array( $this_class, $this_func ))) {
                                    $instance_form = call_user_func( Array( $this_class, $this_func ));
                                } else {
                                    $instance_form = null;
                                }
                            }
                        }

                        if (!$instance_form) {
                            continue;
                        }

                        if (!$nfprofiling->has_cta_been_submitted( $instance_form ) )  {

                            if ($nfprofiling->render_smartcta_form( $instance_form ))  {
                                break;
                            }
                        }
                    }
                }
            }

            print '</div>';

            $post = $prevpost;
        }
      
        /**
         * Admin insterface for building the Smart CTA widget
         *
         * @param  object $instance - the extsting instance settings, if any
         *
         * @return object - the updated instance
         */
        public function form( $instance='' ) {

            if ( isset( $instance['title'] ) ) {
                $title = $instance['title'];
            }  else {
                $title = __( '', 'nfprofiling_widget' );
            }

            $instance_id = str_replace( array( '-', '_' ), '', $this->id );

            
            if ($instance_forms = $this->get_instance_forms( $instance ))
            {
                $types = Array();

                foreach( $instance_forms as $idx => $instance_form )
                {
                    $types[$instance_form['module']] = $instance_form['moduledesc'];
                }

                if ($types)
                {
                    $type_filter = '<select class="nfprofiling-smartcta-type-select" style="margin-top:-5px;"><option value="">All Types';

                     foreach( $types as $id => $desc )
                    {
                        $type_filter .= '<option value="' . $id . '">' . $desc;
                    }

                    $type_filter .= '</select>';
                }

            }

            ?>
            <p>
            <label style="display:none;" for="<?php print $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
            <input style="display:none;" class="widefat" id="<?php print $this->get_field_id( 'title' ); ?>" name="<?php print $this->get_field_name( 'title' ); ?>" type="text" value="<?php print esc_attr( $title ); ?>" />
            </p>

            <?php print $type_filter ?>&nbsp;
            <input type="text" class="nfprofiling-smartcta-filter" placeholder="filter" style="width:45%;" />

            <ul class="nfprofiling_widget">
            <?php 


            if ($instance_forms = $this->get_instance_forms( $instance ))
            {
                foreach( $instance_forms as $idx => $instance_form )
                {
                    if ($instance_form['active'])
                    {
                        $form_uid = $instance_form['uid'];
                        $form_name = strip_tags( $instance_form['name'] );
                        $form_postid = strip_tags( $instance_form['postid'] );
                        $form_desc = strip_tags( $instance_form['desc'] );
                        $form_active = $instance_form['active'];
                        $form_sort = $instance_form['sort'];

                        $checkbox_id    = 'checkbox-' . $this->get_field_id($form_uid);
                        $sort_id        = 'sort-' . $this->get_field_id($form_uid);
                        
                         ?>
                        <li id="<?php echo $this->get_field_id($form_uid); ?>" data-reltype="<?php print $instance_form['module'] ?>">
                            <p>
                                <input data-id="<?php print $instance_form['id'] ?>" class="checkbox" type="checkbox" checked id="<?php print $checkbox_id ?>" name="<?php print $this->get_field_name($form_uid); ?>" value="<?php print $form_sort ?>" /> 
                                <label for="<?php print $checkbox_id; ?>"><span class="item-title"><?php _e( $form_name ); ?></span></label>
                                <label style="display:block;font-size:11px;font-style:italic;margin-left:24px;"><?php 

                                    if (isset($instance_form['details'])) {
                                        print $instance_form['details'];
                                    } 
                                 ?></label>
                            </p>
                        </li>
                        <?php 
                    
                        unset( $instance_forms[ $idx ] );
                    }
                }

                if( $instance_forms )
                {
                    foreach( $instance_forms as $idx => $instance_form )
                    {
                        $form_uid = $instance_form['uid'];
                        $form_name = strip_tags( $instance_form['name'] );
                        $form_postid = strip_tags( $instance_form['postid'] );
                        $form_desc = strip_tags( $instance_form['desc'] );

                        $checkbox_id    = 'checkbox-' . $this->get_field_id($form_uid);
                        $sort_id        = 'sort-' . $this->get_field_id($form_uid);

                         ?>
                        <li id="<?php echo $this->get_field_id($form_uid); ?>" data-reltype="<?php print $instance_form['module'] ?>">
                            <p>
                                <input data-id="<?php print $instance_form['id'] ?>" class="checkbox" type="checkbox" id="<?php print $checkbox_id ?>" name="<?php print $this->get_field_name($form_uid); ?>" value="1" /> 
                                <label for="<?php print $checkbox_id; ?>"><span class="item-title"><?php _e( $form_name ); ?></span></label>
                                <label style="display:block;font-size:11px;font-style:italic;margin-left:24px;"><?php 

                                    if (isset($instance_form['details'])) {
                                        print $instance_form['details'];
                                    } 

                                ?></label>
                            </p>
                        </li>
                        <?php 
                    }
                }
            }

            ?>
            <script>
            jQuery(document).ready(function() {
                
                var instanceId = '<?php print $instance_id ?>';

                jQuery("ul.nfprofiling_widget").sortable({
                    cursor: 'move',
                    update : function () { 

                        var result = jQuery(this).sortable('toArray');
                        var sortOrder = 0;

                        jQuery(result).each(function(idx) {
                            
                            var itemId = result[ idx ];

                            if (jQuery( '#checkbox-' + itemId ).is(':checked'))
                            {
                                sortOrder++;
                               jQuery( '#checkbox-' + itemId ).val( sortOrder );
                            }
                        });
                    }
                });
                
                jQuery( '.nfprofiling-smartcta-filter' ).on( 'keyup', function(){

                    parent_el = jQuery(this).parent();

                    var value = jQuery(this).val().toUpperCase();

                    jQuery(".nfprofiling_widget .item-title", parent_el).each(function() {
                        
                        if (jQuery(this).text().toUpperCase().search(value) > -1) {
                            jQuery(this).parent().parent().parent().show();
                        }
                        else {
                            jQuery(this).parent().parent().parent().hide();
                        }
                    });
                });

                jQuery( '.nfprofiling-smartcta-type-select' ).on( 'change', function(){

                    parent_el = jQuery(this).parent();

                    var value = jQuery(this).val();

                    if (!value) {

                        jQuery(".nfprofiling_widget li", parent_el).each(function() {
                        
                            jQuery(this).show();

                        })

                    } else {

                        jQuery(".nfprofiling_widget li", parent_el).each(function() {
                            
                            console.log( value + ' - ' + jQuery(this).attr('data-reltype') );

                            if (jQuery(this).attr('data-reltype') == value) {
                                jQuery(this).show();
                            }
                            else {
                                jQuery(this).hide();
                            }
                        })
                    }
                });
            });
            </script>
            <?php
        }
    
        /**
         * Update the widget instance
         * 
         * @param  object $new_instance
         * @param  object $old_instance
         *
         * @return object - the updated instance
         */
        public function update( $new_instance, $old_instance ) {
            
            global $nfprofiling;

            $instance = array();
            $instance['title']  = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

            if ($available_forms = $nfprofiling->get_smartcta_forms()) {
                foreach( $available_forms as $form_item )
                {
                    $uid = $form_item['module'] . $form_item['id'];
                    $instance[$uid]  = ( ! empty( $new_instance[$uid] ) ) ? strip_tags( $new_instance[$uid] ) : '';
                }
            }

            return $instance;
        }

        /**
         * Get the forms selected for this widget, if any
         * 
         * @param  object $instance - the widget instance
         *
         * @return array - the list of forms
         */
        private function get_instance_forms( $instance ) {

            global $nfprofiling;
            
            if ($available_forms = $nfprofiling->get_smartcta_forms()) {
                 foreach( $available_forms as $idx => $form_item ) {
                    $uid = $form_item['module'] . $form_item['id'];
                    $available_forms[ $idx ]['uid'] = $uid;

                    if (isset( $instance[$uid] )) {
                        if (intval($instance[$uid]))  {
                            $available_forms[ $idx ]['active'] = true;
                            $available_forms[ $idx ]['sort']   = intval($instance[$uid]);
                        }
                    }
                }
            }

           
            usort($available_forms, array($this, 'sorttheseforms' ));

            return $available_forms;
        }

        /**
         * Sorting utility
         * 
         * @param  string $a
         * @param  string $b
         *
         * @return integer
         */
        private function sorttheseforms($a,$b){ 
            
            if ($a["sort"] == '' && $b["sort"] != '') return 1;
            if ($b["sort"] == '' && $a["sort"] != '') return -1;
            if (!$a["sort"] == $b["sort"]) return -1;

            return ($a["sort"] < $b["sort"]) ? -1 : 1;
        }

    
    }
}

//  Register the widget
add_action( 'widgets_init', 'NFProfiling_WidgetInit' );
function NFProfiling_WidgetInit() {
   register_widget( 'NFProfiling_Widget' );
}
