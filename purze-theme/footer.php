<?php
/**
 * Footer
 * @package PurzeTheme
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
</main>
<footer class="footer" role="contentinfo">
	<div class="purze-container">
		<div style="display:flex; flex-wrap:wrap; gap:20px; align-items:center; justify-content:space-between;">
			<div>
				<strong><?php bloginfo( 'name' ); ?></strong> &copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>
			</div>
			<ul style="list-style:none; display:flex; gap:12px; margin:0; padding:0;">
				<li><a href="#" rel="noopener" target="_blank">Facebook</a></li>
				<li><a href="#" rel="noopener" target="_blank">Instagram</a></li>
				<li><a href="#" rel="noopener" target="_blank">LinkedIn</a></li>
			</ul>
		</div>
		<p style="opacity:.7; margin-top:12px;"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>