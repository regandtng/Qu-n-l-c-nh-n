<?php
$me = $_SESSION['user'];
?>
<link rel="stylesheet" href="/Test/Public/Css/Friend.css">
 
<div class="fr-wrap" style="width:100%;min-height:500px;display:flex;flex-direction:column;">
 
  <!-- Tabs -->
  <div class="fr-tabs">
    <button class="fr-tab active" onclick="showTab('search')">
      <i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm
    </button>
    <button class="fr-tab" onclick="showTab('requests')">
      <i class="fa-solid fa-user-clock"></i> Lời mời
      <span class="fr-badge" id="req-badge" style="display:none"></span>
    </button>
    <button class="fr-tab" onclick="showTab('friends')">
      <i class="fa-solid fa-user-group"></i> Bạn bè
    </button>
  </div>
 
  <!-- Tab: Tìm kiếm -->
  <div class="fr-panel" id="tab-search">
    <div class="fr-search-bar">
      <i class="fa-solid fa-magnifying-glass fr-search-icon"></i>
      <input type="text" id="fr-search-input" class="fr-search-input"
             placeholder="Tìm kiếm theo tên hoặc username...">
    </div>
 
    <div class="fr-section-title" id="suggest-title">Gợi ý kết bạn</div>
    <div class="fr-user-grid" id="fr-search-results"></div>
  </div>
 
  <!-- Tab: Lời mời -->
  <div class="fr-panel" id="tab-requests" style="display:none">
    <div class="fr-section-title">Lời mời kết bạn</div>
    <div class="fr-user-grid" id="fr-requests"></div>
  </div>
 
  <!-- Tab: Bạn bè -->
  <div class="fr-panel" id="tab-friends" style="display:none">
    <div class="fr-search-bar">
      <i class="fa-solid fa-magnifying-glass fr-search-icon"></i>
      <input type="text" id="fr-friend-search" class="fr-search-input"
             placeholder="Tìm trong danh sách bạn bè...">
    </div>
    <div class="fr-section-title">Danh sách bạn bè</div>
    <div class="fr-user-grid" id="fr-friends"></div>
  </div>
 
</div>
 
<script>
'use strict';
const FR_API = '/Test/index.php?controller=FriendController&action=';
const ME_ID  = <?= (int)$me['id'] ?>;
 
// ── Fetch helpers ────────────────────────────────────────
async function frGet(action, params={}) {
  const res = await fetch(FR_API + action + '&' + new URLSearchParams(params));
  return res.json();
}
async function frPost(action, body={}) {
  const res = await fetch(FR_API + action, {
    method: 'POST', body: new URLSearchParams(body)
  });
  return res.json();
}
 
// ── Avatar chữ cái ───────────────────────────────────────
function initials(name) {
  return name.split(' ').slice(-2).map(w=>w[0]).join('').toUpperCase();
}
 
// ── Render nút theo trạng thái bạn bè ───────────────────
function renderBtn(u) {
  const f = u.friendship;
  if (!f) return `<button class="fr-btn fr-btn-add" onclick="sendReq(${u.id},this)">
    <i class="fa-solid fa-user-plus"></i> Kết bạn</button>`;
 
  if (f.status === 'pending' && parseInt(f.sender_id) === ME_ID)
    return `<button class="fr-btn fr-btn-cancel" onclick="cancelReq(${u.id},this)">
      <i class="fa-solid fa-clock"></i> Đã gửi</button>`;
 
  if (f.status === 'pending' && parseInt(f.sender_id) !== ME_ID)
    return `<button class="fr-btn fr-btn-accept" onclick="acceptReq(${u.id},this)">
      <i class="fa-solid fa-check"></i> Chấp nhận</button>`;
 
  if (f.status === 'accepted')
    return `<button class="fr-btn fr-btn-friend" onclick="unfriendUser(${u.id},this)">
      <i class="fa-solid fa-user-check"></i> Bạn bè</button>`;
 
  return `<button class="fr-btn fr-btn-add" onclick="sendReq(${u.id},this)">
    <i class="fa-solid fa-user-plus"></i> Kết bạn</button>`;
}
 
// ── Render card người dùng ───────────────────────────────
function renderCard(u, extraBtn='') {
  return `
    <div class="fr-card" id="fr-card-${u.id}">
      <div class="fr-card-avatar">${initials(u.fullname)}</div>
      <div class="fr-card-name">${u.fullname}</div>
      <div class="fr-card-user">@${u.username}</div>
      <div class="fr-card-actions">
        ${extraBtn || renderBtn(u)}
      </div>
    </div>`;
}
 
// ── TABS ─────────────────────────────────────────────────
function showTab(tab) {
  ['search','requests','friends'].forEach(t => {
    document.getElementById('tab-'+t).style.display = t===tab ? '' : 'none';
    document.querySelectorAll('.fr-tab').forEach((el,i) => {
      el.classList.toggle('active', ['search','requests','friends'][i] === tab);
    });
  });
  if (tab === 'requests') loadRequests();
  if (tab === 'friends')  loadFriends();
}
 
// ── Tìm kiếm ────────────────────────────────────────────
let searchTimer = null;
document.getElementById('fr-search-input').addEventListener('input', function() {
  clearTimeout(searchTimer);
  const q = this.value.trim();
  if (q.length === 0) { loadSuggestions(); return; }
  searchTimer = setTimeout(() => doSearch(q), 400);
});
 
