<?php
/**
 * Template Name: Contact
 * @package PurzeTheme
 */
get_header(); ?>
<section class="section">
	<div class="purze-container">
		<h1>Contact Us</h1>
		<p>Email: <a href="mailto:info@example.com">info@example.com</a> • Phone: <a href="tel:+10000000000">+1 000-000-0000</a></p>
		<div style="margin:20px 0;">
			<strong>Our Location</strong>
			<div class="purze-hero-card">Map placeholder</div>
		</div>
		<h2>Send a Message</h2>
		<?php echo do_shortcode('[theme_contact_form]'); ?>
	</div>
</section>
<?php get_footer();