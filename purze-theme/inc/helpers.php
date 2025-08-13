<?php
namespace Purze;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Helpers {
	private static $mail_from = null;
	private static $mail_from_name = null;
	private static $alt_body = null;

	public static function get_settings() : array {
		$defaults = [
			'notification_emails'     => '',
			'from_name'                => get_bloginfo( 'name' ),
			'from_email'               => get_option( 'admin_email' ),
			'auto_reply_subject'       => 'Thanks for contacting {site_name}',
			'auto_reply_body'          => '<p>Hi {name},</p><p>Thanks for reaching out about {service}. We will get back to you shortly.</p><p>— {site_name}</p>',
			'services'                 => 'Web Design|web_design,Graphic Design|graphic_design,SEO|seo,Digital Marketing|digital_marketing,Other|other',
			'enable_status_notify'     => 0,
			'google_sheets_enabled'    => 0,
			'google_sheets_config'     => '',
			'data_consent_note'        => '',
		];
		$opts = get_option( 'purze_theme_settings', [] );
		return wp_parse_args( $opts, $defaults );
	}

	public static function get_service_options() : array {
		$opts = self::get_settings();
		$list = isset( $opts['services'] ) ? (string) $opts['services'] : '';
		$items = array_map( 'trim', explode( ',', $list ) );
		$out = [];
		if ( empty( $items ) ) {
			return $out;
		}
		foreach ( $items as $item ) {
			$parts = array_map( 'trim', explode( '|', $item ) );
			$label = $parts[0] ?? $item;
			$value = $parts[1] ?? sanitize_title( $label );
			$out[] = [ 'label' => $label, 'value' => $value ];
		}
		return $out;
	}

	public static function replace_shortcodes( string $template, array $data ) : string {
		$map = [
			'{name}'      => $data['name'] ?? '',
			'{email}'     => $data['email'] ?? '',
			'{phone}'     => $data['phone'] ?? '',
			'{service}'   => $data['service'] ?? '',
			'{message}'   => nl2br( esc_html( (string) ( $data['message'] ?? '' ) ) ),
			'{site_name}' => get_bloginfo( 'name' ),
		];
		return strtr( $template, $map );
	}

	public static function sanitize_phone( string $phone ) : string {
		$normalized = preg_replace( '/[^0-9+\-\s]/', '', $phone );
		return trim( $normalized );
	}

	public static function validate_phone_digits( string $phone ) : bool {
		$digits = preg_replace( '/\D+/', '', $phone );
		return strlen( $digits ) >= 7;
	}

	public static function build_email_header_html( string $from_name, string $logo_url ) : string {
		$logo = $logo_url ? '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( $from_name ) . '" style="height:40px;">' : '';
		return '<table role="presentation" width="100%" style="border-collapse:collapse; margin-bottom:16px;"><tr><td style="display:flex; align-items:center; justify-content:space-between;"><div>' . $logo . '</div><div style="font-weight:700; font-family:Arial, sans-serif;">' . esc_html( $from_name ) . '</div></td></tr></table>';
	}

	public static function filter_mail_from( $email ) {
		return self::$mail_from ? self::$mail_from : $email;
	}
	public static function filter_mail_from_name( $name ) {
		return self::$mail_from_name ? self::$mail_from_name : $name;
	}
	public static function filter_phpmailer( $phpmailer ) : void {
		if ( self::$alt_body !== null ) {
			$phpmailer->AltBody = self::$alt_body;
		}
	}

	public static function send_scoped_email( array $args ) : bool {
		$settings = self::get_settings();
		$from_email = sanitize_email( $settings['from_email'] );
		$from_name  = wp_kses_post( $settings['from_name'] );

		self::$mail_from = $from_email;
		self::$mail_from_name = $from_name;
		$plain = wp_strip_all_tags( wp_specialchars_decode( (string) ( $args['message'] ?? '' ), ENT_QUOTES ) );
		self::$alt_body = isset( $args['alt_message'] ) ? (string) $args['alt_message'] : $plain;

		add_filter( 'wp_mail_from', [ __CLASS__, 'filter_mail_from' ] );
		add_filter( 'wp_mail_from_name', [ __CLASS__, 'filter_mail_from_name' ] );
		add_action( 'phpmailer_init', [ __CLASS__, 'filter_phpmailer' ] );

		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];
		$sent = wp_mail( $args['to'], $args['subject'], $args['message'], $headers );

		remove_filter( 'wp_mail_from', [ __CLASS__, 'filter_mail_from' ] );
		remove_filter( 'wp_mail_from_name', [ __CLASS__, 'filter_mail_from_name' ] );
		remove_action( 'phpmailer_init', [ __CLASS__, 'filter_phpmailer' ] );
		self::$mail_from = null;
		self::$mail_from_name = null;
		self::$alt_body = null;
		return (bool) $sent;
	}
}