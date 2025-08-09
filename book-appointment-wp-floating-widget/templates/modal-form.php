<?php if (!defined('ABSPATH')) { exit; } ?>
<div id="baw-modal" class="baw-modal" aria-hidden="true" role="dialog" aria-labelledby="baw-modal-title">
    <div class="baw-modal-overlay" data-close="baw-modal"></div>
    <div class="baw-modal-content">
        <div class="baw-modal-header">
            <h3 id="baw-modal-title"><?php esc_html_e('Book Appointment', 'book-appointment-wp-floating-widget'); ?></h3>
            <button type="button" class="baw-close" data-close="baw-modal">&times;</button>
        </div>
        <form id="baw-appointment-form">
            <?php wp_nonce_field('baw_frontend_nonce', 'nonce'); ?>
            <div class="baw-fields">
                <?php foreach ($fields as $field): if (!($field['enabled'] ?? true)) continue; ?>
                    <?php $key = $field['key'] ?? baw_sanitize_field_key($field['label'] ?? '');
                    $label = $field['label'] ?? ucfirst($key);
                    $type = $field['type'] ?? 'text';
                    $placeholder = $field['placeholder'] ?? '';
                    $required = !empty($field['required']);
                    $options = isset($field['options']) ? (array) $field['options'] : []; ?>
                    <div class="baw-field baw-type-<?php echo esc_attr($type); ?>">
                        <label for="baw-<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?><?php if ($required): ?> <span class="baw-required">*</span><?php endif; ?></label>
                        <?php if ($type === 'textarea'): ?>
                            <textarea id="baw-<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" <?php echo $required ? 'required' : ''; ?>></textarea>
                        <?php elseif ($type === 'select'): ?>
                            <select id="baw-<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" <?php echo $required ? 'required' : ''; ?>>
                                <option value="">--</option>
                                <?php foreach ($options as $opt): ?>
                                    <option value="<?php echo esc_attr($opt); ?>"><?php echo esc_html($opt); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif ($type === 'checkbox'): ?>
                            <input type="checkbox" id="baw-<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" value="1" <?php echo $required ? 'required' : ''; ?> />
                        <?php else: ?>
                            <input type="<?php echo esc_attr($type); ?>" id="baw-<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" <?php echo $required ? 'required' : ''; ?> />
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="baw-actions">
                <button type="submit" class="baw-submit"><?php esc_html_e('Submit', 'book-appointment-wp-floating-widget'); ?></button>
                <button type="button" class="baw-cancel" data-close="baw-modal"><?php esc_html_e('Cancel', 'book-appointment-wp-floating-widget'); ?></button>
            </div>
            <div class="baw-message" role="status" aria-live="polite"></div>
        </form>
    </div>
</div>


