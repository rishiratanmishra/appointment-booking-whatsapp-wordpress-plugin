<?php
if (!defined('ABSPATH')) { exit; }

$contacted = isset($_GET['contacted']) ? sanitize_text_field($_GET['contacted']) : '';
$page = isset($_GET['paged']) ? (int) $_GET['paged'] : 1;
$per_page = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 20;
$args = ['page' => $page, 'per_page' => $per_page];
if (!empty($_GET['s'])) {
    $args['search'] = sanitize_text_field(wp_unslash($_GET['s']));
}
if ($contacted === '0' || $contacted === '1') {
    $args['contacted'] = $contacted;
}
$result = BAW_Database::get_submissions_page($args);
$submissions = $result['rows'];
$total = $result['total'];
$pages = (int) ceil($total / max(1, $result['per_page']));
?>
<div class="wrap baw-wrap">
    <h1><?php esc_html_e('Submissions', 'book-appointment-wp-floating-widget'); ?></h1>
    <div style="display:flex;gap:10px;align-items:center;margin:8px 0 12px;">
        <form method="get" action="" style="display:flex;gap:8px;align-items:center;">
            <input type="hidden" name="page" value="baw_submissions"/>
            <input type="search" name="s" value="<?php echo isset($_GET['s']) ? esc_attr(wp_unslash($_GET['s'])) : ''; ?>" class="regular-text baw-search" placeholder="<?php esc_attr_e('Search by id, name, phone, email...', 'book-appointment-wp-floating-widget'); ?>"/>
            <label>
                <?php esc_html_e('Filter', 'book-appointment-wp-floating-widget'); ?>
                <select name="contacted" onchange="this.form.submit()">
                    <option value=""><?php esc_html_e('All', 'book-appointment-wp-floating-widget'); ?></option>
                    <option value="1" <?php selected($contacted, '1'); ?>><?php esc_html_e('Contacted', 'book-appointment-wp-floating-widget'); ?></option>
                    <option value="0" <?php selected($contacted, '0'); ?>><?php esc_html_e('Not Contacted', 'book-appointment-wp-floating-widget'); ?></option>
                </select>
            </label>
            <label>
                <?php esc_html_e('Rows', 'book-appointment-wp-floating-widget'); ?>
                <select name="per_page" onchange="this.form.submit()">
                    <?php foreach ([10,20,50,100] as $n): ?><option value="<?php echo $n; ?>" <?php selected($per_page, $n); ?>><?php echo $n; ?></option><?php endforeach; ?>
                </select>
            </label>
        </form>
        <a class="button" href="<?php echo esc_url(admin_url('admin-post.php?action=baw_export_csv')); ?>"><?php esc_html_e('Export CSV', 'book-appointment-wp-floating-widget'); ?></a>
    </div>
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
        <table class="widefat fixed striped baw-table-compact">
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
        <div class="tablenav">
            <div class="tablenav-pages">
                <?php if ($pages > 1): ?>
                    <?php for ($i=1; $i<=$pages; $i++): ?>
                        <?php
                            $url = add_query_arg([
                                'page' => 'baw_submissions',
                                'contacted' => $contacted,
                                'per_page' => $per_page,
                                'paged' => $i,
                            ], admin_url('admin.php'));
                        ?>
                        <a class="button <?php echo $i===$page ? 'button-primary' : ''; ?>" href="<?php echo esc_url($url); ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
        </div>
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


