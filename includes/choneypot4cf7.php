<?php
/**
 * Honeypot form tag for Classic Honeypot for CF7.
 *
 * Classic Honeypot for CF7 is a fork of "Honeypot for Contact Form 7" 2.1.1
 * by Nocean and contributors, released under the GPLv2 or later.
 * This file was modified by Nilo Vélez in October 2026. See the changelog in
 * readme.txt and the git history for details of every change.
 *
 * @package Classic_Honeypot_For_CF7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the [honeypot] form tag with Contact Form 7.
 */
function choneypot4cf7_add_form_tag() {
	$config = choneypot4cf7_get_config();

	wpcf7_add_form_tag(
		'honeypot',
		'choneypot4cf7_form_tag_handler',
		array(
			'name-attr'    => true,
			'do-not-store' => ! $config['store_honeypot'],
			'not-for-mail' => true,
		)
	);
}
add_action( 'wpcf7_init', 'choneypot4cf7_add_form_tag', 10 );

/**
 * Read a true/false option of a form tag, such as "nomessage:true".
 *
 * @param WPCF7_FormTag $tag      Form tag.
 * @param string        $option   Option name.
 * @param bool          $fallback Value to use when the tag does not set the option.
 * @return bool
 */
function choneypot4cf7_get_tag_flag( $tag, $option, $fallback ) {
	$value = $tag->get_option( $option, '', true );

	if ( 'true' === $value ) {
		return true;
	}

	if ( 'false' === $value ) {
		return false;
	}

	return $fallback;
}

/**
 * Get the settings of a honeypot field: the tag options, falling back to
 * the global settings.
 *
 * The spam check uses this too, so the time check threshold always comes
 * from the server and never from the submitted form.
 *
 * @param WPCF7_FormTag $tag Form tag.
 * @return array
 */
function choneypot4cf7_get_tag_settings( $tag ) {
	$config = choneypot4cf7_get_config();

	$timecheck_value = absint( $tag->get_option( 'timecheck_value', 'int', true ) );
	if ( $timecheck_value < 1 ) {
		$timecheck_value = absint( $config['timecheck_value'] );
	}

	return array(
		'validautocomplete' => choneypot4cf7_get_tag_flag( $tag, 'validautocomplete', $config['w3c_valid_autocomplete'] ),
		'move_inline_css'   => choneypot4cf7_get_tag_flag( $tag, 'move-inline-css', $config['move_inline_css'] ),
		'nomessage'         => choneypot4cf7_get_tag_flag( $tag, 'nomessage', $config['nomessage'] ),
		'timecheck_enabled' => choneypot4cf7_get_tag_flag( $tag, 'timecheck_enabled', $config['timecheck_enabled'] ),
		'timecheck_value'   => $timecheck_value,
	);
}

/**
 * Generate the HTML of a [honeypot] form tag.
 *
 * @param WPCF7_FormTag $tag Form tag.
 * @return string
 */
