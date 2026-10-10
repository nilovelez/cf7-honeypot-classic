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

	$atts = array(
		'class'             => $tag->get_class_option( wpcf7_form_controls_class( 'text' ) ),
		'id'                => $tag->get_option( 'id', 'id', true ),
		'wrapper_id'        => $tag->get_option( 'wrapper-id', 'id', true ),
		'placeholder'       => $placeholder,
		'message'           => apply_filters( 'wpcf7_honeypot_accessibility_message', $accessibility_message ),
		'name'              => $tag->name,
		'type'              => $tag->type,
		'validautocomplete' => $settings['validautocomplete'],
		'move_inline_css'   => $settings['move_inline_css'],
		'nomessage'         => $settings['nomessage'],
		'timecheck_enabled' => $settings['timecheck_enabled'],
		'timecheck_value'   => $settings['timecheck_value'],
		'validation_error'  => wpcf7_get_validation_error( $tag->name ),
		'css'               => apply_filters( 'wpcf7_honeypot_container_css', 'display:none !important; visibility:hidden !important;' ),
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

	// Hook for filtering finished Honeypot form element.
	return apply_filters( 'wpcf7_honeypot_html_output', $html, $atts );
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
			$elapsed    = time() - $start_time;

			if ( $elapsed < $settings['timecheck_value'] ) {
				$submission->add_spam_log(
					array(
						'agent'  => 'honeypot',
						'reason' => sprintf(
							/* translators: 1: Seconds taken to submit the form. 2: Honeypot field name. */
							__( 'Honeypot detected form submitted too fast (%1$s seconds). Field ID = %2$s', 'classic-honeypot-for-cf7' ),
							$elapsed,
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
 * 
 * Tag generator & handler
 * 		Adds Honeypot to the CF7 form editor
 * 
 */
add_action( 'wpcf7_admin_init', 'choneypot4cf7_generate_form_tag', 10, 0 );

function choneypot4cf7_generate_form_tag() {
	$tag_generator = WPCF7_TagGenerator::get_instance();
	$tag_generator->add( 'honeypot', __( 'Honeypot', 'classic-honeypot-for-cf7' ), 'choneypot4cf7_form_tag_generator' );
}

function choneypot4cf7_form_tag_generator( $contact_form, $args = '' ) {
	$args = wp_parse_args( $args, array() );
	$description = __( 'Generate a form-tag for a spam-stopping honeypot field. For more details, see %s.', 'classic-honeypot-for-cf7' );
	$desc_link = '<a href="https://wordpress.org/plugins/classic-honeypot-for-cf7/" target="_blank">' . __( 'Classic Honeypot for CF7', 'classic-honeypot-for-cf7' ) . '</a>';
	?>
	<div class="control-box">
		<fieldset>
			<legend>
				<?php 
				$honeypotcf7_config_url = esc_url( add_query_arg('page','choneypot4cf7',get_admin_url() . 'admin.php') );
				$honeypotcf7_settings_link = "<a href='$honeypotcf7_config_url'>" . __( 'Honeypot Settings', 'classic-honeypot-for-cf7' ) . '</a>';
				printf(
					/* translators: %s: Link to Honeypot settings page */
					esc_html( __( 'Generate a form-tag for a spam-stopping honeypot field. Check out %s for more settings/info.', 'classic-honeypot-for-cf7' ) ),
					$honeypotcf7_settings_link
				); 
				?>
			</legend>

			<table class="form-table form-table--honeypotcf7"><tbody>
				<tr>
					<th style="width:50%;" scope="row">
						<label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php esc_html_e( 'Name', 'classic-honeypot-for-cf7' ); ?></label>
					</th>
					<td>
						<input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /><br>
					</td>
				</tr>
				<tr>
					<td colspan="2"><em><?php esc_html_e( 'For better security, change "honeypot" to something more appealing to a bot, such as text including "email" or "website".', 'classic-honeypot-for-cf7' ); ?></em></td>
				</tr>

				<tr style="background:#efefef;">
					<td colspan="2" style="text-transform:uppercase;text-align:center;font-weight:bold;padding-top:5px;padding-bottom:5px;">
						<?php esc_html_e( 'Optional Settings', 'classic-honeypot-for-cf7' ); ?>
					</td>
				</tr>

				<tr>
					<th style="width:50%;" scope="row">
						<label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php esc_html_e( 'ID', 'classic-honeypot-for-cf7' ); ?></label>
					</th>
					<td>
						<input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" />
					</td>
				</tr>

				<tr>
					<th style="width:50%;" scope="row">
						<label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php esc_html_e( 'Class', 'classic-honeypot-for-cf7' ); ?></label>
					</th>
					<td>
						<input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" />
					</td>
				</tr>

				<tr>
					<th style="width:50%;" scope="row">
						<label for="<?php echo esc_attr( $args['content'] . '-wrapper-id' ); ?>"><?php esc_html_e( 'Wrapper ID', 'classic-honeypot-for-cf7' ); ?></label>
					</th>
					<td>
						<input type="text" name="wrapper-id" class="wrapper-id-value oneline option" id="<?php echo esc_attr( $args['content'] . '-wrapper-id' ); ?>" /><br>
					</td>
				</tr>

				<tr>
					<th style="width:50%;" scope="row">
						<label for="<?php echo esc_attr( $args['content'] . '-values' ); ?>"><?php echo esc_html( __( 'Placeholder', 'classic-honeypot-for-cf7' ) ); ?></label>
					</th>
					<td>
						<input type="text" name="values" class="oneline" id="<?php echo esc_attr( $args['content'] . '-values' ); ?>" />
					</td>
				</tr>

				<tr>
					<th style="width:50%;" scope="row">
						<label for="<?php echo esc_attr( $args['content'] . '-validautocomplete' ); ?>"><?php esc_html_e( 'Use Standard Autocomplete Value', 'classic-honeypot-for-cf7' ); ?></label>
					</th>
					<td>
						<input type="checkbox" name="validautocomplete:true" id="<?php echo esc_attr( $args['content'] . '-validautocomplete' ); ?>" class="validautocompletevalue option" />
					</td>
				</tr>

				<tr>
					<th style="width:50%;" scope="row">
						<label for="<?php echo esc_attr( $args['content'] . '-move-inline-css' ); ?>"><?php esc_html_e( 'Move inline CSS', 'classic-honeypot-for-cf7' ); ?></label>
					</th>
					<td>
						<input type="checkbox" name="move-inline-css:true" id="<?php echo esc_attr( $args['content'] . '-move-inline-css' ); ?>" class="move-inline-css-value option" />
					</td>
				</tr>

				<tr>
					<th style="width:50%;" scope="row">
						<label for="<?php echo esc_attr( $args['content'] . '-nomessage' ); ?>"><?php esc_html_e( 'Disable Accessibility Label', 'classic-honeypot-for-cf7' ); ?></label>
					</th>
					<td>
						<input type="checkbox" name="nomessage:true" id="<?php echo esc_attr( $args['content'] . '-nomessage' ); ?>" class="messagekillvalue option" />
					</td>
				</tr>

				<tr>
					<th style="width:50%;" scope="row">
						<label for="<?php echo esc_attr( $args['content'] . '-timecheck-enabled' ); ?>"><?php esc_html_e( 'Enable Time Check', 'classic-honeypot-for-cf7' ); ?></label>
					</th>
					<td>
						<input type="checkbox" name="timecheck_enabled:true" id="<?php echo esc_attr( $args['content'] . '-timecheck-enabled' ); ?>" class="option" />
						<input type="number" step="1" min="1" placeholder="4" value="" name="timecheck_value" class="oneline option" id="<?php echo esc_attr( $args['content'] . '-timecheck-value' ); ?>" /> <?php esc_html_e('seconds', 'classic-honeypot-for-cf7'); ?>
					</td>
				</tr>

			</tbody></table>
		</fieldset>
	</div>

	<div class="insert-box">
		<input type="text" name="honeypot" class="tag code" readonly="readonly" onfocus="this.select()" />

		<div class="submitbox">
			<input type="button" class="button button-primary insert-tag" value="<?php esc_attr_e( 'Insert Tag', 'classic-honeypot-for-cf7' ); ?>" />
		</div>

		<br class="clear" />
	</div>
<?php }