async function doSearch(q) {
  document.getElementById('suggest-title').textContent = 'Kết quả tìm kiếm';
  const data = await frGet('search', { q });
  const box  = document.getElementById('fr-search-results');
  if (!data.ok || data.users.length === 0) {
    box.innerHTML = '<div class="fr-empty">Không tìm thấy người dùng nào</div>'; return;
  }
  box.innerHTML = data.users.map(u => renderCard(u)).join('');
}
 
async function loadSuggestions() {
  document.getElementById('suggest-title').textContent = 'Gợi ý kết bạn';
  const data = await frGet('suggestions');
  const box  = document.getElementById('fr-search-results');
  if (!data.ok || data.users.length === 0) {
    box.innerHTML = '<div class="fr-empty">Không có gợi ý nào</div>'; return;
  }
  // Gắn friendship = null vì đây là suggestions (chưa có quan hệ)
  data.users.forEach(u => u.friendship = null);
  box.innerHTML = data.users.map(u => renderCard(u)).join('');
}
 
// ── Lời mời ─────────────────────────────────────────────
async function loadRequests() {
  const data = await frGet('requests');
  const box  = document.getElementById('fr-requests');
  if (!data.ok || data.requests.length === 0) {
    box.innerHTML = '<div class="fr-empty">Không có lời mời nào</div>'; return;
  }
  box.innerHTML = data.requests.map(r => renderCard(
    { id: r.id, fullname: r.fullname, username: r.username },
    `<button class="fr-btn fr-btn-accept" onclick="acceptReq(${r.id},this)">
       <i class="fa-solid fa-check"></i> Chấp nhận</button>
     <button class="fr-btn fr-btn-cancel" onclick="rejectReq(${r.id},this)">
       <i class="fa-solid fa-xmark"></i> Từ chối</button>`
  )).join('');
}
 
// ── Danh sách bạn bè ────────────────────────────────────
let allFriends = [];
async function loadFriends() {
  const data = await frGet('friends');
  const box  = document.getElementById('fr-friends');
  allFriends = data.friends || [];
  renderFriendList(allFriends);
}
function renderFriendList(list) {
  const box = document.getElementById('fr-friends');
  if (list.length === 0) { box.innerHTML = '<div class="fr-empty">Chưa có bạn bè nào</div>'; return; }
  box.innerHTML = list.map(u => renderCard(
    { ...u, friendship: { status:'accepted', sender_id: ME_ID } }
  )).join('');
}
 
// Tìm trong danh sách bạn bè
document.getElementById('fr-friend-search').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  renderFriendList(allFriends.filter(u =>
    u.fullname.toLowerCase().includes(q) || u.username.toLowerCase().includes(q)
  ));
});
 
// ── Actions ──────────────────────────────────────────────
async function sendReq(id, btn) {
  btn.disabled = true;
  const data = await frPost('send', { to: id });
  if (data.ok) {
    btn.outerHTML = `<button class="fr-btn fr-btn-cancel" onclick="cancelReq(${id},this)">
      <i class="fa-solid fa-clock"></i> Đã gửi</button>`;
  } else { btn.disabled = false; }
}
 
async function cancelReq(id, btn) {
  btn.disabled = true;
  const data = await frPost('unfriend', { with: id });
  if (data.ok) {
    btn.outerHTML = `<button class="fr-btn fr-btn-add" onclick="sendReq(${id},this)">
      <i class="fa-solid fa-user-plus"></i> Kết bạn</button>`;
    updateBadge();
  } else { btn.disabled = false; }
}
 
async function acceptReq(id, btn) {
  btn.disabled = true;
  const data = await frPost('accept', { from: id });
  if (data.ok) {
    const card = document.getElementById('fr-card-'+id);
    if (card) {
      card.querySelector('.fr-card-actions').innerHTML =
        `<button class="fr-btn fr-btn-friend" onclick="unfriendUser(${id},this)">
          <i class="fa-solid fa-user-check"></i> Bạn bè</button>`;
    }
    updateBadge();
  } else { btn.disabled = false; }
}
 
async function rejectReq(id, btn) {
  btn.disabled = true;
  const data = await frPost('reject', { from: id });
  if (data.ok) {
    const card = document.getElementById('fr-card-'+id);
    if (card) card.remove();
    updateBadge();
  } else { btn.disabled = false; }
}
 
async function unfriendUser(id, btn) {
  if (!confirm('Hủy kết bạn với người này?')) return;
  btn.disabled = true;
  const data = await frPost('unfriend', { with: id });
  if (data.ok) {
    btn.outerHTML = `<button class="fr-btn fr-btn-add" onclick="sendReq(${id},this)">
      <i class="fa-solid fa-user-plus"></i> Kết bạn</button>`;
  } else { btn.disabled = false; }
}
 
// ── Badge lời mời ────────────────────────────────────────
async function updateBadge() {
  const data = await frGet('requests');
  const cnt  = data.requests?.length || 0;
  const badge = document.getElementById('req-badge');
  if (cnt > 0) { badge.textContent = cnt; badge.style.display = ''; }
  else         { badge.style.display = 'none'; }
}
 
// Boot
loadSuggestions();
updateBadge();
 
// Polling: tự động cập nhật badge + lời mời mỗi 5 giây
setInterval(async () => {
  await updateBadge();
  // Nếu đang xem tab lời mời thì reload luôn
  if (document.getElementById('tab-requests').style.display !== 'none') {
    await loadRequests();
  }
}, 5000);
</script>