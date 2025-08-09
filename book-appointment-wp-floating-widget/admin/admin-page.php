<?php
if (!defined('ABSPATH')) { exit; }

$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
?>
<div class="wrap baw-wrap">
    <h1><?php echo esc_html__('Book Appointment Settings', 'book-appointment-wp-floating-widget'); ?></h1>
    <h2 class="nav-tab-wrapper">
        <a href="<?php echo esc_url(admin_url('admin.php?page=baw_main&tab=general')); ?>" class="nav-tab <?php echo $active_tab==='general'?'nav-tab-active':''; ?>"><?php esc_html_e('General', 'book-appointment-wp-floating-widget'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=baw_main&tab=form')); ?>" class="nav-tab <?php echo $active_tab==='form'?'nav-tab-active':''; ?>"><?php esc_html_e('Form Fields', 'book-appointment-wp-floating-widget'); ?></a>
    </h2>

    <?php if ($active_tab === 'general'): ?>
        <form id="baw-settings-form">
            <?php wp_nonce_field('baw_admin_nonce', 'nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('Enable Widget', 'book-appointment-wp-floating-widget'); ?></th>
                    <td><label><input type="checkbox" name="enabled" value="1" <?php checked($settings['enabled'], 1); ?>> <?php esc_html_e('Enabled', 'book-appointment-wp-floating-widget'); ?></label></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Position', 'book-appointment-wp-floating-widget'); ?></th>
                    <td>
                        <select name="position">
                            <option value="left" <?php selected($settings['position'], 'left'); ?>><?php esc_html_e('Bottom Left', 'book-appointment-wp-floating-widget'); ?></option>
                            <option value="right" <?php selected($settings['position'], 'right'); ?>><?php esc_html_e('Bottom Right', 'book-appointment-wp-floating-widget'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Icon', 'book-appointment-wp-floating-widget'); ?></th>
                    <td>
                        <select name="icon">
                            <option value="whatsapp" <?php selected($settings['icon'], 'whatsapp'); ?>>WhatsApp</option>
                            <option value="calendar" <?php selected($settings['icon'], 'calendar'); ?>>Calendar</option>
                            <option value="phone" <?php selected($settings['icon'], 'phone'); ?>>Phone</option>
                            <option value="appointment" <?php selected($settings['icon'], 'appointment'); ?>>Appointment</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Floating Text Type', 'book-appointment-wp-floating-widget'); ?></th>
                    <td>
                        <label><input type="radio" name="floating_text_type" value="static" <?php checked($settings['floating_text_type'], 'static'); ?>> <?php esc_html_e('Static', 'book-appointment-wp-floating-widget'); ?></label>
                        <label style="margin-left:12px;"><input type="radio" name="floating_text_type" value="animated" <?php checked($settings['floating_text_type'], 'animated'); ?>> <?php esc_html_e('Animated', 'book-appointment-wp-floating-widget'); ?></label>
                    </td>
                </tr>
                <tr class="baw-static-text-row">
                    <th><?php esc_html_e('Static Text', 'book-appointment-wp-floating-widget'); ?></th>
                    <td><input type="text" name="static_text" value="<?php echo esc_attr($settings['static_text']); ?>" class="regular-text"></td>
                </tr>
                <tr class="baw-animated-text-row">
                    <th><?php esc_html_e('Animated Messages', 'book-appointment-wp-floating-widget'); ?></th>
                    <td>
                        <div id="baw-animated-messages">
                            <?php foreach (($settings['animated_messages'] ?? []) as $msg): ?>
                                <div class="baw-row"><input type="text" name="animated_messages[]" value="<?php echo esc_attr($msg); ?>"> <button class="button baw-remove-row" type="button">&times;</button></div>
                            <?php endforeach; ?>
                        </div>
                        <button class="button" id="baw-add-message" type="button"><?php esc_html_e('Add Message', 'book-appointment-wp-floating-widget'); ?></button>
                        <p><label><?php esc_html_e('Animation Speed (ms)', 'book-appointment-wp-floating-widget'); ?> <input type="number" name="animation_speed_ms" min="500" step="100" value="<?php echo esc_attr($settings['animation_speed_ms']); ?>"></label></p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('WhatsApp Phone', 'book-appointment-wp-floating-widget'); ?></th>
                    <td>
                        <label style="margin-right:8px;">
                            <?php esc_html_e('Country Code', 'book-appointment-wp-floating-widget'); ?>
                            <input type="text" name="country_code" value="<?php echo esc_attr($settings['country_code'] ?? ''); ?>" style="width:80px" placeholder="91">
                        </label>
                        <label>
                            <?php esc_html_e('Phone Number', 'book-appointment-wp-floating-widget'); ?>
                            <input type="text" name="whatsapp_phone" value="<?php echo esc_attr($settings['whatsapp_phone'] ?? ''); ?>" class="regular-text" placeholder="9876543210">
                        </label>
                        <p class="description"><?php esc_html_e('Both fields are digits only. The final WhatsApp number becomes country_code + phone. Legacy combined number is still supported as fallback below.', 'book-appointment-wp-floating-widget'); ?></p>
                        <p style="margin-top:8px;">
                            <label>
                                <?php esc_html_e('Legacy Combined Number (optional)', 'book-appointment-wp-floating-widget'); ?>
                                <input type="text" name="whatsapp_number" value="<?php echo esc_attr($settings['whatsapp_number'] ?? ''); ?>" class="regular-text" placeholder="e.g., 11234567890">
                            </label>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Message Template', 'book-appointment-wp-floating-widget'); ?></th>
                    <td>
                        <textarea name="message_template" rows="6" class="large-text"><?php echo esc_textarea($settings['message_template']); ?></textarea>
                        <p class="description"><?php esc_html_e('Use placeholders like {name}, {mobile}, {visit_time}, {description}, or any custom field key.', 'book-appointment-wp-floating-widget'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Extra Emails', 'book-appointment-wp-floating-widget'); ?></th>
                    <td>
                        <input type="text" name="extra_emails" value="<?php echo esc_attr($settings['extra_emails']); ?>" class="regular-text" placeholder="email1@example.com, email2@example.com">
                        <p class="description"><?php esc_html_e('Default admin email is always included. Add more, comma-separated.', 'book-appointment-wp-floating-widget'); ?></p>
                    </td>
                </tr>
            </table>
            <p><button type="submit" class="button button-primary"><?php esc_html_e('Save Settings', 'book-appointment-wp-floating-widget'); ?></button></p>
        </form>
        <script>document.addEventListener('DOMContentLoaded',function(){function t(){var s=document.querySelector('input[name="floating_text_type"]:checked').value;document.querySelector('.baw-static-text-row').style.display=s==='static'?'table-row':'none';document.querySelector('.baw-animated-text-row').style.display=s==='animated'?'table-row':'none'}document.querySelectorAll('input[name="floating_text_type"]').forEach(function(e){e.addEventListener('change',t)});t();});</script>
    <?php else: ?>
        <form id="baw-fields-form">
            <?php wp_nonce_field('baw_admin_nonce', 'nonce'); ?>
            <div style="margin:10px 0;">
                <label>
                    <?php esc_html_e('Load Template', 'book-appointment-wp-floating-widget'); ?>
                    <select id="baw-template-picker">
                        <option value="">—</option>
                        <?php
                        require_once BAW_PLUGIN_DIR . 'admin/templates/form-templates.php';
                        foreach (baw_get_predefined_templates() as $key => $tpl): ?>
                            <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($tpl['label']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button type="button" class="button" id="baw-load-template"><?php esc_html_e('Load', 'book-appointment-wp-floating-widget'); ?></button>
            </div>
            <table class="widefat baw-fields-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Label', 'book-appointment-wp-floating-widget'); ?></th>
                        <th><?php esc_html_e('Key', 'book-appointment-wp-floating-widget'); ?></th>
                        <th><?php esc_html_e('Type', 'book-appointment-wp-floating-widget'); ?></th>
                        <th><?php esc_html_e('Placeholder / Options (comma separated for select)', 'book-appointment-wp-floating-widget'); ?></th>
                        <th><?php esc_html_e('Required', 'book-appointment-wp-floating-widget'); ?></th>
                        <th><?php esc_html_e('Enabled', 'book-appointment-wp-floating-widget'); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="baw-fields-body">
                    <?php foreach ($fields as $f): ?>
                        <tr>
                            <td><input type="text" name="fields[][label]" value="<?php echo esc_attr($f['label']); ?>"/></td>
                            <td><input type="text" name="fields[][key]" value="<?php echo esc_attr($f['key']); ?>"/></td>
                            <td>
                                <select name="fields[][type]">
                                    <?php foreach (['text','textarea','number','email','select','checkbox','date','time','datetime-local','tel'] as $type): ?>
                                        <option value="<?php echo esc_attr($type); ?>" <?php selected($f['type'], $type); ?>><?php echo esc_html($type); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="fields[][placeholder]" value="<?php echo esc_attr($f['placeholder'] ?? ''); ?>" placeholder="Placeholder or options (comma-separated for select)"/>
                            </td>
                            <td style="text-align:center"><input type="checkbox" name="fields[][required]" value="1" <?php checked(!empty($f['required'])); ?>/></td>
                            <td style="text-align:center"><input type="checkbox" name="fields[][enabled]" value="1" <?php checked(!empty($f['enabled']) || !isset($f['enabled'])); ?>/></td>
                            <td><button class="button baw-remove-field" type="button">&times;</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p><button class="button" id="baw-add-field" type="button"><?php esc_html_e('Add Field', 'book-appointment-wp-floating-widget'); ?></button></p>
            <p><button type="submit" class="button button-primary"><?php esc_html_e('Save Fields', 'book-appointment-wp-floating-widget'); ?></button></p>
        </form>
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            const btn = document.getElementById('baw-load-template');
            if (!btn) return;
            btn.addEventListener('click', function(){
                const sel = document.getElementById('baw-template-picker');
                const key = sel.value;
                if (!key) return;
                const map = <?php 
                    $map = [];
                    foreach (baw_get_predefined_templates() as $k=>$t) { $map[$k] = $t['fields']; }
                    echo wp_json_encode($map);
                ?>;
                const fields = map[key] || [];
                const tbody = document.getElementById('baw-fields-body');
                tbody.innerHTML = '';
                fields.forEach(function(f){
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><input type="text" name="fields[][label]" value="${f.label||''}"/></td>
                        <td><input type="text" name="fields[][key]" value="${f.key||''}"/></td>
                        <td>
                            <select name="fields[][type]">
                                ${['text','textarea','number','email','select','checkbox','date','time','datetime-local','tel'].map(t=>`<option value="${t}" ${f.type===t?'selected':''}>${t}</option>`).join('')}
                            </select>
                        </td>
                        <td><input type="text" name="fields[][placeholder]" value="${f.placeholder||''}"/></td>
                        <td style="text-align:center"><input type="checkbox" name="fields[][required]" value="1" ${f.required?'checked':''}/></td>
                        <td style="text-align:center"><input type="checkbox" name="fields[][enabled]" value="1" ${f.enabled!==false?'checked':''}/></td>
                        <td><button class="button baw-remove-field" type="button">&times;</button></td>
                    `;
                    tbody.appendChild(tr);
                });
            });
        });
        </script>
    <?php endif; ?>
</div>


