<?php

class WSUWP_People_Display {
	/**
	 * @var WSUWP_People_Display
	 */
	private static $instance;

	/**
	 * Tracks the version number of the plugin for script enqueues.
	 *
	 * @since 0.0.1
	 *
	 * @var string
	 */
	public $version = '0.0.1';

	/**
	 * Tracks the people query variable.
	 *
	 * @since 0.0.1
	 *
	 * @var string
	 */
	public static $people_query_var = 'people';

	/**
	 * Tracks the person query variable.
	 *
	 * @since 0.0.1
	 *
	 * @var string
	 */
	public static $person_query_var = 'person';

	/**
	 * Maintain and return the one instance. Initiate hooks when called the first time.
	 *
	 * @since 0.0.1
	 *
	 * @return \WSUWP_People_Display
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSUWP_People_Display();
			self::$instance->setup_hooks();
		}
		return self::$instance;
	}

	/**
	 * Setup hooks to include.
	 *
	 * @since 0.0.1
	 */
	public function setup_hooks() {
		require_once( dirname( __FILE__ ) . '/class-people-display-settings.php' );

		add_action( 'init', 'WSUWP_People_Display_Settings' );

		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_action( 'init', array( $this, 'rewrite_rules' ) );
		add_filter( 'the_posts', array( $this, 'placeholder_page' ) );
		add_filter( 'template_include', array( $this, 'template_include' ) );
	}

	/**
	 * Check if the query is for a people view.
	 *
	 * @since 0.0.1
	 *
	 * @return boolean
	 */
	public function people_query() {
		return get_query_var( self::$people_query_var );
	}

	/**
	 * Check if the query is for an individual person view.
	 *
	 * @since 0.0.1
	 *
	 * @param boolean
	 */
	public function person_query() {
		return get_query_var( self::$person_query_var );
	}

	/**
	 * Make WordPress aware of the query variables we'll use in our rewrite rules.
	 *
	 * @since 0.0.1
	 *
	 * @param array $query_vars Whitelisted query variables.
	 *
	 * @return array Modified list of query_vars.
	 */
	public function query_vars( $query_vars ) {
		$query_vars[] = self::$people_query_var;
		$query_vars[] = self::$person_query_var;
		return $query_vars;
	}

	/**
	 * Add rewrite rules for handling people and person views.
	 *
	 * @since 0.0.1
	 */
	public function rewrite_rules() {
		$options = get_option( 'wsu_people_display' );
		$slug = ( isset( $options['slug'] ) && '' !== $options['slug'] ) ? $options['slug'] : 'people';

		add_rewrite_rule(
			$slug . '/([^/]*)/?$',
			'index.php?' . self::$person_query_var . '=$matches[1]',
			'top'
		);

		add_rewrite_rule(
			$slug,
			'index.php?' . self::$people_query_var . '=1',
			'top'
		);
	}

	/**
	 * Catch the request and return a placeholder post.
	 *
	 * Sourced https://coderwall.com/p/fwea7g/create-wordpress-virtual-page-on-the-fly.
	 *
	 * @since 0.0.1
	 *
	 * @param array $posts The array of retrieved posts.
	 *
	 * @return array Modified array containing our placeholder post.
	 */
	public function placeholder_page( $posts ) {
		if ( count( $posts ) === 0 && ( $this->people_query() || $this->person_query() ) ) {
			$post = array(
				'ID' => 0,
				'post_author' => 0,
				'post_date' => 0,
				'post_date_gmt' => 0,
				'post_content' => '',
				'post_title' => '',
				'post_excerpt' => '',
				'post_status' => 'publish',
				'comment_status' => 'closed',
				'ping_status' => '',
				'post_password' => '',
				'post_name' => '',
				'to_ping' => '',
				'pinged' => '',
				'guid' => '',
				'post_type' => 'page',
				'comment_count' => 0,
				'is_404' => false,
				'is_page' => true,
				'is_single' => false,
				'is_archive' => false,
				'is_tax' => false,
			);

			$post = (object) $post;
			$posts = null;
			$posts[] = $post;
		}

		return $posts;
	}

	/**
	 * Assign templates to people and person pages.
	 *
	 * @param string $template The path of the template to include.
	 *
	 * @return string The path of the template to include.
	 */
	public function template_include( $template ) {
		if ( $this->people_query() || $this->person_query() ) {
			$options = get_option( 'wsu_people_display' );
			$template_file = ( isset( $options['template'] ) && '' !== $options['template'] ) ? $options['template'] : 'page.php';

			$template = trailingslashit( get_template_directory() ) . $template_file;

			add_filter( 'the_content', array( $this, 'content' ) );
			add_filter( 'spine_get_title', array( $this, 'title' ) );
			add_filter( 'body_class', array( $this, 'body_class' ) );
		}

		return $template;
	}

	/**
	 * Adjust the query for people page requests.
	 *
	 * @since 0.0.1
	 *
	 * @param string $content Current post content.
	 *
	 * @return string Modified content.
	 */
	public function content( $content ) {
		ob_start();

		if ( $this->people_query() ) {
			include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/people.php';
		}

		if ( $this->person_query() ) {
			include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/person.php';
		}

		$content = ob_get_clean();

		return $content;
	}

	/**
	 * Prepend the page title according to the current view.
	 *
	 * @since 0.0.1
	 *
	 * @param string $title Current page title.
	 *
	 * @return string Modified title.
	 */
	public function title( $title ) {
		if ( $this->people_query() ) {
			$title = 'People | ' . $title;
		}

		if ( $this->person_query() ) {
			$request_url = 'https://people.wsu.edu/wp-json/wp/v2/people/';
			$request_url = add_query_arg( array( 'wsu_nid' => esc_html( self::person_query() ) ), $request_url );
			$response = wp_remote_get( $request_url );

			if ( is_wp_error( $response ) ) {
				return $title;
			}

			$data = wp_remote_retrieve_body( $response );

			if ( empty( $data ) ) {
				return $title;
			}

			$person = json_decode( $data );

			if ( empty( $person ) ) {
				return $title;
			}

			$title = esc_html( $person[0]->title->rendered ) . ' | ' . $title;
		}

		return $title;
	}

	/**
	 * Add a body class for the current view.
	 *
	 * @since 0.0.1
	 *
	 * @param array $classes Current array body classes.
	 *
	 * @return array Modified array body classes.
	 */
	public function body_class( $classes ) {
		if ( $this->people_query() ) {
			$classes[] = 'wsu-people';
		}

		if ( $this->person_query() ) {
			$classes[] = 'wsu-person';
		}

		return $classes;
	}
}
