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
 *
 * Initialize the shortcode
 * 		This lets CF7 know about Mr. Honeypot.
 * 
 */
add_action( 'wpcf7_init', 'choneypot4cf7_add_form_tag', 10 );
function choneypot4cf7_add_form_tag() {
	
	$choneypot4cf7_config = choneypot4cf7_get_config();
	$do_not_store = ( empty( $choneypot4cf7_config['store_honeypot'] ) ) ? true : false;

	wpcf7_add_form_tag(
		'honeypot',
		'choneypot4cf7_form_tag_handler',
		array(
			'name-attr' => true,
			'do-not-store' => $do_not_store,
			'not-for-mail' => true,
		)
	);
}


/**
 * 
 * Form Tag handler
 * 		This is where we generate the honeypot HTML from the shortcode options
 * 
 */
function choneypot4cf7_form_tag_handler( $tag ) {

	if ( empty( $tag->name ) ) {
		return '';
	}

	$validation_error = wpcf7_get_validation_error( $tag->name );

	$choneypot4cf7_config = choneypot4cf7_get_config();

	$class = wpcf7_form_controls_class( 'text' );
	
	$placeholder = (string) reset( $tag->values );

	$accessibility_message = ( ! empty( $choneypot4cf7_config['accessibility_message'] ) ) ? $choneypot4cf7_config['accessibility_message'] : __( 'Please leave this field empty.', 'classic-honeypot-for-cf7' );

	$atts = array(
		'class' 			=> $tag->get_class_option( $class ),
		'id'				=> $tag->get_option( 'id', 'id', true ),
		'wrapper_id' 		=> $tag->get_option( 'wrapper-id' ),
		'placeholder' 		=> ( $placeholder ) ? $placeholder : $choneypot4cf7_config['placeholder'],
		'message' 			=> apply_filters( 'wpcf7_honeypot_accessibility_message', $accessibility_message ),
		'name'				=> $tag->name,
		'type'				=> $tag->type,
		'validautocomplete'	=> ( $tag->get_option( 'validautocomplete' ) ) ? $tag->get_option( 'validautocomplete' ) : $choneypot4cf7_config['w3c_valid_autocomplete'],
		'move_inline_css'	=> ( $tag->get_option( 'move-inline-css' ) ) ? $tag->get_option( 'move-inline-css' ) : $choneypot4cf7_config['move_inline_css'],
		'nomessage'			=> ( $tag->get_option( 'nomessage' ) ) ? $tag->get_option( 'nomessage' ) : $choneypot4cf7_config['nomessage'],
		'timecheck_enabled'	=> ( $tag->get_option( 'timecheck_enabled' ) ) ? $tag->get_option( 'timecheck_enabled' ) : $choneypot4cf7_config['timecheck_enabled'],
		'timecheck_value'	=> ( $timecheck_value = $tag->get_option( 'timecheck_value' ) ) ? reset($timecheck_value) : $choneypot4cf7_config['timecheck_value'],
		'validation_error'	=> $validation_error,
		'css'				=> apply_filters( 'wpcf7_honeypot_container_css', 'display:none !important; visibility:hidden !important;' ),
	);

	$unique_id = uniqid( 'wpcf7-' );
	$wrapper_id = ( ! empty($atts['wrapper_id'] ) ) ? reset( $atts['wrapper_id'] ) : $unique_id . '-wrapper';
	$input_placeholder = ( ! empty( $atts['placeholder'] ) ) ? ' placeholder="' . $atts['placeholder'] . '" ' : '';
	$input_id = ( ! empty( $atts['id'] ) ) ? $atts['id'] : $unique_id . '-field';
	$autocomplete_value = ( $atts['validautocomplete'][0] === 'true' ) ? 'off' : 'new-password';

	// Check if we should move the CSS off the element and into the footer
	if ( ! empty( $atts['move_inline_css'] ) && $atts['move_inline_css'][0] === 'true' ) {
		$hp_css = '#' . $wrapper_id . ' {' . $atts['css'] . '}';
		wp_register_style( $unique_id . '-inline', false );
		wp_enqueue_style( $unique_id . '-inline' );
		wp_add_inline_style( $unique_id . '-inline', $hp_css );
		$el_css = '';
	} else {
		$el_css = 'style="' . $atts['css'] . '"';
	}

	$html = '<span id="' . $wrapper_id . '" class="wpcf7-form-control-wrap ' . $atts['name'] . '-wrap" ' . $el_css . '>';
	
	if ( !empty( $atts['timecheck_enabled'] ) && $atts['timecheck_enabled'][0] !== 'false' ) {
		$html .= '<input type="hidden" name="'.$atts['name'].'-time-start" value="'.time().'">';
		$html .= '<input type="hidden" name="'.$atts['name'].'-time-check" value="'.$atts['timecheck_value'].'">';
	}

	if ( empty( $atts['nomessage'] ) || $atts['nomessage'][0] === 'false' ) {
		$html .= '<label for="' . $input_id . '" class="hp-message">' . $atts['message'] . '</label>';
	}

	$html .= '<input id="' . $input_id . '" ' . $input_placeholder . ' class="' . $atts['class'] . '" type="text" name="' . $atts['name'] . '" value="" size="40" tabindex="-1" autocomplete="'. $autocomplete_value . '" />';
	$html .= $validation_error . '</span>';

	// Hook for filtering finished Honeypot form element.
	return apply_filters( 'wpcf7_honeypot_html_output' , $html, $atts );
}


