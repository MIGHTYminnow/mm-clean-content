<?php
/**
 * The core plugin class.
 *
 * @since  1.0.0
 */
class Mm_Clean_Content {

	/**
	 * Plugin display name.
	 */
	private $plugin_display_name;

	/**
	 * Allowed HTML tags.
	 */
	private $allowed_tags;

	/**
	 * Allowed HTML attributes.
	 */
	private $allowed_attributes;

	/**
	 * The settings options.
	 *
	 * @since  1.0.0
	 */
	private $options;

	/**
	 * The Constructor.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {

		$this->options = get_option( 'mm_clean_content_options' );
		$this->plugin_display_name = __( 'Mm Clean Content', 'mm-clean-content' );
	}

	/**
	 * Initialize the plugin hooks.
	 *
	 * @since  1.0.0
	 */
	public function initialize() {

		// Load the plugin text domain.
		add_action( 'init', array( $this, 'load_text_domain' ) );

		// Set up the admin settings page.
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );

		// Register settings
		add_action( 'admin_init', array( $this, 'register_settings_and_fields' ) );

		// Enqueue the JS for AJAX calls.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );

		// Handle the PHP side of the AJAX calls.
		add_action( 'wp_ajax_mm_clean_post_type_content', array( $this, 'clean_post_type_content' ) );
		add_action( 'wp_ajax_mm_clean_content', array( $this, 'clean_specific_post_content' ) );
		add_action( 'wp_ajax_mm_clean_content_get_options', array( $this, 'get_options' ) );

		// Add action links to the posts and pages admin screens.
		add_action( 'current_screen', array( $this, 'admin_action_links' ) );

