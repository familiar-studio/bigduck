<?php

if ( ! class_exists( 'Timber' ) ) {
	add_action( 'admin_notices', function() {
		echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php') ) . '</a></p></div>';
	});

	add_filter('template_include', function($template) {
		return get_stylesheet_directory() . '/static/no-timber.html';
	});

	return;
}

function add_cors_http_header(){
	// This will need to change on production
    header("Access-Control-Allow-Origin: http://127.0.0.1:3000");
}

add_filter( 'pre_get_posts', 'bd_cpt_search' );
/**
 * This function modifies the main WordPress query to include an array of
 * post types instead of the default 'post' post type.
 *
 * @param object $query  The original query.
 * @return object $query The amended query.
 */
function bd_cpt_search( $query ) {

    if ( $query->is_search ) {
			$query->set( 'post_type', array( 'bd_insight', 'bd_case_study', 'bd_event', 'bd_service' ) );
    }

    return $query;

}

register_sidebar( array(
	'id' => 'footer',
	'name' => __( 'Footer', $text_domain ),
	'description' => __( 'This is the footer Smart CTA' ),
));

register_sidebar( array(
	'id' => 'chat',
	'name' => __( 'Chat', $text_domain ),
	'description' => __( 'This is the chat Smart CTA'),
));

register_sidebar( array(
	'id' => 'insights',
	'name' => __( 'Insights', $text_domain ),
	'description' => __( 'This is the Smart CTA for Insights' ),
));

//add_action('init','add_cors_http_header');
add_filter( 'acf/fields/wysiwyg/toolbars' , 'my_toolbars'  );



function my_toolbars( $toolbars )
{
	// Uncomment to view format of $toolbars
	/*
	echo '< pre >';
		print_r($toolbars);
	echo '< /pre >';
	die;
	*/

	// Add a new toolbar called "Very Simple"
	// - this toolbar has only 1 row of buttons
	$toolbars['Very Simple' ] = array();
	$toolbars['Very Simple' ][1] = array('formatselect', 'bold' , 'italic', 'underline', 'bullist', 'numlist', 'link', 'unlink', 'fullscreen' );

	if( ($key = array_search('code' , $toolbars['Full' ][2])) !== false )
	{
	    unset( $toolbars['Full' ][2][$key] );
	}

	// var_dump( $toolbars['Basic']);
	// die();

	// Edit the "Full" toolbar and remove 'code'
	// - delet from array code from http://stackoverflow.com/questions/7225070/php-array-delete-by-value-not-key
	if( ($key = array_search('code' , $toolbars['Full' ][2])) !== false )
	{
	    unset( $toolbars['Full' ][2][$key] );
	}

	// remove the 'Basic' toolbar completely
	unset( $toolbars['Basic' ] );

	// return $toolbars - IMPORTANT!
	return $toolbars;
}

add_filter( 'rest_user_query' , 'custom_rest_user_query' );
function custom_rest_user_query( $prepared_args, $request = null ) {
  unset($prepared_args['has_published_posts']);
  return $prepared_args;
}

add_filter( 'acf_to_wp_rest_api_user_data', function( $data, $object, $context ) {
    if ( isset( $data['type'] ) && 'user' == $data['type'] && isset( $data['acf'] ) && isset( $data['acf']['team_member'] ) && $data['acf']['team_member'] == 'yes' ) {
    	return $data;
    }
		return null;
}, 10, 3 );

function cc_mime_types($mimes) {
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}

add_filter( 'upload_mimes', 'cc_mime_types');

Timber::$dirname = array('templates', 'views');

class StarterSite extends TimberSite {

