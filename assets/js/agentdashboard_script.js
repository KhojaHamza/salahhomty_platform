// agentdashboard_script.js — version corrigée et complète

// ─── State ────────────────────────────────────────────────
let currentFilter = 'all';
let currentPage   = 1;
let currentSearch = '';
let openReportId  = null;
let searchTimer   = null;
const isAdmin = window.APP?.isAdmin ?? false;

// ─── API Helper ───────────────────────────────────────────
/**
 * FIX: gestion robuste des erreurs réseau et JSON invalide.
 * On vérifie Content-Type avant de parser pour éviter le crash
 * quand api.php retourne du HTML (erreur PHP 500, etc.)
 */
async function api(action, params = {}, method = 'GET') {
    try {
        let url  = `api.php?action=${encodeURIComponent(action)}`;
        let opts = { method, credentials: 'same-origin' };

        if (method === 'GET') {
            Object.entries(params).forEach(([k, v]) => {
                if (v !== null && v !== undefined)
                    url += `&${encodeURIComponent(k)}=${encodeURIComponent(v)}`;
            });
        } else {
            const fd = new FormData();
            fd.append('action', action);
            Object.entries(params).forEach(([k, v]) => fd.append(k, v ?? ''));
            opts.body = fd;
        }

        const res = await fetch(url, opts);

        // FIX: vérifier Content-Type — si ce n'est pas du JSON, afficher
        // un message d'erreur utile au lieu de crasher silencieusement.
        const ct = res.headers.get('content-type') || '';
        if (!ct.includes('application/json')) {
            const text = await res.text();
            console.error(`api(${action}) — réponse non-JSON (HTTP ${res.status}):`, text.substring(0, 300));
            return {
                success: false,
                message: `Erreur serveur (HTTP ${res.status}). Vérifiez api.php. Voir console pour détails.`
            };
        }

        const data = await res.json();
        return data;
    } catch (e) {
        console.error(`api(${action}) — exception:`, e);
        return { success: false, message: 'Erreur réseau : ' + e.message };
    }
}

// ─── Toast ────────────────────────────────────────────────
let toastTimer;
function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    document.getElementById('toast-msg').textContent = msg;
    t.className = 'toast show ' + type;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => t.classList.remove('show'), 3500);
}

// ─── Page Navigation ──────────────────────────────────────
function showPage(p) {
    document.querySelectorAll('.page').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
    document.getElementById('page-' + p)?.classList.add('active');
    const titles = { dashboard: 'Dashboard', reports: 'Reports', profile: 'My Profile' };
    document.getElementById('topbar-title').textContent = titles[p] || '';
    const navMap = { dashboard: 'nav-dashboard', profile: 'nav-profile', reports: 'nav-reports' };
    if (navMap[p]) document.getElementById(navMap[p])?.classList.add('active');

    // Vider la recherche quand on change de page
    if (p !== 'reports') {
        currentSearch = '';
        const si = document.getElementById('search-input');
        if (si) si.value = '';
    }
}

// ─── Stats ────────────────────────────────────────────────
async function loadStats() {
    const r = await api('stats');
    if (!r.success) {
        console.warn('loadStats échoué:', r.message);
        return;
    }
    const s = r.stats;
    const setVal = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val ?? 0;
    };
    setVal('stat-total',    s.total);
    setVal('stat-open',     s.open);
    setVal('stat-progress', s.in_progress);
    setVal('stat-resolved', s.resolved);
    setVal('stat-overdue',  s.overdue);
    setVal('badge-total',   s.total);
}

