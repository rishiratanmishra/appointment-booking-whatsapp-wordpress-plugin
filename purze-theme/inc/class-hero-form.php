<?php
namespace Purze;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Hero_Form {
	const TABLE = 'wp_theme_leads';

	public static function shortcode_hero_form( $atts = [] ) : string {
		ob_start();
		get_template_part( 'template-parts/hero', 'form' );
		return (string) ob_get_clean();
	}

	public static function shortcode_contact_form( $atts = [] ) : string {
		$nonce = wp_create_nonce( 'purze_ajax_nonce' );
		ob_start();
		?>
		<div class="purze-hero-card" role="form" aria-labelledby="purze-contact-form-title">
			<h2 id="purze-contact-form-title" style="margin-top:0;">Contact Form</h2>
			<form id="purze-contact-form" class="purze-form" novalidate>
				<?php wp_nonce_field( 'purze_ajax_nonce', 'security' ); ?>
				<input type="hidden" name="action" value="purze_submit_contact" />
				<p style="display:none;">
					<label>Website
						<input type="text" name="website" tabindex="-1" autocomplete="off" />
					</label>
				</p>
				<label for="purze_c_name"><?php esc_html_e( 'Name', 'purze-theme' ); ?></label>
				<input id="purze_c_name" name="name" type="text" required aria-required="true" />
				<label for="purze_c_email"><?php esc_html_e( 'Email', 'purze-theme' ); ?></label>
				<input id="purze_c_email" name="email" type="email" required aria-required="true" />
				<label for="purze_c_message"><?php esc_html_e( 'Message', 'purze-theme' ); ?></label>
				<textarea id="purze_c_message" name="message" rows="4" required aria-required="true"></textarea>
				<button class="purze-button" type="submit" id="purze_contact_submit_btn"><?php esc_html_e( 'Send Message', 'purze-theme' ); ?></button>
				<div class="purze-message" id="purze_contact_form_message" aria-live="polite"></div>
			</form>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	public static function maybe_install_db() : void {
		global $wpdb;
		$table = self::get_table_name();
		$charset = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE IF NOT EXISTS $table (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(191) NOT NULL,
			email VARCHAR(191) NOT NULL,
			phone VARCHAR(64) NOT NULL,
			service VARCHAR(191) NOT NULL,
			message TEXT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'open',
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY email (email)
		) $charset;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	protected static function get_table_name() : string {
		global $wpdb;
		return $wpdb->prefix . 'theme_leads';
	}

	protected static function rate_limited( string $ip ) : bool {
		$key = 'purze_rate_' . md5( $ip );
		$hits = (int) get_transient( $key );
		$hits++;
		set_transient( $key, $hits, MINUTE_IN_SECONDS );
		return $hits > 5;
	}

	protected static function validate_common( array $data ) : array {
		$errors = [];
		if ( empty( $data['name'] ) ) { $errors['name'] = __( 'Name is required.', 'purze-theme' ); }
		if ( empty( $data['email'] ) || ! is_email( $data['email'] ) ) { $errors['email'] = __( 'Valid email is required.', 'purze-theme' ); }
		if ( isset( $data['phone'] ) ) {
			$phone = Helpers::sanitize_phone( (string) $data['phone'] );
			if ( empty( $phone ) || ! Helpers::validate_phone_digits( $phone ) ) { $errors['phone'] = __( 'Valid phone is required.', 'purze-theme' ); }
		}
		return $errors;
	}

	public static function handle_submit_lead() : void {
		self::handle_submit( 'lead' );
	}
	public static function handle_submit_contact() : void {
		self::handle_submit( 'contact' );
	}

	protected static function handle_submit( string $type ) : void {
		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'purze_ajax_nonce' ) ) {
			wp_send_json_error( [ 'message' => __( 'Nonce failed.', 'purze-theme' ) ] );
		}
		if ( ! empty( $_POST['website'] ) ) { // Honeypot.
			wp_send_json_error( [ 'message' => __( 'Spam detected.', 'purze-theme' ) ] );
		}

		$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
		if ( self::rate_limited( $ip ) ) {
			wp_send_json_error( [ 'message' => __( 'Too many submissions. Please try later.', 'purze-theme' ) ] );
		}

		$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
		$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$phone   = isset( $_POST['phone'] ) ? Helpers::sanitize_phone( (string) wp_unslash( $_POST['phone'] ) ) : '';
		$service = sanitize_text_field( wp_unslash( $_POST['service'] ?? '' ) );
		$message = wp_kses_post( wp_unslash( $_POST['message'] ?? '' ) );
		$human   = isset( $_POST['human'] ) ? (int) $_POST['human'] : 0;

		$data = compact( 'name','email','phone','service','message' );
		$errors = self::validate_common( array_merge( $data, [ 'human' => $human ] ) );
		if ( 'lead' === $type && empty( $service ) ) { $errors['service'] = __( 'Please select a service.', 'purze-theme' ); }
		if ( 'lead' === $type && ! $human ) { $errors['human'] = __( 'Please confirm you are human.', 'purze-theme' ); }
		if ( ! empty( $errors ) ) {
			wp_send_json_error( [ 'message' => __( 'Please correct the errors and try again.', 'purze-theme' ), 'errors' => $errors ] );
		}

		if ( 'lead' === $type ) {
			self::store_lead( $data );
		}
		self::send_emails( $type, $data );
		wp_send_json_success( [ 'message' => __( 'Thank you! We will be in touch shortly.', 'purze-theme' ) ] );
	}