		// Add an action link to the settings page on the plugins page.
		add_filter( 'plugin_action_links_' . MM_CLEAN_CONTENT_BASENAME, array( $this, 'plugins_page_action_links' ) );
	}

	/**
	 * Load plugin text domain.
	 *
	 * @since  1.0.0
	 */
	public function load_text_domain() {

		load_plugin_textdomain( MM_CLEAN_CONTENT_SLUG, false, MM_CLEAN_CONTENT_PATH . '/languages' );
	}

	/**
	 * Create the plugin settings page.
	 *
	 * @since  1.0.0
	 */
	public function add_settings_page() {

		add_options_page(
			$this->plugin_display_name,
			$this->plugin_display_name,
			'manage_options',
			MM_CLEAN_CONTENT_SLUG,
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Set default main array of elements/attributes.
	 *
	 * @since  1.0.0
	 */
	static function set_default_values() {

		$options = get_option( 'mm_clean_content_options' );

		if ( FALSE === $options ) {

			// Use WordPress default elements and attributes array
			global $allowedposttags;

			ksort( $allowedposttags );
			$elements  = implode( ',', array_keys( $allowedposttags ) );

			foreach ( $allowedposttags as $el => $atts ) {

				if ( is_array( $atts ) ) {

					foreach( $atts as $attribute => $value ) {
						if ( $value ) {
							if ( ! @in_array( $attribute, $attributes_array ) ) {
								$attributes_array[] = $attribute;
							}
						}
					}
				}
			}

			sort( $attributes_array );
 			$attributes = implode( ',', $attributes_array );

			update_option(
				'mm_clean_content_options',
				array(
					'allowed_elements'            => $elements,
					'allowed_attributes'          => $attributes,
					'allowed_elements_attributes' => $allowedposttags,
				)
			);

		}
	}

	/**
	 * Register Admin Settings.
	 *
	 * @since  1.0.0
	 */
	public function register_settings_and_fields() {

		register_setting(
			'mm_clean_content_options',
			'mm_clean_content_options',
			array( $this, 'validate_options' )
		);

		add_settings_section(
			'mm_clean_content_options_page_settings',
			__( 'Elements and Attributes', 'mm-clean-content' ),
			array( $this, 'settings_section' ),
			'mm_clean_content_settings_section'
		);

		add_settings_field(
			'allowed_elements',
			__( 'Allowed Elements', 'mm-clean-content' ),
			array( $this, 'field_textarea' ),
			'mm_clean_content_settings_section',
			'mm_clean_content_options_page_settings',
			array(
				'id'          => 'allowed_elements',
				'description' => __( 'Enter all allowed elements', 'mm-clean-content' ),
			)
		);

		add_settings_field(
			'allowed_attributes',
			__( 'Allowed Attributes', 'mm-clean-content' ),
			array( $this, 'field_textarea' ),
			'mm_clean_content_settings_section',
			'mm_clean_content_options_page_settings',
			array(
				'id'          => 'allowed_attributes',
				'description' => __( 'Enter all allowed attributes', 'mm-clean-content' ),
			)
		);
	}

	/**
	 * Get Options values of user defined elements/attributes.
	 *
	 * @since  1.0.0
	 */
	public function get_options() {

		$options = get_option( 'mm_clean_content_options' );
		wp_send_json( $options );
	}

	/**
	 * Validate our options before saving.
	 *
	 * @since   1.0.0
	 *
	 * @param   array  $input  The options to update.
	 *
	 * @return  array          The updated options.
	 */
	public function validate_options( $input ) {

		// Sanitize.
		$options = $this->sanitize_options( $input );

		$allowed_elements_array = array();
		$allowed_attrs_array    = array();

		// Convert user defined values (csv) into array.
		$user_elements   = explode( ',', $options['allowed_elements'] );
		$user_attributes = explode( ',', $options['allowed_attributes'] );

		// Sort Arrays alphabetically.
		sort( $user_elements );
		sort( $user_attributes );

		// Build the arrays in format used by wp_kses.
		foreach( $user_attributes as $attr ) {

			if ( ! empty( $attr ) ) {
				$allowed_attrs_array[ trim( $attr ) ] = true;
			}
		}

		foreach( $user_elements as $element ) {

			if ( ! empty( $element ) ) {
				$allowed_elements_array[ trim( $element ) ] = $allowed_attrs_array;
			}
		}

		// Add options to the array.
		$options['allowed_elements']            = implode( ',', $user_elements );
		$options['allowed_attributes']          = implode( ',', $user_attributes );
		$options['allowed_elements_attributes'] = $allowed_elements_array;

		return $options;
	}

	/**
	 * Validate our options.
	 *
	 * @since   1.0.0
	 *
	 * @param   array  $input  The options to update.
	 *
	 * @return  array          The updated options.
	 */
	public function sanitize_options( $input ) {

		// Start with any existing options.
		$options = get_option( 'mm_clean_content_options' );

		// Update options on the options page.
		if ( isset( $input['allowed_elements'] ) ) {
			$options['allowed_elements'] = sanitize_text_field( $input['allowed_elements'] );
		}

		if ( isset( $input['allowed_attributes'] ) ) {
			$options['allowed_attributes'] = sanitize_text_field( $input['allowed_attributes'] );
		}

		return $options;
	}

	/**
	 * Output the settings section.
	 *
	 * @since  1.0.0
	 */
	public function settings_section() {
		return;
	}

	/**
	 * Output the plugin settings page contents.
	 *
	 * @since  1.0.0
	 */
	public function create_admin_page() {
	?>
		<div class="wrap">
			<h2><?php echo esc_html( $this->plugin_display_name ); ?></h2>
			<form method="post" action="options.php" style="margin-bottom: 40px;">
			    <?php settings_fields( 'mm_clean_content_options' ); ?>
				<?php do_settings_sections( 'mm_clean_content_settings_section' ); ?>
			    <?php submit_button(); ?>
			</form>

			<h3><?php _e( 'Clean Posts', 'mm-clean-content' ); ?></h3>
		    <table class="form-table">
		        <tr valign="top">
			        <th scope="row"><?php _e( 'Select a Post Type', 'mm-clean-content' ); ?></th>
			        <td>
        				<select id="mm-clean-post-types-select">
							<?php $post_types = $this->get_public_post_types(); ?>
							<?php foreach ( $post_types as $key => $value ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
							<?php endforeach; ?>
						</select>
			        </td>
		        </tr>
		    </table>
		    <p><input type="submit" name="submit" id="mm-clean-post-type-button" class="button button-primary" value="<?php _e( 'Clean HTML', 'mm-clean-content' ); ?>"></p>
			<p><?php _e( '<strong>Warning</strong>: This will strip certain HTML tags and attributes from <strong>all items of the post type</strong> you have selected. This process <strong>cannot be undone</strong>. Please proceed with caution.', 'mm-clean-content' ) ?></p>
			<div id="mm-clean-content-response-holder" style="margin-top: 20px;"></div>
			<div id="mm-clean-content-loading-gif" style="display: none;">
				<img src="<?php echo includes_url() . 'images/spinner.gif' ?>" />
			</div>
		</div>
	<?php
	}

	/**
	 * Enqueue the admin JS for the AJAX calls.
	 *
	 * @since  1.0.0
	 */
	public function admin_enqueue( $hook ) {

		// Only enqueue on our settings page and the posts and pages edit screens
		if ( 'settings_page_' . MM_CLEAN_CONTENT_SLUG == $hook || 'edit.php' == $hook ) {

			wp_enqueue_script(
				MM_CLEAN_CONTENT_SLUG . '-admin-js',
				MM_CLEAN_CONTENT_URL . '/js/admin.js',
				array( 'jquery' )
			);

			wp_localize_script(
				MM_CLEAN_CONTENT_SLUG . '-admin-js',
				'ajax_object',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' )
				)
			);

			wp_localize_script(
				MM_CLEAN_CONTENT_SLUG . '-admin-js',
				'mm_clean_content_messages',
				array(
					'confirm_post_type'   => esc_html__( 'You are about to clean the content for the post type:', 'mm-clean-content' ),
					'confirm_post'        => esc_html__( 'You are about to clean post:', 'mm-clean-content' ),
					'confirm_elements'    => esc_html__( 'Only these elements will be allowed to remain in the content:', 'mm-clean-content' ),
					'confirm_attributes'  => esc_html__( 'Only these attributes will be allowed to remain:', 'mm-clean-content' ),
					'confirm_warning'     => esc_html__( 'This action cannot be undone, and it is HIGHLY recommended that you have a backup of the data you are about to clean.', 'mm-clean-content' ),
					'confirm_final'       => esc_html__( 'Are you sure you want to proceed?', 'mm-clean-content' ),
				)
			);
		}
	}

	/**
	 * Add our action link to the all posts and all pages edit screens
	 *
	 * @since  1.0.0
	 */
	public function admin_action_links( $current_screen ) {

		if ( 'edit-post' == $current_screen->id ) {
			add_filter( 'post_row_actions', array( $this, 'do_admin_action_links' ), 10, 2 );
		}

		if ( 'edit-page' == $current_screen->id ) {
			add_filter( 'page_row_actions', array( $this, 'do_admin_action_links' ), 10, 2 );
		}
	}

	/**
	 * Output our action link.
	 *
	 * @since   1.0.0
	 *
	 * @return  array  An array of all the action links.
	 */
	public function do_admin_action_links( $actions, $post ) {

		$actions['clean_page_content'] = '<span class="mm-clean-content-button"><a data-post-id="' . $post->ID . '" href="javascript:void(0);" class="mm-clean-content-link">' . __( 'Clean HTML', 'mm-clean-content' ) . '</a><span class="mm-clean-content-loading-gif" style="display: none; position: relative; margin-left: 6px;"><img src="' . includes_url() . 'images/spinner.gif" style="position: absolute; top: -1px;" /></span></span>';

		return $actions;
	}

	/**
	 * Add an action link to the settings page.
	 *
	 * @since  1.0.0
	 */
	public function plugins_page_action_links( $links ) {

	   $links[] = '<a href="'. get_admin_url( null, 'options-general.php?page=mm-clean-content' ) .'">' . __( 'Settings', 'mm-clean-content' ) . '</a>';

	   return $links;
	}

	/**
	 * Clean the content for selected post type.
	 *
	 * @since  1.0.0
	 */
	public function clean_post_type_content() {

		if ( ! empty( $_POST['post_type'] ) ) {

			$args = array(
				'post_type'      => $_POST['post_type'],
				'posts_per_page' => -1
			);
			$items = get_posts( $args );

			// If we don't have a proper post type label, use the post type slug.
			$label = ( empty( $_POST['post_type_label'] ) ) ? $_POST['post_type'] : $_POST['post_type_label'];

			$this->clean_multiple_items( $items, $label );

			echo '<div class="notice notice-info"><p>' . __( 'All entries have been cleaned.', 'clean-content' ) . '</p>';
		}

		wp_die();
	}

	/**
	 * Clean the content for a specific post or page.
	 *
	 * @since  1.0.0
	 */
	public function clean_specific_post_content() {

		if ( ! empty( $_POST['post_id'] ) ) {

			// Sanitize the passed in post_id.
			$post_id = (int) $_POST['post_id'];

			// Do the cleaning.
			$this->clean_one_item( $post_id );

			// Return a nice message.
			printf(
				'<span style="color: green;">%s %s</span>',
				__( 'All Clean', 'mm-clean-content' ),
				'<span class="dashicons dashicons-yes"></span>'
			);
		}

		wp_die();
	}

	/**
	 * Clean the content for multiple items.
	 *
	 * @since  1.0.0
	 */
	public function clean_multiple_items( $posts, $post_type ) {

		foreach ( $posts as $post ) {

			// Grab the old content.
			$content = $post->post_content;

			// Do the cleaning.
			$clean_content = $this->clean_html( $content );

			// Update the post content in the database.
			$current_post = array(
				'ID'			=> $post->ID,
				'post_content'	=> $clean_content
			);
			wp_update_post( $current_post );

			// Return a nice message.
			printf(
				'<div class="updated"><p>%s <strong>%s</strong> %s</p></div>',
				$post_type,
				$post->post_title,
				__( 'has been cleaned', 'mm-clean-content' )
			);
		}
	}

	/**
	 * Clean the content for one item.
	 *
	 * @since  1.0.0
	 */
	public function clean_one_item( $post_id = 0 ) {

		// Bail if we don't have a valid post ID.
		if ( 0 == $post_id ) {
			return;
		}

		// Get the post object and content.
		$post = get_post( $post_id );
		$content = $post->post_content;

		// Clean the bad elements out of the content.
		$clean_content = $this->clean_html( $content );

		// Update the post content in the database.
		$current_post = array(
			'ID'           => $post_id,
			'post_content' => $clean_content
		);

		wp_update_post( $current_post );
	}

	/**
	 * Clean and return an HTML string.
	 *
	 * @since  1.0.0
	 */
	public function clean_html( $string, $allowed_elements_attributes = NULL ) {

		if ( ! $allowed_elements_attributes && ! is_array( $allowed_elements_attributes ) ) {
			$allowed_elements_attributes = $this->options['allowed_elements_attributes'];
		}

		$allowed_elements_attributes = apply_filters( 'mm_clean_content_allowed_html_elements_attributes', $allowed_elements_attributes );

		$string = wp_kses( $string, $allowed_elements_attributes );

		return $string;
	}

	/**
	 * Return an array of all public post types.
	 *
	 * @since   1.0.0
	 *
	 * @param   string  $context  The context to pass to our filter.
	 *
	 * @return  array             The array of formatted post types.
	 */
	public function get_public_post_types( $context = '' ) {

		$post_type_args = array(
				'public'   => true,
				'_builtin' => false
		);

		$custom_post_types = get_post_types( $post_type_args, 'objects', 'and' );

		$formatted_cpts = array();

		foreach( $custom_post_types as $post_type ) {
			$formatted_cpts[ $post_type->name ] = $post_type->labels->singular_name;
		}

		// Manually add 'post' and 'page' types.
		$default_post_types = array(
				'post' => __( 'Post', 'mm-components' ),
				'page' => __( 'Page', 'mm-components' ),
		);

		$post_types = $default_post_types + $formatted_cpts;

		return apply_filters( 'mm_clean_content_post_types', $post_types, $context );
	}

	/**
	 * Output a textarea field.
	 *
	 * @since  1.0.0
	 */
	public function field_textarea( $args ) {
		// Bail if we don't have an ID.
		if ( empty( $args['id'] ) ) {
			return;
		}
		$option_id          = 'mm-clean-content-options-' . str_replace( '_', '-', $args['id'] );
		$option_key         = 'mm_clean_content_options[' . $args['id'] . ']';
		$option_value       = ( ! empty( $this->options[ $args['id'] ] ) ) ? $this->options[ $args['id'] ] : '';
		$option_description = ( ! empty( $args['description'] ) ) ? '<br /><span class="description">' . wp_kses_post( $args['description'] ) . '</span>' : '';
		printf(
			'<textarea class="regular-text" rows="10" cols="80" id="%s" name="%s">%s</textarea>%s',
			esc_attr( $option_id ),
			esc_attr( $option_key ),
			esc_attr( $option_value ),
			$option_description
		);
	}
}