// ─── Dashboard Recent ─────────────────────────────────────
async function loadDashRecent(filter = 'all', tabEl = null) {
    if (tabEl) {
        document.querySelectorAll('#page-dashboard .ftab').forEach(t => t.classList.remove('active'));
        tabEl.classList.add('active');
    }
    const tb = document.getElementById('dash-tbody');
    tb.innerHTML = `<tr class="loading-row"><td colspan="5"><span class="spinner dark"></span> Loading…</td></tr>`;

    const r = await api('recent_reports', { filter, limit: 8 });
    if (!r.success) {
        tb.innerHTML = `<tr><td colspan="5"><div class="empty"><div class="ei">⚠️</div><p>${esc(r.message)}</p></div></td></tr>`;
        return;
    }
    if (!r.reports || !r.reports.length) {
        tb.innerHTML = `<tr><td colspan="5"><div class="empty"><div class="ei">📭</div><p>No reports found</p></div></td></tr>`;
        return;
    }
    tb.innerHTML = r.reports.map(rep => `
        <tr onclick="openReport(${parseInt(rep.id)})">
          <td><span class="c-id">#${parseInt(rep.id)}</span></td>
          <td><div class="c-title">${esc(rep.title || (rep.description || '').substring(0, 50) || '—')}</div></td>
          <td style="font-size:12px;color:var(--muted)">${esc(rep.citizen_name || '—')}</td>
          <td>${statusBadge(rep.status)}</td>
          <td style="font-size:12px;color:var(--muted)">${formatDate(rep.created_at)}</td>
        </tr>`).join('');
}

// ─── Workload ─────────────────────────────────────────────
async function loadWorkload() {
    const r  = await api('workload');
    const el = document.getElementById('workload-list');
    if (!r.success) {
        el.innerHTML = `<div style="padding:12px;font-size:12px;color:var(--red)">${esc(r.message)}</div>`;
        return;
    }
    if (!r.workload || !r.workload.length) {
        el.innerHTML = `<div style="padding:16px;font-size:12px;color:var(--muted);text-align:center">No agents found</div>`;
        return;
    }
    const colors = ['var(--accent)', '#6366f1', '#f59e0b', '#ec4899', '#8b5cf6'];
    el.innerHTML = r.workload.slice(0, 5).map((a, i) => {
        const ini   = initials(a.name);
        const count = parseInt(a.open_count) || 0;
        const cls   = count > 8 ? 'wls-busy' : count > 0 ? 'wls-active' : 'wls-free';
        const lbl   = count > 8 ? 'busy' : count > 0 ? 'active' : 'free';
        return `<div class="workload-item">
          <div class="wl-avatar" style="background:${colors[i % colors.length]}">${ini}</div>
          <div style="flex:1;min-width:0">
            <div class="wl-name">${esc(a.name)}</div>
            <div class="wl-meta">${a.open_count} open · ${a.total_count} total</div>
          </div>
          <span class="wl-status ${cls}">${lbl}</span>
        </div>`;
    }).join('');
}

// ─── Category Chart ───────────────────────────────────────
async function loadCategoryChart() {
    const r  = await api('category_stats');
    const el = document.getElementById('category-chart');
    if (!r.success || !r.categories || !r.categories.length) {
        el.innerHTML = `<div style="font-size:12px;color:var(--muted);text-align:center;padding:16px">No data</div>`;
        return;
    }
    const max  = Math.max(...r.categories.map(c => parseInt(c.total) || 0));
    const clrs = ['var(--blue)', 'var(--yellow)', 'var(--green)', '#0891b2', 'var(--muted2)'];
    el.innerHTML = r.categories.slice(0, 6).map((c, i) => {
        const pct = max ? Math.round((parseInt(c.total) / max) * 100) : 0;
        return `<div class="bar-row">
          <div class="bar-label" title="${esc(c.category || 'None')}">${esc(c.category || 'None')}</div>
          <div class="bar-track"><div class="bar-fill" style="width:${pct}%;background:${clrs[i % clrs.length]}"></div></div>
          <div class="bar-count">${c.total}</div>
        </div>`;
    }).join('');
}

// ─── Reports Table ────────────────────────────────────────
/**
 * FIX PRINCIPAL : loadReports() est maintenant propre et autonome.
 * Elle utilise les variables d'état globales (currentFilter, currentPage, currentSearch)
 * et peut recevoir des overrides optionnels en paramètre.
 */