	protected static function store_lead( array $data ) : void {
		global $wpdb;
		$table = self::get_table_name();
		$wpdb->insert( $table, [
			'name'       => $data['name'],
			'email'      => $data['email'],
			'phone'      => $data['phone'],
			'service'    => $data['service'],
			'message'    => $data['message'],
			'status'     => 'open',
			'created_at' => gmdate( 'Y-m-d H:i:s' ),
		], [ '%s','%s','%s','%s','%s','%s','%s' ] );
	}

	protected static function send_emails( string $type, array $data ) : void {
		$settings = Helpers::get_settings();
		$site_name = get_bloginfo( 'name' );
		$logo_id = get_theme_mod( 'custom_logo' );
		$logo    = $logo_id ? wp_get_attachment_image_src( $logo_id, 'full' ) : false;
		$logo_url = $logo ? $logo[0] : ( get_site_icon_url() ?: '' );

		$header_html = Helpers::build_email_header_html( $settings['from_name'] ?: $site_name, $logo_url );

		// Admin notification.
		$admin_to = array_filter( array_map( 'sanitize_email', array_map( 'trim', explode( ',', (string) $settings['notification_emails'] ) ) ) );
		if ( ! empty( $admin_to ) ) {
			$subject = sprintf( __( 'New Lead — %s', 'purze-theme' ), $site_name );
			if ( 'contact' === $type ) {
				$subject = sprintf( __( 'New Contact — %s', 'purze-theme' ), $site_name );
			}
			$body = '<div style="font-family:Arial,sans-serif; font-size:14px;">' . $header_html;
			$body .= '<h2 style="margin:0 0 10px;">' . esc_html( $subject ) . '</h2>';
			$body .= '<p><strong>Name:</strong> ' . esc_html( $data['name'] ) . '</p>';
			$body .= '<p><strong>Email:</strong> ' . esc_html( $data['email'] ) . '</p>';
			if ( ! empty( $data['phone'] ) ) { $body .= '<p><strong>Phone:</strong> ' . esc_html( $data['phone'] ) . '</p>'; }
			if ( ! empty( $data['service'] ) ) { $body .= '<p><strong>Service:</strong> ' . esc_html( $data['service'] ) . '</p>'; }
			if ( ! empty( $data['message'] ) ) { $body .= '<p><strong>Message:</strong><br>' . nl2br( esc_html( $data['message'] ) ) . '</p>'; }
			$body .= '<p style="color:#6b7280; font-size:12px;">' . esc_html( gmdate( 'c' ) ) . '</p>';
			$body .= '<hr><p style="font-size:12px; color:#6b7280;">' . esc_url( home_url() ) . ' • ' . esc_html( $site_name ) . '</p></div>';
			Helpers::send_scoped_email([
				'to'      => implode( ',', $admin_to ),
				'subject' => $subject,
				'message' => $body,
			]);
		}

		// Auto-reply to sender (if email valid).
		if ( is_email( $data['email'] ) ) {
			$subject = $settings['auto_reply_subject'] ?: sprintf( __( 'Thanks from %s', 'purze-theme' ), $site_name );
			$template = $settings['auto_reply_body'] ?: '<p>Hi {name},</p><p>Thanks for contacting {site_name}.</p>';
			$replaced = Helpers::replace_shortcodes( $template, $data );
			$body = '<div style="font-family:Arial,sans-serif; font-size:14px;">' . $header_html . $replaced . '<hr><p style="font-size:12px; color:#6b7280;">' . esc_url( home_url() ) . ' • ' . esc_html( $site_name ) . '</p></div>';
			Helpers::send_scoped_email([
				'to'      => $data['email'],
				'subject' => $subject,
				'message' => $body,
			]);
		}
	}
}