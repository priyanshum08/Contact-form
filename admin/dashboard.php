<?php
session_start();

// ── Auth guard ────────────────────────────────────────────────────────────────
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.html');
    exit;
}

$admin = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="admin.css"/>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Syne:wght@700;800&display=swap" rel="stylesheet"/>
</head>
<body class="dashboard-body">
  <div class="bg-grid"></div>

  <!-- ── Sidebar ── -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="logo-dot"></div>
      <span>Control Panel</span>
    </div>
    <nav class="sidebar-nav">
      <a href="#" class="nav-item active">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
        </svg>
        Messages
      </a>
    </nav>
    <div class="sidebar-footer">
      <div class="admin-badge">
        <div class="admin-avatar"><?= strtoupper(substr($admin, 0, 1)) ?></div>
        <span><?= $admin ?></span>
      </div>
      <a href="../php/logout.php" class="logout-btn" title="Logout">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
        </svg>
      </a>
    </div>
  </aside>

  <!-- ── Main content ── -->
  <main class="dash-main">
    <header class="dash-header">
      <div>
        <h1>Inbox</h1>
        <p class="header-sub">All contact form submissions</p>
      </div>
      <div class="header-actions">
        <span class="msg-count" id="msgCount">Loading…</span>
        <button class="refresh-btn" id="refreshBtn" title="Refresh">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/>
          </svg>
        </button>
      </div>
    </header>

    <div id="alertBox" class="alert-success" hidden></div>

    <!-- Stats bar -->
    <div class="stats-row">
      <div class="stat-card">
        <span class="stat-label">Total Messages</span>
        <span class="stat-val" id="statTotal">—</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Today</span>
        <span class="stat-val" id="statToday">—</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">This Week</span>
        <span class="stat-val" id="statWeek">—</span>
      </div>
    </div>

    <!-- Search -->
    <div class="search-bar">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
      </svg>
      <input type="text" id="searchInput" placeholder="Search by name, email, or subject…"/>
    </div>

    <!-- Table -->
    <div class="table-wrap">
      <table id="messagesTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Subject</th>
            <th>Message</th>
            <th>Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <tr class="loading-row">
            <td colspan="8">
              <div class="loading-spinner">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                </svg>
                Loading messages…
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </main>

<script>
let allMessages = [];

// ── Load messages ─────────────────────────────────────────────────────────────
async function loadMessages() {
  const tbody = document.getElementById('tableBody');
  tbody.innerHTML = `<tr class="loading-row"><td colspan="8"><div class="loading-spinner"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>Loading…</div></td></tr>`;

  try {
    const res  = await fetch('../php/get_messages.php');
    const data = await res.json();

    if (data.status === 'success') {
      allMessages = data.data;
      updateStats(allMessages);
      renderTable(allMessages);
    } else {
      tbody.innerHTML = `<tr><td colspan="8" class="empty-row">Failed to load messages.</td></tr>`;
    }
  } catch (e) {
    tbody.innerHTML = `<tr><td colspan="8" class="empty-row">Network error. Please refresh.</td></tr>`;
  }
}

// ── Render table ──────────────────────────────────────────────────────────────
function renderTable(msgs) {
  const tbody = document.getElementById('tableBody');
  document.getElementById('msgCount').textContent = msgs.length + ' message' + (msgs.length !== 1 ? 's' : '');

  if (msgs.length === 0) {
    tbody.innerHTML = `<tr><td colspan="8" class="empty-row">
      <div class="empty-state">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
          <polyline points="22,6 12,13 2,6"/>
        </svg>
        <p>No messages yet.<br>Submit the contact form to see messages here.</p>
      </div>
    </td></tr>`;
    return;
  }

  tbody.innerHTML = msgs.map((m, i) => `
    <tr data-id="${m.id}">
      <td><span class="id-badge">${m.id}</span></td>
      <td><strong>${esc(m.name)}</strong></td>
      <td><a href="mailto:${esc(m.email)}" class="email-link">${esc(m.email)}</a></td>
      <td>${m.phone ? esc(m.phone) : '<span class="na">—</span>'}</td>
      <td>${m.subject ? esc(m.subject) : '<span class="na">No subject</span>'}</td>
      <td><span class="msg-preview" title="${esc(m.message)}">${esc(m.message)}</span></td>
      <td><span class="date-badge">${formatDate(m.created_at)}</span></td>
      <td>
        <button class="del-btn" onclick="deleteMessage(${m.id}, this)">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6M10 11v6M14 11v6M9 6V4h6v2"/>
          </svg>
          Delete
        </button>
      </td>
    </tr>
  `).join('');
}

// ── Delete message ────────────────────────────────────────────────────────────
async function deleteMessage(id, btn) {
  if (!confirm('Delete this message? This cannot be undone.')) return;

  btn.disabled = true;
  btn.textContent = '…';

  const formData = new FormData();
  formData.append('id', id);

  try {
    const res  = await fetch('../php/delete_message.php', { method: 'POST', body: formData });
    const data = await res.json();

    if (data.status === 'success') {
      const row = document.querySelector(`tr[data-id="${id}"]`);
      if (row) {
        row.style.opacity = '0';
        row.style.transform = 'translateX(20px)';
        row.style.transition = 'all 0.3s ease';
        setTimeout(() => row.remove(), 300);
      }
      allMessages = allMessages.filter(m => m.id !== id);
      updateStats(allMessages);
      document.getElementById('msgCount').textContent = allMessages.length + ' message' + (allMessages.length !== 1 ? 's' : '');
      showAlert('Message deleted successfully.');
    } else {
      alert('Failed to delete: ' + data.message);
      btn.disabled = false;
      btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6M10 11v6M14 11v6M9 6V4h6v2"/></svg> Delete`;
    }
  } catch (e) {
    alert('Network error.');
    btn.disabled = false;
  }
}

// ── Search ────────────────────────────────────────────────────────────────────
document.getElementById('searchInput').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  const filtered = allMessages.filter(m =>
    m.name.toLowerCase().includes(q)    ||
    m.email.toLowerCase().includes(q)   ||
    (m.subject || '').toLowerCase().includes(q)
  );
  renderTable(filtered);
});

// ── Stats ─────────────────────────────────────────────────────────────────────
function updateStats(msgs) {
  document.getElementById('statTotal').textContent = msgs.length;

  const now   = new Date();
  const today = now.toISOString().slice(0, 10);
  const weekAgo = new Date(now - 7 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10);

  document.getElementById('statToday').textContent =
    msgs.filter(m => m.created_at.slice(0, 10) === today).length;
  document.getElementById('statWeek').textContent =
    msgs.filter(m => m.created_at.slice(0, 10) >= weekAgo).length;
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function esc(str) {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}

function formatDate(dt) {
  return new Date(dt).toLocaleDateString('en-US', {
    month: 'short', day: 'numeric', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  });
}

function showAlert(msg) {
  const box = document.getElementById('alertBox');
  box.textContent = msg;
  box.hidden = false;
  setTimeout(() => { box.hidden = true; }, 3000);
}

document.getElementById('refreshBtn').addEventListener('click', loadMessages);

// Initial load
loadMessages();
</script>
</body>
</html>