async function loadReports(page = null, filter = null, search = null) {
    // Appliquer les overrides si fournis
    if (filter !== null) currentFilter = filter;
    if (search !== null) currentSearch = search;
    if (page   !== null) currentPage   = page;

    const tb = document.getElementById('full-tbody');
    if (!tb) return; // page pas encore dans le DOM

    tb.innerHTML = `<tr class="loading-row"><td colspan="8"><span class="spinner dark"></span> Loading…</td></tr>`;

    const r = await api('reports', {
        filter: currentFilter,
        page:   currentPage,
        search: currentSearch
    });

    if (!r.success) {
        tb.innerHTML = `<tr><td colspan="8"><div class="empty"><div class="ei">⚠️</div><p>${esc(r.message)}</p></div></td></tr>`;
        return;
    }

    const titleMap = { all: 'All Reports', pending: 'Open Reports', progress: 'In Progress', resolved: 'Resolved Reports' };
    const titleEl  = document.getElementById('reports-page-title');
    if (titleEl) titleEl.textContent = titleMap[currentFilter] || 'Reports';

    const countEl = document.getElementById('full-count');
    const total   = r.total ?? 0;
    if (countEl) countEl.textContent = `${total} report${total !== 1 ? 's' : ''}`;

    if (!r.reports || !r.reports.length) {
        tb.innerHTML = `<tr><td colspan="8"><div class="empty"><div class="ei">📭</div><p>No reports found</p></div></td></tr>`;
        renderPagination(0, 0);
        return;
    }

    tb.innerHTML = r.reports.map(rep => `
        <tr onclick="openReport(${parseInt(rep.id)})">
          <td><span class="c-id">#${parseInt(rep.id)}</span></td>
          <td>
            <div class="c-title">${esc(rep.title || (rep.description || '').substring(0, 50) || '—')}</div>
            <div class="c-sub">${parseInt(rep.comment_count) || 0} comment${(parseInt(rep.comment_count) || 0) !== 1 ? 's' : ''}</div>
          </td>
          <td style="font-size:12px">
            <div>${esc(rep.citizen_name || '—')}</div>
            <div class="c-sub">${esc(rep.citizen_email || '')}</div>
          </td>
          <td style="font-size:12px;color:var(--muted)">${esc(rep.category_name || '—')}</td>
          <td style="font-size:12px;color:var(--muted)">${esc(rep.agent_name || '—')}</td>
          <td>${statusBadge(rep.status)}</td>
          <td style="font-size:12px;color:var(--muted)">${formatDate(rep.created_at)}</td>
          <td onclick="event.stopPropagation()">
            <div class="act-btns">
              <button class="act-btn" onclick="openReport(${parseInt(rep.id)})">✏️ View</button>
            </div>
          </td>
        </tr>`).join('');

    renderPagination(r.page ?? 1, r.pages ?? 1);
}

function renderPagination(page, pages) {
    const cont = document.getElementById('page-btns');
    if (!cont) return;
    if (pages <= 1) { cont.innerHTML = ''; return; }

    let html = `<button class="page-btn" onclick="loadReports(${page - 1})" ${page <= 1 ? 'disabled' : ''}>‹</button>`;
    for (let i = 1; i <= pages; i++) {
        if (pages > 7 && Math.abs(i - page) > 2 && i !== 1 && i !== pages) {
            if (Math.abs(i - page) === 3) html += `<button class="page-btn" disabled>…</button>`;
            continue;
        }
        html += `<button class="page-btn ${i === page ? 'active-page' : ''}" onclick="loadReports(${i})">${i}</button>`;
    }
    html += `<button class="page-btn" onclick="loadReports(${page + 1})" ${page >= pages ? 'disabled' : ''}>›</button>`;
    cont.innerHTML = html;
}

/**
 * FIX: applyFilter gère proprement la navigation et le rechargement.
 * C'est le seul point d'entrée pour filtrer les reports.
 */
