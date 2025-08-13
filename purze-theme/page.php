<?php
/**
 * Default Page Template
 * @package PurzeTheme
 */
get_header(); ?>
<section class="section">
	<div class="purze-container">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			<article <?php post_class(); ?>>
				<h1><?php the_title(); ?></h1>
				<div class="entry-content"><?php the_content(); ?></div>
			</article>
		<?php endwhile; endif; ?>
	</div>
</section>
<?php get_footer();