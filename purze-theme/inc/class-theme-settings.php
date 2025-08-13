<?php
namespace Purze;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Theme_Settings {
	public static function init() : void {
		add_action( 'admin_menu', [ __CLASS__, 'register_menu' ] );
		add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
	}

	public static function register_menu() : void {
		add_menu_page(
			__( 'Theme Settings', 'purze-theme' ),
			__( 'Theme Settings', 'purze-theme' ),
			'manage_options',
			'purze-theme-settings',
			[ __CLASS__, 'render_page' ],
			'dashicons-email',
			61
		);
	}

	public static function register_settings() : void {
		register_setting( 'purze_theme_settings_group', 'purze_theme_settings', [ __CLASS__, 'sanitize' ] );

		add_settings_section( 'purze_leads_email', __( 'Leads & Email', 'purze-theme' ), function(){
			echo '<p>' . esc_html__( 'Configure notification recipients, sender, auto-replies, and services list.', 'purze-theme' ) . '</p>';
		}, 'purze-theme-settings' );

		$fields = [
			'notification_emails'  => [ 'label' => __( 'Notification Emails', 'purze-theme' ), 'type' => 'text' ],
			'from_name'            => [ 'label' => __( 'From Name', 'purze-theme' ), 'type' => 'text' ],
			'from_email'           => [ 'label' => __( 'From Email', 'purze-theme' ), 'type' => 'email' ],
			'auto_reply_subject'   => [ 'label' => __( 'Auto-Reply Subject', 'purze-theme' ), 'type' => 'text' ],
			'auto_reply_body'      => [ 'label' => __( 'Auto-Reply Body (HTML)', 'purze-theme' ), 'type' => 'wp_editor' ],
			'services'             => [ 'label' => __( 'Service Options (label|value, comma-separated)', 'purze-theme' ), 'type' => 'text' ],
			'enable_status_notify' => [ 'label' => __( 'Enable status change notifications', 'purze-theme' ), 'type' => 'checkbox' ],
			'google_sheets_enabled'=> [ 'label' => __( 'Google Sheets (phase-ready placeholder)', 'purze-theme' ), 'type' => 'checkbox' ],
			'google_sheets_config' => [ 'label' => __( 'Google Sheets JSON config', 'purze-theme' ), 'type' => 'textarea' ],
			'data_consent_note'    => [ 'label' => __( 'Data consent note (optional)', 'purze-theme' ), 'type' => 'textarea' ],
		];

		foreach ( $fields as $key => $field ) {
			add_settings_field(
				$key,
				$field['label'],
				[ __CLASS__, 'render_field' ],
				'purze-theme-settings',
				'purze_leads_email',
				[ 'key' => $key, 'field' => $field ]
			);
		}
	}

	public static function sanitize( array $input ) : array {
		$output = Helpers::get_settings();
		$output['notification_emails']  = sanitize_text_field( $input['notification_emails'] ?? '' );
		$output['from_name']            = sanitize_text_field( $input['from_name'] ?? '' );
		$output['from_email']           = sanitize_email( $input['from_email'] ?? '' );
		$output['auto_reply_subject']   = sanitize_text_field( $input['auto_reply_subject'] ?? '' );
		$output['auto_reply_body']      = wp_kses_post( $input['auto_reply_body'] ?? '' );
		$output['services']             = sanitize_text_field( $input['services'] ?? '' );
		$output['enable_status_notify'] = ! empty( $input['enable_status_notify'] ) ? 1 : 0;
		$output['google_sheets_enabled']= ! empty( $input['google_sheets_enabled'] ) ? 1 : 0;
		$output['google_sheets_config'] = wp_kses_post( $input['google_sheets_config'] ?? '' );
		$output['data_consent_note']    = wp_kses_post( $input['data_consent_note'] ?? '' );
		return $output;
	}

	public static function render_field( array $args ) : void {
		$key   = $args['key'];
		$field = $args['field'];
		$opts  = Helpers::get_settings();
		$value = $opts[ $key ] ?? '';

		switch ( $field['type'] ) {
			case 'text':
			case 'email':
				printf( '<input type="%1$s" name="purze_theme_settings[%2$s]" value="%3$s" class="regular-text" />', esc_attr( $field['type'] ), esc_attr( $key ), esc_attr( (string) $value ) );
				break;
			case 'textarea':
				printf( '<textarea name="purze_theme_settings[%1$s]" rows="5" class="large-text">%2$s</textarea>', esc_attr( $key ), esc_textarea( (string) $value ) );
				break;
			case 'checkbox':
				printf( '<label><input type="checkbox" name="purze_theme_settings[%1$s]" value="1" %2$s /> %3$s</label>', esc_attr( $key ), checked( (int) $value, 1, false ), esc_html__( 'Enabled', 'purze-theme' ) );
				break;
			case 'wp_editor':
				$editor_id = 'purze_theme_settings_' . $key;
				wp_editor( (string) $value, $editor_id, [
					'textarea_name' => 'purze_theme_settings[' . $key . ']',
					'textarea_rows' => 8,
				] );
				break;
		}
	}

	public static function render_page() : void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Theme Settings → Leads & Email', 'purze-theme' ); ?></h1>
			<form action="options.php" method="post">
				<?php
					settings_fields( 'purze_theme_settings_group' );
					do_settings_sections( 'purze-theme-settings' );
					submit_button();
				?>
			</form>
		</div>
		<?php
	}
}

Theme_Settings::init();