function applyFilter(f) {
    currentFilter = f;
    currentPage   = 1;

    // Mettre à jour les onglets filtres
    ['all', 'pending', 'progress', 'resolved'].forEach(k => {
        const el = document.getElementById('ftab-' + k);
        if (el) el.classList.toggle('active', k === f);
    });

    // Mettre à jour le titre
    const titleMap = { all: 'All Reports', pending: 'Open Reports', progress: 'In Progress', resolved: 'Resolved Reports' };
    const titleEl  = document.getElementById('reports-page-title');
    if (titleEl) titleEl.textContent = titleMap[f] || 'Reports';

    // Naviguer vers la page reports si nécessaire
    if (!document.getElementById('page-reports').classList.contains('active')) {
        showPage('reports');
    } else {
        // Déjà sur reports : juste mettre à jour la nav highlight
        document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
        document.getElementById('nav-reports')?.classList.add('active');
    }

    // FIX: toujours charger les reports après avoir appliqué le filtre
    loadReports();
}

// ─── Search ───────────────────────────────────────────────
function onSearchInput(val) {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        currentSearch = val.trim();
        currentPage   = 1;

        // FIX: basculer sur la page reports si on cherche depuis le dashboard
        if (!document.getElementById('page-reports').classList.contains('active')) {
            showPage('reports');
        }
        loadReports();
    }, 350);
}

