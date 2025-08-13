<?php
/**
 * Front Page Template
 * @package PurzeTheme
 */
get_header(); ?>

<section class="purze-hero">
	<div class="purze-container purze-hero-grid">
		<div>
			<h1 style="font-size:44px; margin-bottom:14px;">Professional Cleaning Services</h1>
			<p style="font-size:18px; opacity:.9;">Book trusted home and office cleaning. Transparent pricing, vetted professionals, and spotless results.</p>
			<div class="trust-badges" style="margin-top:20px;">
				<div class="purze-hero-card" style="text-align:center; padding:14px;">5-star Rated</div>
				<div class="purze-hero-card" style="text-align:center; padding:14px;">Insured Pros</div>
				<div class="purze-hero-card" style="text-align:center; padding:14px;">Eco-friendly</div>
				<div class="purze-hero-card" style="text-align:center; padding:14px;">Flexible Slots</div>
			</div>
		</div>
		<div>
			<?php echo do_shortcode('[theme_hero_form]'); ?>
		</div>
	</div>
</section>

<section class="section section-muted">
	<div class="purze-container">
		<h2>Services</h2>
		<div style="display:grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap:20px; margin-top:20px;">
			<div class="purze-hero-card"><h3>Home Cleaning</h3><p>Deep and regular cleaning tailored to your home.</p></div>
			<div class="purze-hero-card"><h3>Office Cleaning</h3><p>Keep your workspace pristine and productive.</p></div>
			<div class="purze-hero-card"><h3>Move-in/Move-out</h3><p>End-of-lease cleans that pass inspection.</p></div>
		</div>
	</div>
</section>

<section class="section">
	<div class="purze-container">
		<h2>Testimonials</h2>
		<p>Coming soon…</p>
	</div>
</section>

<section class="section section-dark">
	<div class="purze-container" style="text-align:center;">
		<h2>Ready for a spotless space?</h2>
		<p>Get a free quote in minutes.</p>
		<a href="#" class="purze-button">Get a Quote</a>
	</div>
</section>

<?php get_footer();