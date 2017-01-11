<?php
// Retrieve people from people.wsu.edu.
$request_url = 'https://people.wsu.edu/wp-json/wp/v2/people/';

$response = wp_remote_get( $request_url );

if ( is_wp_error( $response ) ) {
	return '<!-- ' . sanitize_text_field( $response->get_error_message() ) . ' -->';
}

$data = wp_remote_retrieve_body( $response );

if ( empty( $data ) ) {
	return '<!-- empty -->';
}

$people = json_decode( $data );

if ( empty( $people ) ) {
	return '<!-- empty -->';
}

$options = get_option( 'wsu_people_display' );

// Layout options to come - table, grid, etc.
$layout = ( isset( $options['layout'] ) && '' !== $options['layout'] ) ? $options['layout'] : 'table';

// $base_url is used to create the link to individual profiles in templates/person.php
$slug = ( isset( $options['directory_slug'] ) && '' !== $options['directory_slug'] ) ? $options['directory_slug'] : 'people';
$base_url = trailingslashit( trailingslashit( get_home_url() ) . $slug );
?>
<div class="wsu-people-wrapper<?php echo esc_html( ' ' . $layout ); ?>">

	<div class="wsu-people">
	<?php
	foreach ( $people as $person ) {
		include dirname( __FILE__ ) . '/person.php';
	}
	?>
	</div>

</div>
<?php
