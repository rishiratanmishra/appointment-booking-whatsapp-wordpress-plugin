<?php
/**
 * Index Template (fallback)
 * @package PurzeTheme
 */
get_header(); ?>
<section class="section">
	<div class="purze-container">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class( 'purze-hero-card' ); ?> style="margin-bottom:20px;">
					<h2 style="margin-top:0;"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
					<div class="entry-content">
						<?php if ( is_singular() ) { the_content(); } else { the_excerpt(); } ?>
					</div>
				</article>
			<?php endwhile; ?>
			<div class="pagination">
				<?php the_posts_pagination(); ?>
			</div>
		<?php else : ?>
			<article class="purze-hero-card">
				<h2><?php esc_html_e( 'Nothing Found', 'purze-theme' ); ?></h2>
				<p><?php esc_html_e( 'It seems we can’t find what you’re looking for.', 'purze-theme' ); ?></p>
			</article>
		<?php endif; ?>
	</div>
</section>
<?php get_footer();