function choneypot4cf7_form_tag_handler( $tag ) {
	if ( empty( $tag->name ) ) {
		return '';
	}

	$config   = choneypot4cf7_get_config();
	$settings = choneypot4cf7_get_tag_settings( $tag );

	$placeholder = (string) reset( $tag->values );
	if ( '' === $placeholder ) {
		$placeholder = $config['placeholder'];
	}

	$accessibility_message = $config['accessibility_message'];
	if ( '' === $accessibility_message ) {
		$accessibility_message = __( 'Please leave this field empty.', 'classic-honeypot-for-cf7' );
	}

	// The wpcf7_honeypot_* filters keep the names of Honeypot for Contact Form 7,
	// so existing customizations keep working after switching plugins.
	$atts = array(
		'class'             => $tag->get_class_option( wpcf7_form_controls_class( 'text' ) ),
		'id'                => $tag->get_option( 'id', 'id', true ),
		'wrapper_id'        => $tag->get_option( 'wrapper-id', 'id', true ),
		'placeholder'       => $placeholder,
		'message'           => apply_filters( 'wpcf7_honeypot_accessibility_message', $accessibility_message ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Kept for compatibility.
		'name'              => $tag->name,
		'type'              => $tag->type,
		'validautocomplete' => $settings['validautocomplete'],
		'move_inline_css'   => $settings['move_inline_css'],
		'nomessage'         => $settings['nomessage'],
		'timecheck_enabled' => $settings['timecheck_enabled'],
		'timecheck_value'   => $settings['timecheck_value'],
		'validation_error'  => wpcf7_get_validation_error( $tag->name ),
		'css'               => apply_filters( 'wpcf7_honeypot_container_css', 'display:none !important; visibility:hidden !important;' ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Kept for compatibility.
	);

	$unique_id = uniqid( 'wpcf7-' );

	$wrapper_id = $atts['wrapper_id'];
	if ( empty( $wrapper_id ) ) {
		$wrapper_id = $unique_id . '-wrapper';
	}

	$input_id = $atts['id'];
	if ( empty( $input_id ) ) {
		$input_id = $unique_id . '-field';
	}

	// "new-password" stops browsers from autofilling the field better than
	// the standard (but less effective) "off" value.
	$autocomplete = $atts['validautocomplete'] ? 'off' : 'new-password';

	// Hide the field with inline CSS, or move that CSS to the page footer.
	if ( $atts['move_inline_css'] ) {
		$handle = $unique_id . '-inline';
		wp_register_style( $handle, false, array(), CHONEYPOT4CF7_VERSION );
		wp_enqueue_style( $handle );
		wp_add_inline_style( $handle, '#' . $wrapper_id . ' {' . $atts['css'] . '}' );
		$style_attribute = '';
	} else {
		$style_attribute = ' style="' . esc_attr( $atts['css'] ) . '"';
	}

	$html = '<span id="' . esc_attr( $wrapper_id ) . '" class="wpcf7-form-control-wrap ' . esc_attr( $atts['name'] ) . '-wrap"' . $style_attribute . '>';

	if ( $atts['timecheck_enabled'] ) {
		$html .= '<input type="hidden" name="' . esc_attr( $atts['name'] ) . '-time-start" value="' . esc_attr( time() ) . '">';
	}

	if ( ! $atts['nomessage'] ) {
		$html .= '<label for="' . esc_attr( $input_id ) . '" class="hp-message">' . esc_html( $atts['message'] ) . '</label>';
	}

	$html .= '<input id="' . esc_attr( $input_id ) . '"';
	if ( '' !== $atts['placeholder'] ) {
		$html .= ' placeholder="' . esc_attr( $atts['placeholder'] ) . '"';
	}
	$html .= ' class="' . esc_attr( $atts['class'] ) . '" type="text" name="' . esc_attr( $atts['name'] ) . '" value="" size="40" tabindex="-1" autocomplete="' . esc_attr( $autocomplete ) . '" />';

	// The validation error HTML comes from Contact Form 7, already escaped.
	$html .= $atts['validation_error'] . '</span>';

	// Hook for filtering finished Honeypot form element. The name is kept from
	// Honeypot for Contact Form 7 for compatibility.
	return apply_filters( 'wpcf7_honeypot_html_output', $html, $atts ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Kept for compatibility.
}

/**
 * Flag a submission as spam if a honeypot field was filled in, or if the
 * form was submitted faster than the time check allows.
 *
 * Contact Form 7 verifies the request before running this filter.
 *
 * @param bool             $spam       Whether the submission is already spam.
 * @param WPCF7_Submission $submission Current submission.
 * @return bool
 */
function choneypot4cf7_spam_check( $spam, $submission ) {
	if ( $spam || ! $submission ) {
		return $spam;
	}

	$contact_form = $submission->get_contact_form();
	if ( ! $contact_form ) {
		return $spam;
	}

	$honeypot_tags = $contact_form->scan_form_tags( array( 'type' => 'honeypot' ) );

	foreach ( $honeypot_tags as $tag ) {
		if ( empty( $tag->name ) ) {
			continue;
		}

		$settings = choneypot4cf7_get_tag_settings( $tag );

		// SPAM CHECK #1: Submission speed. A real browser always sends the
		// start time, so a missing value means a bot built the request.
		if ( $settings['timecheck_enabled'] ) {
			$start_field = $tag->name . '-time-start';

			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified by Contact Form 7.
			if ( ! isset( $_POST[ $start_field ] ) ) {
				$submission->add_spam_log(
					array(
						'agent'  => 'honeypot',
						'reason' => sprintf(
							/* translators: %s: Honeypot field name. */
							__( 'Honeypot time check field is missing. Field ID = %s', 'classic-honeypot-for-cf7' ),
							$tag->name
						),
					)
				);

				return true;
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified by Contact Form 7.
			$start_time = absint( wp_unslash( $_POST[ $start_field ] ) );

			// A start time in the future can only come from a forged request.
			if ( $start_time > time() ) {
				$submission->add_spam_log(
					array(
						'agent'  => 'honeypot',
						'reason' => sprintf(
							/* translators: %s: Honeypot field name. */
							__( 'Honeypot detected a start time in the future. Field ID = %s', 'classic-honeypot-for-cf7' ),
							$tag->name
						),
					)
				);

				return true;
			}

			$elapsed = time() - $start_time;

			if ( $elapsed < $settings['timecheck_value'] ) {
				$submission->add_spam_log(
					array(
						'agent'  => 'honeypot',
						'reason' => sprintf(
							/* translators: 1: Seconds taken to submit the form. 2: Minimum seconds required. 3: Honeypot field name. */
							__( 'Honeypot detected form submitted too fast (%1$s seconds, minimum %2$s). Field ID = %3$s', 'classic-honeypot-for-cf7' ),
							$elapsed,
							$settings['timecheck_value'],
							$tag->name
						),
					)
				);

				return true;
			}
		}

		// SPAM CHECK #2: The honeypot itself. Any value, even "0", means a
		// bot filled in the field.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified by Contact Form 7.
		if ( isset( $_POST[ $tag->name ] ) && '' !== $_POST[ $tag->name ] ) {
			$submission->add_spam_log(
				array(
					'agent'  => 'honeypot',
					'reason' => sprintf(
						/* translators: %s: Honeypot field name. */
						__( 'Something is stuck in the honey. Field ID = %s', 'classic-honeypot-for-cf7' ),
						$tag->name
					),
				)
			);

			return true;
		}
	}

	return $spam;
}
add_filter( 'wpcf7_spam', 'choneypot4cf7_spam_check', 10, 2 );

/**
 * Add the "Honeypot" button to the Contact Form 7 form editor.
 */
function choneypot4cf7_add_tag_generator() {
	$tag_generator = WPCF7_TagGenerator::get_instance();

	$tag_generator->add(
		'honeypot',
		__( 'Honeypot', 'classic-honeypot-for-cf7' ),
		'choneypot4cf7_tag_generator',
		array( 'version' => '2' )
	);
}
add_action( 'wpcf7_admin_init', 'choneypot4cf7_add_tag_generator', 50, 0 );

/**
 * Render the honeypot form-tag generator dialog.
 *
 * Uses the version 2 tag generator API of Contact Form 7 6.0. Each input
 * with data-tag-part="option" adds its data-tag-option to the tag: checkboxes
 * add it when checked, and options ending in ":" get the input value.
 *
 * @param WPCF7_ContactForm $contact_form Current contact form.
 * @param array             $options      Tag generator options.
 */
function choneypot4cf7_tag_generator( $contact_form, $options ) {
	$tgg = new WPCF7_TagGeneratorGenerator( $options['content'] );

	$settings_url  = add_query_arg( 'page', 'choneypot4cf7', admin_url( 'admin.php' ) );
	$settings_link = '<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Honeypot settings', 'classic-honeypot-for-cf7' ) . '</a>';
	?>
	<header class="description-box">
		<h3><?php esc_html_e( 'Honeypot form-tag generator', 'classic-honeypot-for-cf7' ); ?></h3>
		<p>
			<?php
			echo wp_kses(
				sprintf(
					/* translators: %s: Link to the honeypot settings page. */
					__( 'Generate a form-tag for a spam-stopping honeypot field. Check out %s for more settings/info.', 'classic-honeypot-for-cf7' ),
					$settings_link
				),
				array( 'a' => array( 'href' => array() ) )
			);
			?>
		</p>
	</header>

	<div class="control-box">
		<?php
		$tgg->print(
			'field_type',
			array(
				'select_options' => array(
					'honeypot' => __( 'Honeypot', 'classic-honeypot-for-cf7' ),
				),
			)
		);

		$tgg->print( 'field_name' );
		?>
		<p class="description"><?php esc_html_e( 'For better security, change "honeypot" to something more appealing to a bot, such as text including "email" or "website".', 'classic-honeypot-for-cf7' ); ?></p>

		<?php
		$tgg->print( 'id_attr' );

		$tgg->print( 'class_attr' );
		?>

		<fieldset>
			<legend id="<?php echo esc_attr( $tgg->ref( 'wrapper-id-legend' ) ); ?>"><?php esc_html_e( 'Wrapper ID', 'classic-honeypot-for-cf7' ); ?></legend>
			<input type="text" data-tag-part="option" data-tag-option="wrapper-id:" pattern="[A-Za-z][A-Za-z0-9_\-]*" aria-labelledby="<?php echo esc_attr( $tgg->ref( 'wrapper-id-legend' ) ); ?>" />
		</fieldset>

		<fieldset>
			<legend id="<?php echo esc_attr( $tgg->ref( 'placeholder-legend' ) ); ?>"><?php esc_html_e( 'Placeholder', 'classic-honeypot-for-cf7' ); ?></legend>
			<input type="text" data-tag-part="value" aria-labelledby="<?php echo esc_attr( $tgg->ref( 'placeholder-legend' ) ); ?>" />
		</fieldset>

		<fieldset>
			<legend><?php esc_html_e( 'Optional settings', 'classic-honeypot-for-cf7' ); ?></legend>
			<label>
				<input type="checkbox" data-tag-part="option" data-tag-option="validautocomplete:true" />
				<?php esc_html_e( 'Use Standard Autocomplete Value', 'classic-honeypot-for-cf7' ); ?>
			</label>
			<br />
			<label>
				<input type="checkbox" data-tag-part="option" data-tag-option="move-inline-css:true" />
				<?php esc_html_e( 'Move Inline CSS', 'classic-honeypot-for-cf7' ); ?>
			</label>
			<br />
			<label>
				<input type="checkbox" data-tag-part="option" data-tag-option="nomessage:true" />
				<?php esc_html_e( 'Disable Accessibility Label', 'classic-honeypot-for-cf7' ); ?>
			</label>
		</fieldset>

		<fieldset>
			<legend id="<?php echo esc_attr( $tgg->ref( 'timecheck-legend' ) ); ?>"><?php esc_html_e( 'Time Check', 'classic-honeypot-for-cf7' ); ?></legend>
			<label>
				<input type="checkbox" data-tag-part="option" data-tag-option="timecheck_enabled:true" />
				<?php esc_html_e( 'Enable Time Check', 'classic-honeypot-for-cf7' ); ?>
			</label>
			<br />
			<label>
				<input type="number" min="1" step="1" placeholder="4" data-tag-part="option" data-tag-option="timecheck_value:" />
				<?php esc_html_e( 'seconds', 'classic-honeypot-for-cf7' ); ?>
			</label>
		</fieldset>
	</div>

	<footer class="insert-box">
		<?php $tgg->print( 'insert_box_content' ); ?>
	</footer>
	<?php
}
