<?php
/**
 * Header
 * @package PurzeTheme
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'purze-theme' ); ?></a>
<header class="site-header" role="banner">
	<div class="purze-container site-header-inner">
		<div class="site-logo">
			<?php if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) { the_custom_logo(); } else { ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-name"><?php bloginfo( 'name' ); ?></a>
			<?php } ?>
		</div>
		<nav class="primary-nav" role="navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'purze-theme' ); ?>">
			<?php
				wp_nav_menu([
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'      => 'menu-items',
					'fallback_cb'     => false,
				]);
			?>
		</nav>
		<button id="purze-dark-toggle" class="purze-button" aria-pressed="false" aria-label="Toggle dark mode" style="background:#0F172A;">
			<span>🌓</span>
		</button>
	</div>
</header>
<main id="content">
<script>
(function(){
	try{
		var btn=document.getElementById('purze-dark-toggle');
		if(!btn) return;
		var key='purze-theme-mode';
		var mode=localStorage.getItem(key);
		if(mode==='dark'){ document.documentElement.setAttribute('data-theme','dark'); }
		btn.addEventListener('click',function(){
			var isDark=document.documentElement.getAttribute('data-theme')==='dark';
			if(isDark){ document.documentElement.removeAttribute('data-theme'); localStorage.setItem(key,'light'); btn.setAttribute('aria-pressed','false'); }
			else { document.documentElement.setAttribute('data-theme','dark'); localStorage.setItem(key,'dark'); btn.setAttribute('aria-pressed','true'); }
		});
	}catch(e){}
})();
</script>