/**
 * 
 * Honeypot Spam Check
 * 		Bots beware!
 * 
 */
add_filter( 'wpcf7_spam', 'choneypot4cf7_spam_check', 10, 2 );

function choneypot4cf7_spam_check( $spam, $submission = null ) {

	if ( $spam ) {
		return $spam;
	}

	$cf7form = WPCF7_ContactForm::get_current();
	$form_tags = $cf7form->scan_form_tags();
	
	foreach ( $form_tags as $tag ) {
		if ( $tag->type == 'honeypot' ) {
			$hp_ids[] = $tag->name;
		}
	}

	// Check if form has Honeypot fields, if not, exit
	if ( empty( $hp_ids ) ) {
		return $spam;
	}

	foreach ( $hp_ids as $hpid ) {
		$value = isset( $_POST[$hpid] ) ? $_POST[$hpid] : '';
		

		// SPAM CHECK #1: Let's check submission speed first, as it's time sensitive. :)
		$timecheck_start = isset( $_POST[$hpid.'-time-start'] ) ? $_POST[$hpid.'-time-start'] : '';

		if ( $timecheck_start ) {
			$submission_time = time();
			$timecheck_value = isset( $_POST[$hpid.'-time-check'] ) ? $_POST[$hpid.'-time-check'] : '';
			$submission_interval = $submission_time - $timecheck_start;
			
			if ( $submission_interval < $timecheck_value ) {
				// Fast Bots!
				$spam = true;
				
				if ( $submission ) {
					$submission->add_spam_log( array(
						'agent' => 'honeypot',
						'reason' => sprintf(
							/* translators: 1: submission interval integer 2: honeypot field ID */
							__( 'Honeypot detected form submitted too fast (%1$s seconds). Field ID = %2$s', 'classic-honeypot-for-cf7' ), 
							$submission_interval,
							$hpid
						),
					) );
				}

				return $spam; // There's no need to go on, this is most likely a bot submission.

			}
		}

		// SPAM CHECK #2: Now we check the honeypot!
		if ( $value != '' ) {
			// Chatty Bots!
			$spam = true;
			
			if ( $submission ) {
				$submission->add_spam_log( array(
					'agent' => 'honeypot',
					'reason' => sprintf(
						/* translators: %s: honeypot field ID */
						__( 'Something is stuck in the honey. Field ID = %s', 'classic-honeypot-for-cf7' ), 
						$hpid
					),
				) );
			}

			return $spam; // There's no need to go on, we've got flies in the honey.
		}

	}

	return $spam;
}


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