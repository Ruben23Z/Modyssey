<?php require_once __DIR__ . '/../../core/Lang.php'; $pageTitle = Lang::t('admin_users_page_title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

    <style>
        .wrap {
            padding: 1.5rem 0;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .header h2 {
            font-size: 18px;
            font-weight: 500;
            color: var(--text);
            margin: 0;
        }

        .btn-settings {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            padding: 7px 14px;
            border-radius: 8px;
            border: 1px solid var(--border);
            color: var(--text-muted);
            background: transparent;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s;
        }

        .btn-settings:hover {
            background: var(--bg4);
        }

        .tbl {
            width: 100%;
            border-collapse: collapse;
            background: transparent !important;
            color: var(--text) !important;
        }

        .tbl thead th {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted) !important;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 14px 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border) !important;
            background: var(--bg4) !important;
        }

        .tbl tbody tr {
            border-bottom: 1px solid var(--border) !important;
            transition: background 0.12s;
            background: transparent !important;
        }

        .tbl tbody tr:last-child {
            border-bottom: none !important;
        }

        .tbl tbody tr:hover {
            background: rgba(255, 255, 255, 0.02) !important;
        }

        .tbl td {
            padding: 14px 1rem;
            font-size: 14px;
            vertical-align: middle;
            background: transparent !important;
            color: var(--text) !important;
        }

        .td-id {
            color: var(--text-muted);
            font-size: 13px;
            font-family: monospace;
            width: 48px;
        }

        .td-email {
            color: var(--text-muted);
            font-size: 13px;
        }

        .name-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 500;
            flex-shrink: 0;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 500;
            padding: 3px 10px;
            border-radius: 99px;
        }

        .badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
        }

        .role-select-wrap {
            position: relative;
            display: inline-flex;
            align-items: center;
        }

        .role-select-wrap::after {
            content: '';
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: 4px solid transparent;
            border-top-color: var(--text-muted);
            margin-top: 2px;
            pointer-events: none;
        }
        .toast.success {
            background: rgba(82, 192, 124, 0.12);
            color: #80d4a0;
            border-color: rgba(82, 192, 124, 0.25);
        }

        .toast.error {
            background: rgba(224, 85, 85, 0.12);
            color: #f08080;
            border-color: rgba(224, 85, 85, 0.25);
        }
        select.role-sel {
            appearance: none;
            font-size: 13px;
            padding: 6px 28px 6px 10px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--bg4) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='7' viewBox='0 0 12 7'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%236470a0' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat right 10px center !important;
            color: var(--text) !important;
            cursor: pointer;
            transition: border-color 0.15s;
            outline: none;
        }

        select.role-sel:hover {
            border-color: var(--accent) !important;
        }

        select.role-sel:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .saving-dot {
            display: none;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #378ADD;
            animation: pulse 0.8s infinite alternate;
            margin-left: 8px;
        }

        @keyframes pulse {
            from {
                opacity: 0.3;
            }
            to {
                opacity: 1;
            }
        }

        .toast-area {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            z-index: 999;
        }

        .toast {
            font-size: 13px;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 8px;
            border: 1px solid;
            opacity: 0;
            transform: translateY(8px);
            transition: opacity 0.2s, transform 0.2s;
            max-width: 320px;
        }


        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }
    </style>

    <main>
        <div class="container">
            <section class="section">
                <div class="wrap">
                    <div class="header">
                        <h2><?= Lang::t('manage_users') ?></h2>
                        <a href="<?= BASE_URL ?>/admin/settings" class="btn-settings">
                            <?= Lang::t('system_settings_link') ?>
                        </a>
                    </div>

                    <?php if (!empty($users)): ?>
                        <div class="card" style="border: 1px solid var(--border); overflow: hidden; border-radius: var(--radius-lg);">
                            <table class="tbl">
                                <thead>
                                <tr>
                                    <th style="width:48px">ID</th>
                                    <th><?= Lang::t('th_user') ?></th>
                                    <th><?= Lang::t('th_email') ?></th>
                                    <th><?= Lang::t('th_role') ?></th>
                                    <th style="width:170px"><?= Lang::t('th_change_role') ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $av_bg = ['rgba(55,138,221,0.15)', 'rgba(99,153,34,0.15)', 'rgba(127,119,221,0.15)', 'rgba(180,80,80,0.15)', 'rgba(180,140,60,0.15)'];
                                $av_tx = ['#7ab8f0', '#a8d96b', '#b0a8f0', '#e07a7a', '#d4a543'];
                                $dot_colors = [
                                        'guest' => '#888780',
                                        'user' => '#378ADD',
                                        'sympathizer' => '#7F77DD',
                                        'admin' => '#639922',
                                        ];
                                $role_labels = [
                                        'guest' => Lang::t('role_guest'),
                                        'user' => Lang::t('role_user'),
                                        'sympathizer' => Lang::t('role_sympathizer'),
                                        'admin' => Lang::t('role_admin_full'),
                                        ];
                                $i = 0;
                                ?>
                                <?php foreach ($users as $user):
                                    $role = htmlspecialchars($user['role_name']);
                                    $uid = (int)$user['id'];
                                    $parts = explode('.', $user['username']);
                                    $initials = strtoupper(implode('', array_map(fn($p) => $p[0], $parts)));
                                    $initials = substr($initials, 0, 2);
                                    $ai = $i % count($av_bg);
                                    $dot_color = $dot_colors[$user['role_name']] ?? '#888780';
                                    $label = $role_labels[$user['role_name']] ?? ucfirst($user['role_name']);
                                    $i++;
                                    ?>
                                    <tr id="row-<?= $uid ?>">
                                        <td class="td-id">#<?= $uid ?></td>
                                        <td>
                                            <div class="name-cell">
                                                <div class="avatar"
                                                     style="background:<?= $av_bg[$ai] ?>;color:<?= $av_tx[$ai] ?>"><?= $initials ?></div>
                                                <strong><?= htmlspecialchars($user['username']) ?></strong>
                                            </div>
                                        </td>
                                        <td class="td-email"><?= htmlspecialchars($user['email']) ?></td>
                                        <td id="badge-<?= $uid ?>">
                        <span class="role-badge badge-<?= $role ?>">
                          <span class="badge-dot" style="background:<?= $dot_color ?>"></span>
                          <?= $label ?>
                        </span>
                                        </td>
                                        <td>
                                            <div style="display:flex;align-items:center">
                                                <div class="role-select-wrap">
                                                    <select class="role-sel" data-uid="<?= $uid ?>">
                                                        <option value="1" <?= $user['role_name'] === 'guest' ? 'selected' : '' ?>>
                                                            <?= Lang::t('role_guest') ?>
                                                        </option>
                                                        <option value="2" <?= $user['role_name'] === 'user' ? 'selected' : '' ?>>
                                                            <?= Lang::t('role_user') ?>
                                                        </option>
                                                        <option value="3" <?= $user['role_name'] === 'sympathizer' ? 'selected' : '' ?>>
                                                            <?= Lang::t('role_sympathizer') ?>
                                                        </option>
                                                        <option value="4" <?= $user['role_name'] === 'admin' ? 'selected' : '' ?>>
                                                            <?= Lang::t('role_admin_full') ?>
                                                        </option>
                                                    </select>
                                                </div>
                                                <span class="saving-dot" id="dot-<?= $uid ?>"></span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p style="color:var(--text-muted);font-size:14px"><?= Lang::t('no_users_found') ?></p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

    <div class="toast-area" id="toast-area"></div>

    <script>
        const ROLE_META = {
            guest:       { label: <?= json_encode(Lang::t('role_guest')) ?>,     dot: '#5a5d6e' },
            user:        { label: <?= json_encode(Lang::t('role_user')) ?>,    dot: '#7ab8f0' },
            sympathizer: { label: <?= json_encode(Lang::t('role_sympathizer')) ?>,  dot: '#b0a8f0' },
            admin:       { label: <?= json_encode(Lang::t('role_admin_full')) ?>, dot: '#a8d96b' },
        };
        const ROLE_BY_ID = { 1: 'guest', 2: 'user', 3: 'sympathizer', 4: 'admin' };

        function renderBadge(roleKey) {
            const m = ROLE_META[roleKey] || { label: roleKey, dot: '#5a5d6e' };
            return `<span class="role-badge badge-${roleKey}">
        <span class="badge-dot" style="background:${m.dot}"></span>
        ${m.label}
    </span>`;
        }

        function showToast(msg, type) {
            const area = document.getElementById('toast-area');
            const t = document.createElement('div');
            t.className = 'toast ' + type;
            t.innerHTML = msg;
            area.appendChild(t);
            requestAnimationFrame(() => requestAnimationFrame(() => t.classList.add('show')));
            setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 250); }, 3000);
        }

        document.querySelectorAll('.role-sel').forEach(sel => {
            sel.addEventListener('change', () => {
                const uid = sel.dataset.uid;
                const roleId = sel.value;
                const roleKey = ROLE_BY_ID[roleId];
                const dot = document.getElementById('dot-' + uid);

                sel.disabled = true;
                dot.style.display = 'block';

                fetch('<?= BASE_URL ?>/api/users/role', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: uid, role_id: roleId })
                })
                    .then(res => { if (!res.ok) throw new Error(<?= json_encode(Lang::t('network_error')) ?>); return res.json(); })
                    .then(data => {
                        if (!data.success) throw new Error(data.error || <?= json_encode(Lang::t('unknown_error')) ?>);
                        const key = data.role || roleKey;
                        document.getElementById('badge-' + uid).innerHTML = renderBadge(key);
                        showToast(<?= json_encode(Lang::t('role_updated_to')) ?> + ` <strong>${ROLE_META[key]?.label || key}</strong>.`, 'success');
                    })
                    .catch(err => {
                        showToast(err.message || <?= json_encode(Lang::t('role_update_error')) ?>, 'error');
                    })
                    .finally(() => {
                        dot.style.display = 'none';
                        sel.disabled = false;
                    });
            });
        });
    </script>

<?php require __DIR__ . '/../layout/footer.php'; ?>