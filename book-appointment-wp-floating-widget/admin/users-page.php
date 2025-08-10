<?php
if (!defined('ABSPATH')) { exit; }

$groups = BAW_Database::get_user_groups();
$contacted_groups = array_values(array_filter($groups, function($g){ return !empty($g['contactedCount']); }));

// Apply search and pagination server-side for accuracy
$search_q = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
if ($search_q !== '') {
    $needle = strtolower($search_q);
    $contacted_groups = array_values(array_filter($contacted_groups, function($g) use ($needle) {
        $r = $g['latest'];
        $hay = strtolower(($r['id'] ?? '') . ' ' . ($r['name'] ?? '') . ' ' . ($r['email'] ?? '') . ' ' . ($r['mobile'] ?? '') . ' ' . ($r['description'] ?? ''));
        if (strpos($hay, $needle) !== false) return true;
        if (is_numeric($needle) && isset($r['id']) && (int)$r['id'] === (int)$needle) return true;
        return false;
    }));
}
$page = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
$per_page = isset($_GET['per_page']) ? max(1, (int) $_GET['per_page']) : 20;
$total = count($contacted_groups);
$pages = (int) ceil($total / max(1, $per_page));
$offset = ($page - 1) * $per_page;
$contacted_groups = array_slice($contacted_groups, $offset, $per_page);
?>
<div class="wrap baw-wrap baw-manage-users">
    <div class="baw-header">
        <h1><?php esc_html_e('Manage Users', 'book-appointment-wp-floating-widget'); ?></h1>
    </div>
    <div class="baw-toolbar" style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;">
        <form method="get" style="display:flex;align-items:center;gap:10px;">
            <input type="hidden" name="page" value="baw_users"/>
            <input type="search" name="s" value="<?php echo esc_attr($search_q); ?>" id="baw-user-search" class="regular-text baw-search" placeholder="<?php esc_attr_e('Search by name, phone or email...', 'book-appointment-wp-floating-widget'); ?>"/>
            <label>
                <?php esc_html_e('Rows', 'book-appointment-wp-floating-widget'); ?>
                <select name="per_page" onchange="this.form.submit()">
                    <?php foreach ([10,20,50,100] as $n): ?><option value="<?php echo $n; ?>" <?php selected($per_page, $n); ?>><?php echo $n; ?></option><?php endforeach; ?>
                </select>
            </label>
        </form>
        <!-- Single table view; toggle removed per request -->
    </div>

    <?php if (empty($contacted_groups)): ?>
        <div class="baw-empty">
            <img class="baw-empty-img" src="<?php echo esc_url(BAW_PLUGIN_URL . 'assets/icons/empty.svg'); ?>" alt="Empty"/>
            <p><?php esc_html_e('No contacted users yet. Mark submissions as Contacted to see them here.', 'book-appointment-wp-floating-widget'); ?></p>
        </div>
    <?php else: ?>
        <div class="baw-views">
            <div class="baw-view baw-view-table" data-mode="table">
                <table class="widefat fixed striped baw-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('ID', 'book-appointment-wp-floating-widget'); ?></th>
                            <th><?php esc_html_e('Name', 'book-appointment-wp-floating-widget'); ?></th>
                            <th><?php esc_html_e('Email', 'book-appointment-wp-floating-widget'); ?></th>
                            <th><?php esc_html_e('Phone Number', 'book-appointment-wp-floating-widget'); ?></th>
                            <th><?php esc_html_e('Description', 'book-appointment-wp-floating-widget'); ?></th>
                            <th><?php esc_html_e('Visits', 'book-appointment-wp-floating-widget'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="baw-users-table-body">
                        <?php foreach ($contacted_groups as $g): $row=$g['latest'];
                            // Normalize display to ensure values are in correct columns
                            $id_disp = (int) ($row['id'] ?? 0);
                            $name_disp = trim(preg_replace('/\s+/', ' ', (string) ($row['name'] ?? '')));
                            $email_disp = '';
                            $mobile_disp = '';
                            // Try dynamic keys for email/phone
                            $fields_cfg = get_option('baw_form_fields');
                            if (is_array($fields_cfg)) {
                                foreach ($fields_cfg as $f) {
                                    $k = sanitize_key($f['key'] ?? '');
                                    $label = strtolower($f['label'] ?? '');
                                    $type = $f['type'] ?? '';
                                    if ($email_disp === '' && ($type === 'email' || strpos($k,'email')!==false || strpos($label,'email')!==false)) {
                                        if (isset($row[$k])) $email_disp = (string) $row[$k];
                                    }
                                    if ($mobile_disp === '' && ($type === 'tel' || preg_match('/phone|mobile|contact/',$label) || preg_match('/phone|mobile/',$k))) {
                                        if (isset($row[$k])) $mobile_disp = (string) $row[$k];
                                    }
                                }
                            }
                            if ($email_disp === '' && isset($row['email'])) { $email_disp = (string) $row['email']; }
                            if ($mobile_disp === '' && isset($row['mobile'])) { $mobile_disp = (string) $row['mobile']; }
                            $desc_disp = trim(preg_replace('/\s+/', ' ', (string) ($row['description'] ?? '')));
                        ?>
                            <tr class="baw-row" data-identifier="<?php echo esc_attr($g['identifier']); ?>">
                                <td><?php echo $id_disp; ?></td>
                                <td><?php echo esc_html($name_disp); ?></td>
                                <td><?php echo esc_html($email_disp); ?></td>
                                <td><?php echo esc_html($mobile_disp); ?></td>
                                <td class="baw-ellipsis" title="<?php echo esc_attr($desc_disp); ?>"><?php echo esc_html($desc_disp); ?></td>
                                <td><?php echo (int) $g['count']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="tablenav"><div class="tablenav-pages">
                    <?php if ($pages > 1): for ($i=1; $i<=$pages; $i++):
                        $url = add_query_arg([
                            'page' => 'baw_users',
                            's' => $search_q,
                            'per_page' => $per_page,
                            'paged' => $i,
                        ], admin_url('admin.php'));
                    ?>
                        <a class="button <?php echo $i===$page ? 'button-primary' : ''; ?>" href="<?php echo esc_url($url); ?>"><?php echo $i; ?></a>
                    <?php endfor; endif; ?>
                </div></div>
            </div>
        </div>
    <?php endif; ?>

    <div class="baw-offcanvas" id="baw-offcanvas" hidden>
        <div class="baw-offcanvas-overlay"></div>
        <div class="baw-offcanvas-panel">
            <div class="baw-offcanvas-header">
                <h2><?php esc_html_e('User Profile', 'book-appointment-wp-floating-widget'); ?></h2>
                <button type="button" class="baw-offcanvas-close">&times;</button>
            </div>
            <div class="baw-offcanvas-body" id="baw-profile"></div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function(){
        const search = document.getElementById('baw-user-search');
        const views = document.querySelectorAll('.baw-view');
        const toggleBtn = document.getElementById('baw-toggle-view');
        const offcanvas = document.getElementById('baw-offcanvas');
        const closeOff = offcanvas.querySelector('.baw-offcanvas-close');
        const overlay = offcanvas.querySelector('.baw-offcanvas-overlay');

        function setMode(){
            views.forEach(v=>{ v.hidden = (v.getAttribute('data-mode') !== 'table'); });
        }

        function filterLists(q){
            q = q.toLowerCase();
            document.querySelectorAll('.baw-card, .baw-view-table tbody tr').forEach(function(el){
                const text = el.textContent.toLowerCase();
                el.style.display = text.indexOf(q) !== -1 ? '' : 'none';
            });
        }
        search.addEventListener('input', ()=>filterLists(search.value));

        function openProfile(identifier){
            const params = new URLSearchParams({ action: 'baw_get_user_profile', identifier, nonce: BAW_ADMIN.nonce });
            fetch(BAW_ADMIN.ajax_url + '?' + params.toString())
                .then(r=>r.json())
                .then(json=>{
                    if (!json.success) return;
                    const visits = json.data.visits || [];
                    const profile = document.getElementById('baw-profile');
                    if (!visits.length) { profile.innerHTML = '<p>No visits found.</p>'; return; }
                    const head = visits[0];
                    const name = head.name || '';
                    const phone = head.mobile || '';
                    const email = head.email || '';
                    let html = '';
                    html += `<div class=\"baw-profile-header\"><h3>${name}</h3><div>${email}</div><div>${phone}</div></div>`;
                    html += '<div class=\"baw-visits\">';
                    visits.forEach(function(v){
                        html += `
                            <div class=\"baw-visit\">
                                <div class=\"baw-visit-top\">
                                    <div><strong>ID:</strong> ${v.id} &nbsp; <strong>Date:</strong> ${v.created_at}</div>
                                    <div><strong>Contacted:</strong> ${v.contacted==1?'Yes':'No'}</div>
                                </div>
                                <div class=\"baw-visit-fields\">
                                    <div><strong>Description:</strong> ${v.description||''}</div>
                                </div>
                                <div class=\"baw-visit-notes\">
                                    <label>Charges (e.g., ₹100): <input type=\"text\" data-id=\"${v.id}\" class=\"baw-charges\" value=\"${v.charges||''}\"></label>
                                    <label>Prescription:<br/><textarea data-id=\"${v.id}\" class=\"baw-prescription\" rows=\"3\">${v.prescription||''}</textarea></label>
                                    <button class=\"button button-secondary baw-save-visit\" data-id=\"${v.id}\">Save Notes</button>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    profile.innerHTML = html;
                    offcanvas.hidden = false;
                });
        }

        document.addEventListener('click', function(e){
            const row = e.target.closest('.baw-view-table tbody tr');
            if (row) {
                e.preventDefault();
                e.stopPropagation();
                openProfile(row.getAttribute('data-identifier'));
            }
        });

        function closePanel(){ offcanvas.hidden = true; }
        closeOff.addEventListener('click', closePanel);
        overlay.addEventListener('click', closePanel);

        setMode();
    });
        document.getElementById('baw-profile').addEventListener('click', async function(e){
            const btn = e.target.closest('.baw-save-visit');
            if (!btn) return;
            const id = btn.getAttribute('data-id');
            const charges = document.querySelector(`.baw-charges[data-id="${id}"]`).value;
            const prescription = document.querySelector(`.baw-prescription[data-id="${id}"]`).value;
            const fd = new FormData();
            fd.append('action','baw_update_visit');
            fd.append('nonce', BAW_ADMIN.nonce);
            fd.append('id', id);
            fd.append('charges', charges);
            fd.append('prescription', prescription);
            const res = await fetch(BAW_ADMIN.ajax_url, { method:'POST', body: fd });
            const json = await res.json();
            if (json.success) { btn.textContent = 'Saved'; setTimeout(()=>btn.textContent='Save Notes', 1200); }
        });
    });
    </script>
</div>


