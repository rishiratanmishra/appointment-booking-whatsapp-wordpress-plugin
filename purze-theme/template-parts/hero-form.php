<?php
/**
 * Hero Form Partial
 * @package PurzeTheme
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$settings = Purze\Helpers::get_settings();
$services = Purze\Helpers::get_service_options();
$consent  = isset( $settings['data_consent_note'] ) ? $settings['data_consent_note'] : '';
?>
<div class="purze-hero-card" role="form" aria-labelledby="purze-hero-form-title">
	<h2 id="purze-hero-form-title" style="margin-top:0;">Request a Service</h2>
	<form id="purze-hero-form" class="purze-form" novalidate>
		<?php wp_nonce_field( 'purze_ajax_nonce', 'security' ); ?>
		<input type="hidden" name="action" value="purze_submit_lead" />
		<p style="display:none;">
			<label>Website
				<input type="text" name="website" tabindex="-1" autocomplete="off" />
			</label>
		</p>
		<label for="purze_name"><?php esc_html_e( 'Name', 'purze-theme' ); ?></label>
		<input id="purze_name" name="name" type="text" required aria-required="true" />

		<label for="purze_email"><?php esc_html_e( 'Email', 'purze-theme' ); ?></label>
		<input id="purze_email" name="email" type="email" required aria-required="true" />

		<label for="purze_phone"><?php esc_html_e( 'Phone', 'purze-theme' ); ?></label>
		<input id="purze_phone" name="phone" type="tel" required aria-required="true" />

		<label for="purze_service"><?php esc_html_e( 'Select Service', 'purze-theme' ); ?></label>
		<select id="purze_service" name="service" required aria-required="true">
			<option value=""><?php esc_html_e( 'Choose…', 'purze-theme' ); ?></option>
			<?php foreach ( $services as $service ) : ?>
				<option value="<?php echo esc_attr( $service['value'] ); ?>"><?php echo esc_html( $service['label'] ); ?></option>
			<?php endforeach; ?>
		</select>

		<label for="purze_message"><?php esc_html_e( 'Message (optional)', 'purze-theme' ); ?></label>
		<textarea id="purze_message" name="message" rows="4"></textarea>

		<label style="display:flex; align-items:center; gap:8px;">
			<input type="checkbox" name="human" required />
			<span><?php esc_html_e( "I'm not a robot", 'purze-theme' ); ?></span>
		</label>

		<?php if ( ! empty( $consent ) ) : ?>
			<p style="font-size: 12px; opacity: .8; "><?php echo wp_kses_post( $consent ); ?></p>
		<?php endif; ?>

		<button class="purze-button" type="submit" id="purze_submit_btn"><?php esc_html_e( 'Send Request', 'purze-theme' ); ?></button>
		<div class="purze-message" id="purze_form_message" aria-live="polite"></div>
	</form>
</div>