	function __construct() {
		add_theme_support( 'post-formats' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'menus' );
		add_filter( 'timber_context', array( $this, 'add_to_context' ) );
		add_filter( 'get_twig', array( $this, 'add_to_twig' ) );
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_bootstrap' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_bundles'));
		add_action( 'rest_api_init', array( $this, 'add_rest_fields'));
		add_action( 'rest_api_init', array( $this, 'register_routes') );
		add_action( 'init', array( $this, 'add_rest_to_cpts'));
		add_action( 'init', array( $this, 'add_image_sizes'));
		parent::__construct();
		// add_filter( 'allowed_http_origin', '__return_true' );
		add_action( 'init', array( $this, 'handle_preflight'));

		// add_action( 'rest_api_init', function() {

		// 	remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );

		// }, 15 );

				parent::__construct();


	}

	function handle_preflight() {
		// Set the domain that's allowed to make the API call.
		header("Access-Control-Allow-Origin: " . get_http_origin());
		// Set the methods
		header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");

		header("Access-Control-Allow-Headers: Origin, Content-Type, Accept");

		if ( 'OPTIONS' == $_SERVER['REQUEST_METHOD'] ) {
			status_header(200);
			exit();
		}
	}

	function register_routes() {
		register_rest_route( 'familiar/v1', '/widgets', array(
			'methods' => 'GET',
			'callback' => array($this, 'get_items')
		));
		register_rest_route( 'familiar/v1', '/widgets/(?P<id_base>[\w-]+)', array(
			'methods' => 'GET',
			'callback' => array($this, 'get_item')
		));
		register_rest_route( 'familiar/v1/', '/widgets/(?P<id_base>[\w-]+)/(?P<instance_id>[\w-]+)', array(
			'methods' => 'GET',
			'callback' => array($this, 'get_item_instance')
		));
		register_rest_route( 'familiar/v1', '/sidebars', array(
			'methods' => 'GET',
			'callback' => array($this, 'sidebars')
		));
		register_rest_route( 'familiar/v1', '/sidebars/(?P<id>[\w-]+)', array(
			'methods' => 'GET',
			'callback' => array($this, 'sidebar')
		));
		register_rest_route( 'familiar/v1', '/featured-work', array(
			'methods' => 'GET',
			'callback' => array($this, 'featured_work')
		));
		register_rest_route('familiar/v1', '/events/user/(?P<id>\d+)', array(
			'methods' => 'GET',
			'callback' => array($this, 'events_by_user')
		));
		register_rest_route('familiar/v1', '/work/user/(?P<id>\d+)', array(
			'methods' => 'GET',
			'callback' => array($this, 'work_by_user')
		));
		register_rest_route('familiar/v1', '/team', array(
			'methods' => 'GET',
			'callback' => array($this, 'team')
		));
		register_rest_route('familiar/v1', '/team/(?P<id>[\w-]+)', array(
			'methods' => 'GET',
			'callback' => array($this, 'get_team_member')
		));
		register_rest_route('familiar/v1', '/events', array(
			'methods' => 'GET',
			'callback' => array($this, 'get_events')
		));
	}

	function get_items( $request ){
		// do type checking here as the method declaration must be compatible with parent
	 if ( ! $request instanceof WP_REST_Request ) {
			 throw new InvalidArgumentException( __METHOD__ . ' expects an instance of WP_REST_Request' );
	 }
	 global $wp_registered_widgets;
	 $widgets = [];
	 foreach ( (array) $wp_registered_widgets as $key => $widget ) {
			 if ( isset( $widget['callback'][0] ) && $widget['callback'][0] instanceof WP_Widget ) {
					 $widget_instance = $widget['callback'][0];
					 $unique_widget = ! in_array( $widget_instance->id_base, array_map( function ( $widget ) {
							 return $widget['id_base'];
					 }, $widgets ) );
					 // only push unique widgets as we are not interested in the instances
					 if ( $unique_widget ) {
							 $widget['name'] = $widget_instance->name;
							 $widget['id_base'] = $widget_instance->id_base;
							 $widget['option_name'] = $widget_instance->option_name;
							 $widget['instances'] = 0;
							 $widget['sidebars'] = [];
							 // list the sidebars this widget has instances in, and the instance id's
							 foreach ( (array) wp_get_sidebars_widgets() as $sidebar_id => $sidebar_widgets ) {
									 foreach ( $sidebar_widgets as $widget_id ) {
											 if ( preg_match( "/({$widget['id_base']}-\d)/", $widget_id, $match ) ) {
													 if ( ! isset( $widget['sidebars'][ $sidebar_id ] ) ) {
															 $widget['sidebars'][ $sidebar_id ] = [];
													 }
													 ++$widget['instances'];
													 $widget['sidebars'][ $sidebar_id ][] = $widget_id;
											 }
									 }
							 }
							 unset( $widget['id'] );
							 unset( $widget['params'] );
							 unset( $widget['callback'] );
							 $widgets[] = $widget;
					 }
			 }
	 }
	 return new WP_REST_Response( $widgets, 200 );
	}

	function get_item( $request ){
		// do type checking here as the method declaration must be compatible with parent
		if ( ! $request instanceof WP_REST_Request ) {
				throw new InvalidArgumentException( __METHOD__ . ' expects an instance of WP_REST_Request' );
		}
		$widget = self::get_widget( $request->get_param( 'id_base' ) );
		return new WP_REST_Response( $widget, 200 );
	}

	function get_item_instance( $request ){
		// do type checking here as the method declaration must be compatible with parent
        if ( ! $request instanceof WP_REST_Request ) {
            throw new InvalidArgumentException( __METHOD__ . ' expects an instance of WP_REST_Request' );
        }
        $widget_instance = self::get_widget_instance( $request->get_param( 'instance_id' ) );
        return new WP_REST_Response( $widget_instance, 200 );
	}

	public static function get_widget( $id_base ) {
        global $wp_registered_widgets;
        foreach ( (array) $wp_registered_widgets as $id => $widget ) {
            if ( isset( $widget['callback'][0] ) && $widget['callback'][0] instanceof WP_Widget ) {
                $widget_instance = $widget['callback'][0];
                if ( $widget_instance->id_base === $id_base ) {
                    $widget['name'] = $widget_instance->name;
                    $widget['id_base'] = $widget_instance->id_base;
                    $widget['option_name'] = $widget_instance->option_name;
                    $widget['instances'] = 0;
                    $widget['sidebars'] = [];
                    // list the sidebars this widget has instances in, and the instance id's
                    foreach ( (array) wp_get_sidebars_widgets() as $sidebar_id => $sidebar_widgets ) {
                        foreach ( $sidebar_widgets as $widget_id ) {
                            if ( preg_match( "/({$widget['id_base']}-\d)/", $widget_id, $match ) ) {
                                if ( ! isset( $widget['sidebars'][ $sidebar_id ] ) ) {
                                    $widget['sidebars'][ $sidebar_id ] = [];
                                }
                                ++$widget['instances'];
                                $widget['sidebars'][ $sidebar_id ][] = $widget_id;
                            }
                        }
                    }
                    unset( $widget['id'] );
                    unset( $widget['params'] );
                    unset( $widget['callback'] );
                    return $widget;
                }
            }
        }
        return null;
    }
    /**
     * Returns a widget instance based on the given id or null if not found
     *
     * @global array $wp_registered_widgets
     *
     * @param string $instance_id
     *
     * @return WP_Widget|null
     */
    public static function get_widget_instance( $instance_id ) {
        global $wp_registered_widgets;
        foreach ( (array) $wp_registered_widgets as $id => $widget ) {
            if (
                $instance_id === $id &&
                isset( $widget['callback'][0] ) &&
                $widget['callback'][0] instanceof WP_Widget
            ) {
                // @todo: format the widget object
                return $widget['callback'][0];
            }
        }
        return null;
    }

	function sidebars( $request ){
		if ( ! $request instanceof WP_REST_Request ) {
					 throw new InvalidArgumentException( __METHOD__ . ' expects an instance of WP_REST_Request' );
			 }
			 global $wp_registered_sidebars;
			 $sidebars = [];
			 foreach ( (array) wp_get_sidebars_widgets() as $id => $widgets ) {
					 $sidebar = compact( 'id', 'widgets' );
					 if ( isset( $wp_registered_sidebars[ $id ] ) ) {
							 $registered_sidebar = $wp_registered_sidebars[ $id ];
							 $sidebar['status'] = 'active';
							 $sidebar['name'] = $registered_sidebar['name'];
							 $sidebar['description'] = $registered_sidebar['description'];
					 } else {
							 $sidebar['status'] = 'inactive';
					 }
					 $sidebars[] = $sidebar;
			 }
			 return new WP_REST_Response( $sidebars, 200 );
	}

	function sidebar( $request ){
		if ( ! $request instanceof WP_REST_Request ) {
				throw new InvalidArgumentException( __METHOD__ . ' expects an instance of WP_REST_Request' );
		}
		ob_start();
		gravity_form(3);
		$sidebar = self::get_sidebar( $request->get_param( 'id' ) );
		$sidebar['widgets'] = self::get_widgets( $sidebar['id'] );
		$sidebar['rendered'] = ob_get_clean();
		return new WP_REST_Response( $sidebar, 200 );
	}

	public static function get_sidebar( $id ) {
        global $wp_registered_sidebars;
        if ( is_int( $id ) ) {
            $id = 'sidebar-' . $id;
        } else {
            $id = sanitize_title( $id );
            foreach ( (array) $wp_registered_sidebars as $key => $sidebar ) {
                if ( sanitize_title( $sidebar['name'] ) == $id ) {
                    return $sidebar;
                }
            }
        }
        foreach ( (array) $wp_registered_sidebars as $key => $sidebar ) {
            if ( $key === $id ) {
                return $sidebar;
            }
        }
        return null;
    }
    /**
     * Returns a list of widgets for the given sidebar id
     *
     * @global array $wp_registered_widgets
     * @global array $wp_registered_sidebars
     *
     * @param string $sidebar_id
     *
     * @return array
     */
    public static function get_widgets( $sidebar_id ) {
        global $wp_registered_widgets, $wp_registered_sidebars;
        $widgets = [];
        $sidebars_widgets = (array) wp_get_sidebars_widgets();
        if ( isset( $wp_registered_sidebars[ $sidebar_id ] ) && isset( $sidebars_widgets[ $sidebar_id ] ) ) {
            foreach ( $sidebars_widgets[ $sidebar_id ] as $widget_id ) {
                // just to be sure
                if ( isset( $wp_registered_widgets[ $widget_id ] ) ) {
                    $widget = $wp_registered_widgets[ $widget_id ];
                    // get the widget output
                    if ( is_callable( $widget['callback'] ) ) {
                        // @note: everything up to ob_start is taken from the dynamic_sidebar function
                        $widget_parameters = array_merge(
                            [
                                array_merge( $wp_registered_sidebars[ $sidebar_id ], [
                                    'widget_id' => $widget_id,
                                    'widget_name' => $widget['name'],
                                ] )
                            ],
                            (array) $widget['params']
                        );
                        $classname = '';
                        foreach ( (array) $widget['classname'] as $cn ) {
                            if ( is_string( $cn ) )
                                $classname .= '_' . $cn;
                            elseif ( is_object( $cn ) )
                                $classname .= '_' . get_class( $cn );
                        }
                        $classname = ltrim( $classname, '_' );
                        $widget_parameters[0]['before_widget'] = sprintf( $widget_parameters[0]['before_widget'], $widget_id, $classname );
                        ob_start();
                        call_user_func_array( $widget['callback'], $widget_parameters );
                        $widget['rendered'] = ob_get_clean();
                    }
                    unset( $widget['callback'] );
                    unset( $widget['params'] );
                    $widgets[] = $widget;
                }
            }
        }
        return $widgets;
    }


	function team($object) {
		$users = get_users(array(
			'fields' => 'all_with_meta'
		));
		$team = array();
		foreach($users as $key => $user){
				$fields = get_fields('user_' . $user->ID);

				if ($fields['team_member'] == 'yes'){
					$fields['slug'] = $user->user_nicename;
					$fields['name'] = $user->display_name;
					$fields['email'] = $user->user_email;
					// add name, email, and slug to $fields
					$team[] = $fields;
				}
			// }
		}
		return new WP_REST_Response($team);
	}

	function get_team_member($request) {
		$nicename = $request->get_params('id')['id'];
		// $user = get_user_by('nicename', $nicename->id);
		$user = get_user_by('slug', $nicename);
		$fields = get_fields('user_' . $user->ID);
		if ($fields['team_member'] == 'yes'){
			$fields['slug'] = $user->user_nicename;
			$fields['name'] = $user->display_name;
			$fields['email'] = $user->user_email;
			// add name, email, and slug to $fields
			$team_member = $fields;
		}
		$rawEvents = get_posts(array(
				'post_type' => 'bd_event'
		));

		$team_member['events'] = array();
		foreach($rawEvents as $rawEvent){
			$fields = get_fields($rawEvent->ID);
			// $events[] = $fields['start_time'];
			// $events[] = strtotime($fields['start_time']);
			if(strtotime($fields['start_time']) > strtotime('now')){
				$team = $fields['related_team_members'];
				foreach($team as $member) {
					if ($member['ID'] == $user->ID){
						// $meta = get_post_content($rawEvent->ID);
						$topics = wp_get_post_terms($rawEvent->ID, 'topic');
						$eventCategories = wp_get_post_terms($rawEvent->ID, 'event_category');
						$data = get_post($rawEvent->ID);
						$postContent = $data->post_content;
						$data->acf = $fields;
						$data->event_category = array($eventCategories[0]->term_id);
						$data->topic = array($topics[0]->term_id);
						$data->title = array('rendered' => get_the_title($rawEvent->ID));
						$team_member['events'][] = $data;
						continue;
					}
				}
			}
		}

		$rawInsights = get_posts(array(
			'post_type' => 'bd_insight'
		));

		$team_member['insights'] = array();
		foreach($rawInsights as $rawInsight){
			$fields = get_fields($rawInsight->ID);
			if ($fields['author']['ID'] == $user->ID){
				$insight = get_post($rawInsight->ID);
				$topics = wp_get_post_terms($rawInsight->ID, 'topic');
				$insight->acf = $fields;
				$insight->topic = array($topics[0]->term_id);
				$team_member['insights'][] = $insight;
			}
		}

		return new WP_REST_Response($team_member);
	}

	function events_by_user($data) {
		$rawEvents = get_posts(array(
				'post_type' => 'bd_event'
		));
		$events = array();
		foreach($rawEvents as $rawEvent){
			$fields = get_fields($rawEvent->ID);
			// $events[] = $fields['start_time'];
			// $events[] = strtotime($fields['start_time']);
			if(strtotime($fields['start_time']) > strtotime('now')){
				$team = $fields['related_team_members'];
				foreach($team as $member) {
					if ($member['ID'] == $data->get_params('id')['id']){
						$events[] = $fields;
						continue;
					}
				}
			}
		}
		// $related_events = array();
		// foreach($events as $event){
		// 	$field = get_field('related_team_members', $event->ID);
		// 	if (gettype($field) == "array"){
		// 		foreach($field as $member){
		// 			if($member['ID'] == $data['id']){
		// 				array_push($related_events, $event);
		// 			}
		// 		}
		// 	}
		// }
		return new WP_REST_Response(array('events' => $events));
	}

	function featured_work($data) {
		$work = get_posts(array(
			'post_type' => 'bd_case_study',
			'meta_key' => 'featured',
			'meta_value' => true,
		));

		$featured_work = array();

		if( $work ){
			foreach( $work as $case_study ){
				$topic = wp_get_post_terms($case_study->ID, 'topic');
				$acf = get_fields($case_study->ID);
				array_push($featured_work,
					array('id' => $case_study->ID, 'acf' => $acf, 'topic' => $topic )
				);
			}
		}

		return new WP_REST_Response($featured_work);
	}

	function add_image_sizes() {
		add_image_size( 'cropped_400_square', 400, 400, TRUE);
		add_image_size( 'cropped_rectangle', 700, 350, TRUE);
	}



	function add_rest_fields() {
		register_rest_field('event_category',
			'icon',
			array(
				'get_callback' => array($this, 'get_event_category_icon'),
				'schema' =>	null
			)
		);
		register_rest_field('sector',
			'icon',
			array(
				'get_callback' => array($this, 'get_sector_icon'),
				'schema' =>	null
			)
		);
		register_rest_field('type',
			'icon',
			array(
				'get_callback' => array($this, 'get_type_icon' ),
				'schema' => null
			)
		);
		register_rest_field('topic',
			'icon',
			array(
				'get_callback' => array($this, 'get_topic_icon' ),
				'schema' => null
			)
		);
		register_rest_field('bd_insight',
			'author_headshot',
				array(
					'get_callback' => array( $this, 'get_author_headshot' ),
					'schema' => null
				)
		);
		register_rest_field('bd_event',
			'related_team_members',
			array(
				'get_callback' => array( $this, 'get_event_team_members'),
				'schema' => null
			)
		);
		register_rest_field('bd_insight',
			'calculated_reading_time',
			array(
				'get_callback' => array( $this, 'insight_reading_time' ),
				'schema' => null
			)
		);
		register_rest_field('user',
			'headshot',
			array(
				'get_callback' => array( $this, 'get_user_headshot' ),
				'schema' => null
			)
		);
		register_rest_field('user',
			'work',
			array(
				'get_callback' => array( $this, 'get_user_work' ),
				'schema' => null
			)
		);
		register_rest_field('user',
			'team_member',
			array(
				'get_callback' => array( $this, 'is_user_team_member'),
				'schema' => null
			)
		);
		register_rest_field('user',
			'related_events',
			array(
				'get_callback' => array( $this, 'get_user_events'),
				'schema' => null
			)
		);
		register_rest_field('bd_case_study',
			'related_team_members',
			array(
				'get_callback' => array( $this, 'get_work_team_members'),
				'schema' => null
			)
		);
		register_rest_field('page',
			'featured_image',
			array(
				'get_callback' => array( $this, 'get_featured_image'),
				'schema' => null
			)
		);
	}

	function get_featured_image($object) {
			return wp_get_attachment_url($object['featured_media']);
	}

	function get_event_times($object) {
		//This is a good example of adding a native (non-ACF) field to a wp/v2 endpoint that's already returning the acf data
		$event = get_post($object->ID);
		return new WP_REST_Response(array(
			'event' => $event,
			'EventStartDate'=> $event->EventStartDate,
			'EventEndDate'=> $event->EventEndDate
		));
	}

	function insight_reading_time($object) {
		$body = get_field('body');
		$content = '';
		if (!is_null($body) && $body){
			foreach($body as $block) {
				$content .= $block['text'];
			}
		}
		$wordCount = str_word_count(strip_tags($content));
		$minutes = floor($wordCount / 200);
		$minutes = $minutes . ' min' . ($minutes == 1 ? '' : 's');
		return new WP_REST_Response($minutes);
	}

	function get_event_category_icon($object) {
		$icon = get_field('taxonomy-icon', 'event_category_'.$object['id']);
		if (!empty($icon)){
			$icon = file_get_contents($icon);
		}
		return $icon;
	}

	function get_sector_icon($object) {
		$icon = get_field('taxonomy-icon', 'sector_'.$object['id']);
		if (!empty($icon)){
			$icon = file_get_contents($icon);
		}
		return $icon;
	}

	function get_type_icon($object) {
		$icon = get_field('taxonomy-icon', 'type_'.$object['id']);
		if (!empty($icon)){
			$icon = file_get_contents($icon);
		}
		return $icon;
	}

	function get_topic_icon($object) {
		$icon = get_field('taxonomy-icon', 'topic_'.$object['id']);
		if (!empty($icon)){
			$icon = file_get_contents($icon);
		}
		return $icon;
	}

	function get_work_team_members($object) {
		return new WP_REST_Response(get_field('related_team_members', 'bd_case_study_'. $object['id']));
	}

	function is_user_team_member($object) {
		return new WP_REST_Response(get_field('team_member', 'user_' . $object['id']));
	}

	function get_user_headshot($object) {
		return new WP_REST_Response(get_field('headshot', 'user_' . $object['id']));
	}

	function get_user_work($object) {
		$work = get_posts(array(
			'post_type' => 'bd_case_study',
			'meta_key' => 'related_team_members',
			'meta_value' => $object['id']
		));
		return new WP_REST_Response($work);
	}

	function get_user_events($data) {
		// $user_id = $data['id'];
		// $event_ids = array();
		// $events = tribe_get_events();
		// $related_team_members = array();
		$user_events = array();
		$event_ids = array();
		$events = get_posts(array(
			'post_type' => 'tribe_events',
			'meta_key' => 'related_team_members',
			'meta_value' => $object['id']
		));
		foreach($events as $event){
			$event_tms = get_field('related_team_members', 'tribe_events_' . $event->ID);
			array_push($event_ids, $event_tms);

		}

		return new WP_REST_Response(array( 'events' => $events, 'event_ids' => $event_ids ));
	}

	function get_author_headshot($object) {
		$author = get_field('author');
		$id = $author['ID'];
		$headshot = get_field('headshot', 'user_' . $id);

		return $headshot;
	}

	function get_event_team_members($object) {
		$rawMembers = get_field('related_team_members');
		$members = array();
		foreach($rawMembers as $member){
			$members[] = array(
				'headshot' => get_field('headshot', 'user_' . $member['ID']),
				'member' => $member
			);
			// $member['headshot'] = get_field('headshot', 'user_' . $id);
		}
		return new WP_REST_Response($members);
		return new WP_REST_Response(get_fields('bd_event_' . $object['id']));
	}

	function get_post_event_for_api($object) {
		$post_id = $object['id'];
		$start_time = tribe_get_start_date( $post_id, true );
		$end_time = tribe_get_end_date( $post_id, true );

		return new WP_REST_Response(array('start' => $start_time, 'end' => $end_time));
	}

	function add_rest_to_taxonomies() {
		global $wp_taxonomies;
		$taxonomy_name = 'tribe_events_cat';

		if ( isset ( $wp_taxonomies[ $taxonomy_name ] ) ) {
			$wp_taxonomies[ $taxonomy_name ]->show_in_rest = true;
			$wp_taxonomies[ $taxonomy_name ]->rest_base = $taxonomy_name;
			$wp_taxonomies[ $taxonomy_name ]->rest_controller_class = 'WP_REST_Terms_Controller';
		}
	}

	function add_rest_to_cpts() {
		global $wp_post_types;
		$post_type_name = 'tribe_events';
		if ( isset ( $wp_post_types[ $post_type_name ] ) ) {
			$wp_post_types[ $post_type_name ]->show_in_rest = true;
			$wp_post_types[ $post_type_name ]->rest_base = $post_type_name;
			$wp_post_types[ $post_type_name ]->rest_controller_class = 'WP_REST_Posts_Controller';
		}
	}

	function add_categories_to_cpts() {
		register_taxonomy_for_object_type('category', 'bd_insight');
		register_taxonomy_for_object_type('category', 'page');
		register_taxonomy_for_object_type('category', 'bd_case_study');
		register_taxonomy_for_object_type('category', 'bd_service');
		register_taxonomy_for_object_type('category', 'tribe_events');
		register_taxonomy_for_object_type('category', 'bd_event');
	}

	function enqueue_bundles() {
		wp_enqueue_script('commons', '/public/dist/commons-bundle.js', array(), false, true);
		wp_enqueue_script('global', '/public/dist/global-bundle.js', array('commons'), false, true);
	}

	function enqueue_bootstrap() {
		wp_enqueue_script('jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js', array(), '3.2.1', true);
		wp_enqueue_script('tether', 'https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js', array(), '1.4.0', true);
		wp_enqueue_script('bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js', array('jquery'), '1.0.0', true);
		wp_enqueue_style('bootstrap', get_stylesheet_directory_uri() . '/css/bootstrap.min.css' );
	}

	function register_post_types() {
		//this is where you can register custom post types
			register_post_type( 'bd_event',
				array(
					'labels' => array(
						'name' => __( 'Events' ),
						'singular_name' => __( 'Event' )
					),
					'public' => true,
					'show_ui' => true,
					'show_in_menu' => true,
					'has_archive' => true,
					'rewrite' => array('slug' => 'events'),
					'show_in_rest' => true,
				)
			);
		  register_post_type( 'bd_insight',
		    array(
		      'labels' => array(
		        'name' => __( 'Insights' ),
		        'singular_name' => __( 'Insight' )
		      ),
		      'public' => true,
					'show_ui' => true,
					'show_in_menu' => true,
		      'has_archive' => true,
					'rewrite' => array('slug' => 'insights'),
					'show_in_rest' => true,
					// 'taxonomies'          => array( 'category' )
		    )
		  );
			register_post_type( 'bd_service',
		    array(
		      'labels' => array(
		        'name' => __( 'Services' ),
		        'singular_name' => __( 'Service' )
		      ),
		      'public' => true,
					'show_ui' => true,
					'show_in_menu' => true,
		      'has_archive' => true,
					'rewrite' => array('slug' => 'services'),
					'show_in_rest' => true
		    )
		  );
			register_post_type( 'bd_case_study',
				array(
					'labels' => array(
						'name' => __( 'Work' ),
						'singular_name' => __( 'Case Study' )
					),
					'public' => true,
					'show_ui' => true,
					'show_in_menu' => true,
					'has_archive' => true,
					'rewrite' => array('slug' => 'work'),
					'show_in_rest' => true,
				)
			);
			register_post_type( 'bd_client',
				array(
					'labels' => array(
						'name' => __( 'Clients' ),
						'singular_name' => __( 'Client' )
					),
					'public' => true,
					'show_ui' => true,
					'show_in_menu' => true,
					'has_archive' => true,
					'rewrite' => array('slug' => 'clients'),
					'show_in_rest' => true,
				)
			);

			register_post_type( 'bd_job',
				array(
					'labels' => array(
						'name' => __( 'Jobs' ),
						'singular_name' => __( 'Job' )
					),
					'public' => true,
					'show_ui' => true,
					'show_in_menu' => true,
					'has_archive' => true,
					'rewrite' => array('slug' => 'jobs'),
					'show_in_rest' => true,
				)
			);
			register_post_type( 'bd_callout',
				array(
					'labels' => array(
						'name' => __( 'Callouts' ),
						'singular_name' => __( 'Callout' )
					),
					'public' => true,
					'show_ui' => true,
					'show_in_menu' => true,
					'has_archive' => true,
					'rewrite' => array('slug' => 'callouts'),
					'show_in_rest' => true
				)
			);
	}

	function register_taxonomies() {
		//this is where you can register custom taxonomies
		$labels = array(
			'name'              => _x( 'Event Categories', 'taxonomy general name', 'textdomain' ),
			'singular_name'     => _x( 'Event', 'taxonomy singular name', 'textdomain' ),
			'search_items'      => __( 'Search Event', 'textdomain' ),
			'all_items'         => __( 'All Event Categoies', 'textdomain' ),
			'parent_item'       => __( 'Parent Event Category', 'textdomain' ),
			'parent_item_colon' => __( 'Parent Event Category:', 'textdomain' ),
			'edit_item'         => __( 'Edit Event Category', 'textdomain' ),
			'update_item'       => __( 'Update Event Category', 'textdomain' ),
			'add_new_item'      => __( 'Add New Event Category', 'textdomain' ),
			'new_item_name'     => __( 'New Sector Name', 'textdomain' ),
			'menu_name'         => __( 'Event Categories', 'textdomain' ),
		);

		$args = array(
			'hierarchical' => true,
			'labels' => $labels,
			'rewrite' => array( 'slug' => 'event_category' ),
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_rest' => true,
			'show_admin_column' => true
		);

		register_taxonomy(
			'event_category',
			array('bd_event'),
			$args
		);

		$labels = array(
			'name'              => _x( 'Sectors', 'taxonomy general name', 'textdomain' ),
			'singular_name'     => _x( 'Sector', 'taxonomy singular name', 'textdomain' ),
			'search_items'      => __( 'Search Sector', 'textdomain' ),
			'all_items'         => __( 'All Sectors', 'textdomain' ),
			'parent_item'       => __( 'Parent Sector', 'textdomain' ),
			'parent_item_colon' => __( 'Parent Sector:', 'textdomain' ),
			'edit_item'         => __( 'Edit Sector', 'textdomain' ),
			'update_item'       => __( 'Update Sector', 'textdomain' ),
			'add_new_item'      => __( 'Add New Sector', 'textdomain' ),
			'new_item_name'     => __( 'New Sector Name', 'textdomain' ),
			'menu_name'         => __( 'Sectors', 'textdomain' ),
		);

		$args = array(
			'hierarchical' => true,
			'labels' => $labels,
			'rewrite' => array( 'slug' => 'sector' ),
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_rest' => true,
			'show_admin_column' => true
		);

		register_taxonomy(
			'sector',
			array('bd_case_study', 'bd_client'),
			$args
		);

		$labels = array(
			'name'              => _x( 'Topics', 'taxonomy general name', 'textdomain' ),
			'singular_name'     => _x( 'Topic', 'taxonomy singular name', 'textdomain' ),
			'search_items'      => __( 'Search Topics', 'textdomain' ),
			'all_items'         => __( 'All Topics', 'textdomain' ),
			'parent_item'       => __( 'Parent Topic', 'textdomain' ),
			'parent_item_colon' => __( 'Parent Topic:', 'textdomain' ),
			'edit_item'         => __( 'Edit Topic', 'textdomain' ),
			'update_item'       => __( 'Update Topic', 'textdomain' ),
			'add_new_item'      => __( 'Add New Topic', 'textdomain' ),
			'new_item_name'     => __( 'New Topic Name', 'textdomain' ),
			'menu_name'         => __( 'Topics', 'textdomain' ),
		);

		$args = array(
			'hierarchical' => true,
			'labels' => $labels,
			'rewrite' => array( 'slug' => 'topic' ),
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_rest' => true,
			'show_admin_column' => true
		);

		register_taxonomy(
			'topic',
			array('bd_insight', 'bd_service', 'bd_case_study', 'bd_event'),
			$args
		);

		$labels = array(
			'name'              => _x( 'Types', 'taxonomy general name', 'textdomain' ),
			'singular_name'     => _x( 'Type', 'taxonomy singular name', 'textdomain' ),
			'search_items'      => __( 'Search Types', 'textdomain' ),
			'all_items'         => __( 'All Types', 'textdomain' ),
			'parent_item'       => __( 'Parent Type', 'textdomain' ),
			'parent_item_colon' => __( 'Parent Type:', 'textdomain' ),
			'edit_item'         => __( 'Edit Type', 'textdomain' ),
			'update_item'       => __( 'Update Type', 'textdomain' ),
			'add_new_item'      => __( 'Add New Type', 'textdomain' ),
			'new_item_name'     => __( 'New Type Name', 'textdomain' ),
			'menu_name'         => __( 'Types', 'textdomain' ),
		);

		$args = array(
			'hierarchical' => true,
			'labels' => $labels,
			'rewrite' => array( 'slug' => 'type' ),
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_rest' => true,
			'show_admin_column' => true
		);

		register_taxonomy(
			'type',
			array('bd_insight'),
			$args
		);
	}

	function add_to_context( $context ) {
		$context['foo'] = 'bar';
		$context['stuff'] = 'I am a value set in your functions.php file';
		$context['notes'] = 'These values are available everytime you call Timber::get_context();';
		$context['menu'] = new TimberMenu();
		$context['site'] = $this;
		return $context;
	}

	function myfoo( $text ) {
		$text .= ' bar!';
		return $text;
	}

	function add_to_twig( $twig ) {
		/* this is where you can add your own functions to twig */
		$twig->addExtension( new Twig_Extension_StringLoader() );
		$twig->addFilter('myfoo', new Twig_SimpleFilter('myfoo', array($this, 'myfoo')));
		return $twig;
	}

	function get_events( $data ){
		$events = get_posts(array(
			'post_type' => 'bd_event'
		));
		foreach($events as $event){
			$event->acf = get_fields($event->id);
		}
		return new WP_REST_Response(array( 'events' => $events ));
	}

	function get_past_events( $data ){
		$events = tribe_get_events( array(
			'end_date' => date( 'Y-m-d H:i:s' )
		));
		return new WP_REST_Response(array( 'events' => $events ));
	}

	function get_events_categories( $data ){
		$categories = get_object_taxonomies('tribe_events', 'objects');
		return new WP_REST_Response(array( 'categories' => $categories ));
	}

	function get_insights_and_categories( $data ){
		$insights = get_posts( array(
			'post_type' => 'bd_insight'
		));

		$categories = get_categories(array(
			"hide_empty" => 0,
			'exclude' => 1
		));
		return new WP_REST_Response(array( 'insights' => $insights, 'categories' => $categories), 200);
	}

}

new StarterSite();
