<?php


function bd_pre_get_posts( $query ) {
	// if( is_admin() ) {
	// 	return $query;
	// }
	if ( isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'bd_event' ) {
		$today = date('Y-m-d H:i:s');
		$query->set('orderby', 'meta_value');
		$query->set('meta_key', 'start_time');
		$query->set('order', 'ASC');
		$query->set('meta_query', array(
			array(
				'key' => 'start_time',
				'value' => $today,
				'compare' => '>'
			)
		));
	}

	return $query;
}

add_filter('pre_get_posts', 'bd_pre_get_posts');

function bd_search_join( $join ) {
	global $wpdb;

	if ( is_search() ) {
		$join .=' LEFT JOIN '.$wpdb->postmeta. ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id';
	}
	return $join;
}

add_filter('posts_join', 'bd_search_join');

function bd_search_where( $where ) {
    global $wpdb;

    if ( is_search() ) {
        $where = preg_replace(
            "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            "(".$wpdb->posts.".post_title LIKE $1) OR (".$wpdb->postmeta.".meta_value LIKE $1)", $where );
    }

    return $where;
}
add_filter( 'posts_where', 'bd_search_where' );


function bd_search_distinct( $where ) {
	global $wpdb;
	if ( is_search() ) {
		return "DISTINCT";
	}
	return $where;
}

add_filter('posts_distinct', 'bd_search_distinct');

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
add_theme_support( 'post-formats' );
add_theme_support( 'post-thumbnails' );
add_theme_support( 'menus' );
add_action( 'init', array( $this, 'register_post_types' ) );
add_action( 'init', array( $this, 'register_taxonomies' ) );
add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_bootstrap' ) );
add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_bundles'));
add_action( 'rest_api_init', array( $this, 'add_rest_fields'));
add_action( 'rest_api_init', array( $this, 'register_routes') );
add_action( 'init', array( $this, 'add_rest_to_cpts'));
add_action( 'init', array( $this, 'add_image_sizes'));
add_action( 'init', array( $this, 'handle_preflight'));

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
		register_rest_route( 'familiar/v1', '/featured-work', array(
			'methods' => 'GET',
			'callback' => array($this, 'featured_work')
		));
		register_rest_route('familiar/v1', '/insights/user/(?P<id>[\w-]+)', array(
			'methods' => 'GET',
			'callback' => array($this, 'insights_by_user')
		));
		register_rest_route('familiar/v1', '/events/user/(?P<id>[\w-]+)', array(
			'methods' => 'GET',
			'callback' => array($this, 'events_by_user')
		));
		register_rest_route('familiar/v1', '/work/user/(?P<id>[\w-]+)', array(
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
		register_rest_route('familiar/v1', '/gated', array(
			'methods' => 'GET',
			'callback' => array($this, 'get_gated_id')
		));
	}

	function get_gated_id ($request) {
		$form_id = $request->get_param( 'form_id' );
		$post_id = $request->get_param( 'post_id' );

		$gated = new NFGated();
		$id = $gated->getGatedUniqueId( $form_id, $post_id );
		return new WP_REST_Response( $id, 200 );

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

		return new WP_REST_Response($team_member);
	}

	function insights_by_user($data) {
		$nicename = $data->get_params('id')['id'];
		$per_page = $data->get_params()['posts_per_page'] ?? -1;
		$page = $data->get_params()['page'] ?? 0;
		$offset = intval($page) * $per_page;

		$user = get_user_by('slug', $nicename);


		$rawInsights = new WP_Query(array(
			'post_type' => 'bd_insight',
			'offset' => $offset,
			'posts_per_page' => $per_page,
			'paged' => true,

			'meta_query' => array(
				array(
					'key' => 'author',
					'value' => $user->ID,
					'compare' => 'IN'
				)
			)
		));
		$total = $rawInsights->max_num_pages;
		$insights = array();
		foreach($rawInsights->posts as $rawInsight){
			$fields = get_fields($rawInsight->ID);
			$insightUser = $data->get_params('id')['id'];
				foreach($fields['author'] as $a){
						$authors_meta = array();
						foreach($fields['author'] as $a2){
							$author_meta = get_fields('user_' . $a2['ID']);
							$author_data = $a2;
							$author_data['meta'] = $author_meta;
							$authors_meta[] = $author_data;
						}
						$insight_data = $rawInsight;
						$insight_data->acf = $fields;
						$insight_data->authors = $authors_meta;
						$insight_data->type = wp_get_post_terms($rawInsight->ID, 'type');
						$insight_data->topic = wp_get_post_terms($rawInsight->ID, 'topic');
						$insight_data->title = get_the_title($rawInsight->ID);
						$insights[] = $insight_data;
						continue;
				}
		}
		$headers = $rawInsights->headers;
		// $insights['headers'] = $total;
		return new WP_REST_Response(array(
				'data' =>$insights,
				'pages' => $total
		));
	}

	function events_by_user($data) {
		$nicename = $data->get_params('id')['id'];
		$user = get_user_by('slug', $nicename);


		$rawEvents = get_posts(array(
				'post_type' => 'bd_event',
				'posts_per_page' => 10,
					'paged' => true,
					'meta_query' => array(
				array(
					'key' => 'related_team_members',
					'value' => $user->ID,
					'compare' => 'IN'
				)
			)
		));

	

		$events = array();
		foreach($rawEvents as $rawEvent){
			$fields = get_fields($rawEvent->ID);
			
		
							$team = $fields['related_team_members'];
							$isOnTeam = false;
							if (is_array($team)){
								foreach($team as $member_data) {
									// $team_meta[] = $included_member['ID'];
									$included_member = get_fields('user_' . $member_data['ID']);
									$included_member['display_name'] = $member_data['display_name'];
									$included_member['ID'] = $member_data['ID'];
									$included_member['userId'] = $user->ID;

									$team_meta[] = $included_member;
									if ($member_data['ID']==$user->ID ) {
										$isOnTeam = true;
									}

								}
							}



							if ($isOnTeam) {
	
								$event = get_post($rawEvent->ID);
								$topics = wp_get_post_terms($rawEvent->ID, 'topic');
								$eventCategories = wp_get_post_terms($rawEvent->ID, 'event_category');
								$event->acf = $fields;
								$event->slug = $event->post_name;
								$event->topic = $topics;
								$event->event_category = $eventCategories;
								$events[] = array('data' => $event, 'team_meta' => $team_meta);
							}
							

			}


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
				$topics = wp_get_post_terms($case_study->ID, 'topic');
				$topicIds = array();
				foreach($topics as $topic){
					$topicIds[] = $topic->term_id;
				}
				$acf = get_fields($case_study->ID);
				$title = get_the_title($case_study->ID);
				array_push($featured_work,
					array('id' => $case_study->ID, 'acf' => $acf, 'topic' => $topicIds, 'title' => array('rendered' => $title), 'slug' => $case_study->post_name )
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
			'author_headshots',
				array(
					'get_callback' => array( $this, 'get_author_headshots' ),
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
		if ($body) {
			$content = '';
			if (!is_null($body) && $body){
				foreach($body as $block) {
					$content .= $block['text'];
				}
			}
			$wordCount = str_word_count(strip_tags($content));
			$minutes = floor($wordCount / 200);
			$minutes = $minutes . ' min';
			return new WP_REST_Response($minutes);
		}
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

	function get_author_headshots($object) {
		$authors = get_field('author');
		$headshots = array();
		if (gettype($authors) == 'array') {
			foreach($authors as $author) {
				$headshots[$author['user_nicename']] = get_field('headshot', 'user_' . $author['ID']);
			}
		} else if (isset($authors['user_nicename'])) {
			$headshots[$authors['user_nicename']] = get_field('headshot', 'user_' . $authors['ID']);
		} else {
			$headshots = null;
		}
		return $headshots;
	}

	function get_event_team_members($object) {
		$rawMembers = get_field('related_team_members');
		$members = array();
		if (is_array($rawMembers)){
			foreach($rawMembers as $member){
				$members[] = array(
					'headshot' => get_field('headshot', 'user_' . $member['ID']),
					'member' => $member
				);
			}
		}
		return new WP_REST_Response($members);
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
		register_taxonomy_for_object_type('tag', 'bd_insight');
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

			register_post_type( 'sidebarcta',
				array(
					'labels' => array(
						'name' => __( 'Sidebar CTAs' ),
						'singular_name' => __( 'Sidebar CTA' )
					),
					'public' => true,
					'show_ui' => true,
					'show_in_menu' => true,
					'has_archive' => true,
					'rewrite' => array('slug' => 'sidebarcta'),
					'show_in_rest' => true
				)
			);
	}

	function register_taxonomies() {

		//this is where you can register custom taxonomies
		$labels = array(
			 'name' => _x( 'Tags', 'taxonomy general name' ),
			 'singular_name' => _x( 'Tag', 'taxonomy singular name' ),
			 'search_items' =>  __( 'Search Tags' ),
			 'popular_items' => __( 'Popular Tags' ),
			 'all_items' => __( 'All Tags' ),
			 'parent_item' => null,
			 'parent_item_colon' => null,
			 'edit_item' => __( 'Edit Tag' ),
			 'update_item' => __( 'Update Tag' ),
			 'add_new_item' => __( 'Add New Tag' ),
			 'new_item_name' => __( 'New Tag Name' ),
			 'separate_items_with_commas' => __( 'Separate tags with commas' ),
			 'add_or_remove_items' => __( 'Add or remove tags' ),
			 'choose_from_most_used' => __( 'Choose from the most used tags' ),
			 'menu_name' => __( 'Tags' ),
		 );

		 register_taxonomy('tag','bd_insight',array(
			 'hierarchical' => false,
			 'labels' => $labels,
				'show_in_rest' => true,
				'show_ui' => true,
			 'query_var' => true,
			 'rewrite' => array( 'slug' => 'tag' ),
		 ));

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
			array('bd_case_study'),
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



	function myfoo( $text ) {
		$text .= ' bar!';
		return $text;
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