// ─── Report Modal ─────────────────────────────────────────
async function openReport(id) {
    openReportId = id;
    const overlay = document.getElementById('report-modal');
    overlay.classList.add('open');
    document.getElementById('modal-title').textContent = `Report #${id}`;
    document.getElementById('modal-body').innerHTML =
        `<div style="text-align:center;padding:30px"><span class="spinner dark"></span> Loading report…</div>`;

    const [rDetail, rAgents] = await Promise.all([
        api('report_detail', { id }),
        api('agents')
    ]);

    if (!rDetail.success) {
        document.getElementById('modal-body').innerHTML =
            `<div class="empty"><div class="ei">⚠️</div><p>${esc(rDetail.message)}</p></div>`;
        return;
    }

    const rep      = rDetail.report;
    const comments = rDetail.comments || [];
    const logs     = rDetail.logs     || [];
    const agents   = rAgents.agents   || [];

    document.getElementById('modal-title').textContent = `Report #${rep.id} — ${rep.status}`;

    const agentsOptions = `<option value="">— Unassigned —</option>` +
        agents.map(a => `<option value="${a.id}" ${rep.assigned_to == a.id ? 'selected' : ''}>${esc(a.name)} (${a.role})</option>`).join('');

    const commentsHtml = comments.length
        ? comments.map(c => `
          <div class="comment-item" id="comment-${parseInt(c.id)}">
            <div class="comment-avatar ${c.author_role === 'admin' ? 'admin-av' : ''}">${initials(c.author_name || '?')}</div>
            <div class="comment-bubble">
              <div class="comment-meta">
                <span class="comment-author">${esc(c.author_name || 'Unknown')}</span>
                <span>${esc(c.author_role || '')}</span>
                <span>${formatDate(c.created_at)}</span>
                ${isAdmin ? `<button class="comment-del" onclick="deleteComment(${parseInt(c.id)})">🗑 Delete</button>` : ''}
              </div>
              <div class="comment-text">${esc(c.content)}</div>
            </div>
          </div>`).join('')
        : `<div class="no-comments">💬 No comments yet. Be the first to add one.</div>`;

    const logsHtml = logs.length
        ? logs.map(l => `
          <div class="log-item">
            <div class="log-dot">📝</div>
            <div class="log-text">
              <strong>${esc(l.changed_by_name || 'System')}</strong> changed status
              from <strong>${esc(l.old_status || '—')}</strong> to <strong>${esc(l.new_status || '—')}</strong>
              ${l.note ? `<br><span style="color:var(--muted);font-size:11px">${esc(l.note)}</span>` : ''}
            </div>
            <div class="log-time">${formatDate(l.created_at)}</div>
          </div>`).join('')
        : `<div style="font-size:12px;color:var(--muted2);padding:8px 0">No status changes logged yet.</div>`;

    const imageHtml = rep.image
        ? `<img class="report-image" src="${esc(rep.image)}" alt="Report image"
             onclick="openLightbox('${esc(rep.image)}')"
             onerror="this.style.display='none'"/>`
        : '';

    const mapHtml = rep.latitude && rep.longitude
        ? `<a class="report-map-link" href="https://maps.google.com/?q=${encodeURIComponent(rep.latitude)},${encodeURIComponent(rep.longitude)}"
             target="_blank" rel="noopener">📍 View on map (${parseFloat(rep.latitude).toFixed(5)}, ${parseFloat(rep.longitude).toFixed(5)})</a>`
        : '<span style="font-size:12px;color:var(--muted2)">📍 No location data</span>';

    // FIX: deadline — normaliser le format pour l'input datetime-local
    const deadlineVal = rep.deadline
        ? rep.deadline.replace(' ', 'T').substring(0, 16)
        : '';

    document.getElementById('modal-body').innerHTML = `
        <!-- INFO GRID -->
        <div class="info-grid">
          <div class="info-cell"><div class="info-cell-lbl">Report ID</div><div class="info-cell-val">#${parseInt(rep.id)}</div></div>
          <div class="info-cell"><div class="info-cell-lbl">Submitted</div><div class="info-cell-val">${formatDate(rep.created_at)}</div></div>
          <div class="info-cell"><div class="info-cell-lbl">Citizen</div><div class="info-cell-val">${esc(rep.citizen_name || '—')}</div></div>
          <div class="info-cell"><div class="info-cell-lbl">Contact</div><div class="info-cell-val">
            ${rep.citizen_email ? `<a href="mailto:${esc(rep.citizen_email)}">${esc(rep.citizen_email)}</a>` : '—'}
            ${rep.citizen_phone ? `<br><small>${esc(rep.citizen_phone)}</small>` : ''}
          </div></div>
          <div class="info-cell"><div class="info-cell-lbl">Category</div><div class="info-cell-val">${esc(rep.category_name || '—')}</div></div>
          <div class="info-cell"><div class="info-cell-lbl">Deadline</div><div class="info-cell-val">${rep.deadline ? formatDate(rep.deadline) : '—'}</div></div>
        </div>

        <!-- TITLE & DESCRIPTION -->
        ${rep.title ? `<div class="form-group"><label class="form-label">Title</label><input class="form-control" value="${esc(rep.title)}" readonly/></div>` : ''}
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea class="form-control" readonly style="min-height:70px">${esc(rep.description || 'No description provided.')}</textarea>
        </div>

        <!-- IMAGE -->
        ${imageHtml ? `<div class="form-group">${imageHtml}</div>` : ''}
        <div class="form-group">${mapHtml}</div>

        <!-- UPDATE STATUS -->
        <div class="modal-section">Update Status</div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Status</label>
            <select class="form-control" id="m-status">
              <option value="Open"        ${rep.status === 'Open'        ? 'selected' : ''}>⏳ Open</option>
              <option value="In progress" ${rep.status === 'In progress' ? 'selected' : ''}>🔄 In Progress</option>
              <option value="Resolved"    ${rep.status === 'Resolved'    ? 'selected' : ''}>✅ Resolved</option>
              <option value="Closed"      ${rep.status === 'Closed'      ? 'selected' : ''}>🔒 Closed</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Assign To</label>
            <select class="form-control" id="m-agent">${agentsOptions}</select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Deadline</label>
          <input class="form-control" type="datetime-local" id="m-deadline" value="${deadlineVal}"/>
        </div>
        <div class="form-group">
          <label class="form-label">Internal Note (logged)</label>
          <textarea class="form-control" id="m-note" placeholder="Add a processing note…" style="min-height:60px"></textarea>
        </div>
        <div class="modal-actions" style="margin-bottom:20px">
          <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
          <button class="btn btn-primary" id="save-btn" onclick="saveReport()">💾 Save Changes</button>
        </div>

        <!-- COMMENTS -->
        <div class="modal-section">💬 Comments (${comments.length})</div>
        <div class="comments-list" id="comments-list">${commentsHtml}</div>
        <div style="display:flex;gap:8px;margin-bottom:20px">
          <textarea class="form-control" id="new-comment" placeholder="Write a comment…" style="min-height:50px;flex:1"></textarea>
          <button class="btn btn-primary btn-sm" style="align-self:flex-end;white-space:nowrap" onclick="addComment()">Send 💬</button>
        </div>

        <!-- ACTIVITY LOG -->
        <div class="modal-section">📋 Activity Log</div>
        <div>${logsHtml}</div>
    `;
}

