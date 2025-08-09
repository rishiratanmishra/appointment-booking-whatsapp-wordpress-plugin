<?php
if (!defined('ABSPATH')) { exit; }

$contacted = isset($_GET['contacted']) ? sanitize_text_field($_GET['contacted']) : '';
$args = [];
if ($contacted === '0' || $contacted === '1') {
    $args['contacted'] = $contacted;
}
$submissions = BAW_Database::get_submissions($args);
?>
<div class="wrap baw-wrap">
    <h1><?php esc_html_e('Submissions', 'book-appointment-wp-floating-widget'); ?></h1>
    <p>
        <a class="button" href="<?php echo esc_url(admin_url('admin-post.php?action=baw_export_csv')); ?>"><?php esc_html_e('Export CSV', 'book-appointment-wp-floating-widget'); ?></a>
    </p>
    <form id="baw-submissions-form">
        <?php wp_nonce_field('baw_admin_nonce', 'nonce'); ?>
        <div style="margin: 8px 0;">
            <select id="baw-bulk-action">
                <option value="">Bulk actions</option>
                <option value="contacted">Mark as Contacted</option>
                <option value="not_contacted">Mark as Not Contacted</option>
            </select>
            <button type="button" class="button" id="baw-apply-bulk">Apply</button>
        </div>
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th><input type="checkbox" id="baw-check-all"></th>
                    <?php if (!empty($submissions)): foreach (array_keys($submissions[0]) as $col): ?>
                        <th><?php echo esc_html(ucwords(str_replace('_', ' ', $col))); ?></th>
                    <?php endforeach; endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($submissions)): ?>
                    <tr><td colspan="20"><?php esc_html_e('No submissions yet.', 'book-appointment-wp-floating-widget'); ?></td></tr>
                <?php else: foreach ($submissions as $row): ?>
                    <tr>
                        <td><input type="checkbox" class="baw-row-check" value="<?php echo (int) $row['id']; ?>"></td>
                        <?php foreach ($row as $cell): ?>
                            <td><?php echo esc_html((string) $cell); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </form>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        const checkAll = document.getElementById('baw-check-all');
        const checks = document.querySelectorAll('.baw-row-check');
        if (checkAll) checkAll.addEventListener('change', function(){
            checks.forEach(c=>c.checked=checkAll.checked);
        });
        const applyBtn = document.getElementById('baw-apply-bulk');
        if (applyBtn) applyBtn.addEventListener('click', async function(){
            const ids = Array.from(document.querySelectorAll('.baw-row-check:checked')).map(c=>c.value);
            const action = document.getElementById('baw-bulk-action').value;
            if (!ids.length || !action) return;
            const form = new FormData();
            form.append('action','baw_mark_contacted');
            form.append('nonce', document.querySelector('input[name="nonce"]').value);
            form.append('ids', ids);
            form.append('contacted', action==='contacted'?1:0);
            const res = await fetch(ajaxurl, { method:'POST', body: form });
            const json = await res.json();
            if (json.success) location.reload();
        });
    });
    </script>
</div>


