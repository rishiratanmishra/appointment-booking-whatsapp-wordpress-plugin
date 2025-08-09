<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="baw-floating-button <?php echo esc_attr($position_class); ?>" id="baw-floating-button" aria-controls="baw-modal" aria-expanded="false">
    <span class="baw-icon">
        <img src="<?php echo esc_url(BAW_PLUGIN_URL . 'assets/icons/' . ($settings['icon'] ?? 'whatsapp') . '.svg'); ?>" alt="icon"/>
    </span>
    <span class="baw-text">
        <?php if (($settings['floating_text_type'] ?? 'static') === 'animated'): ?>
            <span class="baw-animated" data-messages="<?php echo esc_attr(wp_json_encode($settings['animated_messages'] ?? [])); ?>" data-speed="<?php echo (int) ($settings['animation_speed_ms'] ?? 2500); ?>"></span>
        <?php else: ?>
            <?php echo esc_html($settings['static_text'] ?? ''); ?>
        <?php endif; ?>
    </span>
</div>