async function saveReport() {
    if (!openReportId) return;

    const btn      = document.getElementById('save-btn');
    const status   = document.getElementById('m-status')?.value;
    const agentId  = document.getElementById('m-agent')?.value;
    const deadline = document.getElementById('m-deadline')?.value;
    const note     = document.getElementById('m-note')?.value.trim();

    if (!status) { showToast('⚠️ Statut manquant', 'error'); return; }

    btn.disabled   = true;
    btn.innerHTML  = '<span class="spinner"></span> Saving…';

    // FIX: appels séquentiels plutôt que Promise.all pour mieux identifier les erreurs
    const r1 = await api('update_status', { id: openReportId, status, note }, 'POST');
    if (!r1.success) {
        showToast('❌ ' + r1.message, 'error');
        btn.disabled  = false;
        btn.innerHTML = '💾 Save Changes';
        return;
    }

    const r2 = await api('assign_report', { id: openReportId, agent_id: agentId }, 'POST');
    if (!r2.success) {
        showToast('❌ ' + r2.message, 'error');
        btn.disabled  = false;
        btn.innerHTML = '💾 Save Changes';
        return;
    }

    const r3 = await api('set_deadline', { id: openReportId, deadline }, 'POST');
    if (!r3.success) {
        showToast('❌ ' + r3.message, 'error');
        btn.disabled  = false;
        btn.innerHTML = '💾 Save Changes';
        return;
    }

    btn.disabled  = false;
    btn.innerHTML = '💾 Save Changes';
    showToast('✅ Report updated successfully');
    closeModal();
    loadStats();

    if (document.getElementById('page-reports').classList.contains('active'))   loadReports();
    if (document.getElementById('page-dashboard').classList.contains('active')) loadDashRecent('all');
}

async function addComment() {
    if (!openReportId) return;
    const textarea = document.getElementById('new-comment');
    const content  = textarea?.value.trim();
    if (!content) { showToast('⚠️ Comment cannot be empty.', 'error'); return; }

    const r = await api('add_comment', { report_id: openReportId, content }, 'POST');
    if (!r.success) { showToast('❌ ' + r.message, 'error'); return; }

    textarea.value = '';
    const c   = r.comment;
    const ini = initials(c.author_name || '?');
    const html = `
        <div class="comment-item" id="comment-${parseInt(c.id)}">
          <div class="comment-avatar ${c.author_role === 'admin' ? 'admin-av' : ''}">${ini}</div>
          <div class="comment-bubble">
            <div class="comment-meta">
              <span class="comment-author">${esc(c.author_name || 'You')}</span>
              <span>${esc(c.author_role || '')}</span>
              <span>Just now</span>
              ${isAdmin ? `<button class="comment-del" onclick="deleteComment(${parseInt(c.id)})">🗑 Delete</button>` : ''}
            </div>
            <div class="comment-text">${esc(c.content)}</div>
          </div>
        </div>`;
    const list = document.getElementById('comments-list');
    const noC  = list?.querySelector('.no-comments');
    if (noC) noC.remove();
    list?.insertAdjacentHTML('beforeend', html);
    if (list) list.scrollTop = list.scrollHeight;
    showToast('💬 Comment added');
}

async function deleteComment(id) {
    if (!confirm('Delete this comment?')) return;
    const r = await api('delete_comment', { id }, 'POST');
    if (!r.success) { showToast('❌ ' + r.message, 'error'); return; }
    document.getElementById('comment-' + id)?.remove();
    showToast('🗑 Comment deleted');
}

function closeModal() {
    document.getElementById('report-modal').classList.remove('open');
    openReportId = null;
}

// ─── Profile ──────────────────────────────────────────────
async function saveProfile() {
    const name  = document.getElementById('prof-name')?.value.trim();
    const phone = document.getElementById('prof-phone')?.value.trim();
    if (!name) { showToast('⚠️ Name is required.', 'error'); return; }
    const r = await api('update_profile', { name, phone }, 'POST');
    if (!r.success) { showToast('❌ ' + r.message, 'error'); return; }
    const pName = document.getElementById('p-name');
    if (pName) pName.textContent = name;
    showToast('✅ Profile updated successfully');
}

async function changePassword() {
    const current = document.getElementById('pw-current')?.value;
    const newPw   = document.getElementById('pw-new')?.value;
    const confirm = document.getElementById('pw-confirm')?.value;
    if (!current || !newPw || !confirm) { showToast('⚠️ All fields are required.', 'error'); return; }
    if (newPw !== confirm) { showToast('⚠️ New passwords do not match.', 'error'); return; }
    if (newPw.length < 6)  { showToast('⚠️ Password must be at least 6 characters.', 'error'); return; }
    const r = await api('change_password', {
        current_password: current,
        new_password:     newPw,
        confirm_password: confirm
    }, 'POST');
    if (!r.success) { showToast('❌ ' + r.message, 'error'); return; }
    ['pw-current', 'pw-new', 'pw-confirm'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    showToast('🔐 Password changed successfully');
}

// ─── Logout ───────────────────────────────────────────────
function confirmLogout() {
    if (confirm('Are you sure you want to sign out?')) {
        window.location.href = '../auth/logout.php';
    }
}

// ─── Lightbox ─────────────────────────────────────────────
function openLightbox(src) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox').classList.add('open');
}
function closeLightbox() {
    document.getElementById('lightbox').classList.remove('open');
}

// ─── Helpers ──────────────────────────────────────────────
function esc(str) {
    if (str == null) return '';
    return String(str)
        .replace(/&/g,  '&amp;')
        .replace(/</g,  '&lt;')
        .replace(/>/g,  '&gt;')
        .replace(/"/g,  '&quot;')
        .replace(/'/g,  '&#39;');
}

function statusBadge(s) {
    const map = {
        'Open':        'st-open',
        'In progress': 'st-progress',
        'Resolved':    'st-resolved',
        'Closed':      'st-resolved',
    };
    const cls = map[s] || 'st-open';
    return `<span class="st-badge ${cls}"><span class="dot"></span>${esc(s)}</span>`;
}

function initials(name) {
    return (name || '?').split(' ').slice(0, 2).map(w => (w[0] || '').toUpperCase()).join('') || '?';
}

function formatDate(dt) {
    if (!dt) return '—';
    // FIX: normaliser le format avant de parser (remplacer espace par T pour Safari/Firefox)
    const d = new Date(String(dt).replace(' ', 'T'));
    if (isNaN(d.getTime())) return String(dt);
    return d.toLocaleDateString('en-GB') + ' ' + d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
}

// ─── Event Listeners ──────────────────────────────────────
document.getElementById('report-modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeModal(); closeLightbox(); }
});

// ─── Init ─────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    loadStats();
    loadDashRecent('all');
    loadWorkload();
    loadCategoryChart();
});