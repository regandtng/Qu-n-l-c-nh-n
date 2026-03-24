<?php
// Messenger.php — View partial, nhúng vào layout Home
$me = $_SESSION['user'];
?>
<link rel="stylesheet" href="/Test/Public/Css/Messenger.css">
 
<div class="msn-wrap">
 
  <!-- Danh sách người dùng -->
  <div class="msn-sidebar">
    <div class="msn-sidebar-header">
      <span class="msn-title">Tin nhắn</span>
      <input class="msn-search" id="msn-search" type="text" placeholder="Tìm kiếm...">
    </div>
    <ul class="msn-user-list" id="msn-user-list">
      <li class="msn-loading">Đang tải...</li>
    </ul>
  </div>
 
  <!-- Khu vực chat -->
  <div class="msn-chat" id="msn-chat">
 
    <!-- Trạng thái chưa chọn ai -->
    <div class="msn-empty" id="msn-empty">
      <div class="msn-empty-icon">💬</div>
      <div class="msn-empty-text">Chọn một người để bắt đầu chat</div>
    </div>
 
    <!-- Header người đang chat -->
    <div class="msn-chat-header" id="msn-chat-header" style="display:none">
      <div class="msn-avatar" id="msn-avatar"></div>
      <div class="msn-chat-name" id="msn-chat-name"></div>
      <div class="msn-online-dot"></div>
    </div>
 
    <!-- Tin nhắn -->
    <div class="msn-messages" id="msn-messages"></div>
 
    <!-- Input gửi -->
    <div class="msn-input-bar" id="msn-input-bar" style="display:none">
      <textarea class="msn-input" id="msn-input" placeholder="Nhập tin nhắn..." rows="1"></textarea>
      <button class="msn-send-btn" id="msn-send-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <line x1="22" y1="2" x2="11" y2="13"></line>
          <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
        </svg>
      </button>
    </div>
 
  </div>
</div>
 
<script>
'use strict';
 
const API      = '/Test/index.php?controller=MessengerController&action=';
const ME_ID    = <?= (int)$me['id'] ?>;
const ME_NAME  = <?= json_encode($me['fullname'] ?? $me['username']) ?>;
 
let currentUser = null;   // { id, fullname, username }
let lastMsgId   = 0;
let pollTimer   = null;
 
// ── Fetch helper ──────────────────────────────
async function apiFetch(action, params = {}) {
  const url = API + action + '&' + new URLSearchParams(params);
  const res = await fetch(url);
  return res.json();
}
async function apiPost(action, body = {}) {
  const res = await fetch(API + action, {
    method: 'POST',
    body: new URLSearchParams(body),
  });
  return res.json();
}
 
// ── Avatar chữ cái đầu ───────────────────────
function initials(name) {
  return name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
}
 
// ── Render danh sách user ────────────────────
async function loadUserList() {
  const data = await apiFetch('userList');
  if (!data.ok) return;
 
  const list = document.getElementById('msn-user-list');
  list.innerHTML = '';
 
  if (data.users.length === 0) {
    list.innerHTML = '<li class="msn-loading">Chưa có người dùng nào</li>';
    return;
  }
 
  data.users.forEach(u => {
    const li = document.createElement('li');
    li.className = 'msn-user-item';
    li.dataset.id = u.id;
    li.innerHTML = `
      <div class="msn-user-avatar">${initials(u.fullname)}</div>
      <div class="msn-user-info">
        <div class="msn-user-name">${u.fullname}</div>
        <div class="msn-user-sub">@${u.username}</div>
      </div>
      ${u.unread > 0 ? `<div class="msn-badge">${u.unread}</div>` : ''}
    `;
    li.addEventListener('click', () => openChat(u));
    list.appendChild(li);
  });
}
 
// ── Mở chat với user ─────────────────────────
async function openChat(user) {
  currentUser = user;
  lastMsgId   = 0;
 
  // Active state
  document.querySelectorAll('.msn-user-item').forEach(el => {
    el.classList.toggle('active', el.dataset.id == user.id);
  });
 
  // Xóa badge
  const badge = document.querySelector(`.msn-user-item[data-id="${user.id}"] .msn-badge`);
  if (badge) badge.remove();
 
  // Hiện header & input
  document.getElementById('msn-empty').style.display  = 'none';
  document.getElementById('msn-chat-header').style.display = 'flex';
  document.getElementById('msn-input-bar').style.display   = 'flex';
  document.getElementById('msn-avatar').textContent = initials(user.fullname);
  document.getElementById('msn-chat-name').textContent = user.fullname;
 
  // Load lịch sử
  document.getElementById('msn-messages').innerHTML = '<div class="msn-loading-msg">Đang tải...</div>';
  const data = await apiFetch('getMessages', { with: user.id });
  if (!data.ok) return;
 
  renderMessages(data.messages, true);
 
  // Bắt đầu polling
  clearInterval(pollTimer);
  pollTimer = setInterval(doPoll, 2000);
}
 
// ── Polling ───────────────────────────────────
async function doPoll() {
  if (!currentUser) return;
  const data = await apiFetch('poll', { with: currentUser.id, lastId: lastMsgId });
  if (!data.ok || data.messages.length === 0) return;
  renderMessages(data.messages, false);
}
 
// ── Render tin nhắn ───────────────────────────
function renderMessages(messages, replace) {
  const box = document.getElementById('msn-messages');
  if (replace) box.innerHTML = '';
 
  messages.forEach(m => {
    const isMine = parseInt(m.sender_id) === ME_ID;
    const time   = new Date(m.created_at).toLocaleTimeString('vi-VN', { hour:'2-digit', minute:'2-digit' });
 
    const div = document.createElement('div');
    div.className = 'msn-msg ' + (isMine ? 'msn-msg-mine' : 'msn-msg-other');
    div.innerHTML = `
      <div class="msn-bubble">${m.content}</div>
      <div class="msn-time">${time}</div>
    `;
    box.appendChild(div);
 
    if (parseInt(m.id) > lastMsgId) lastMsgId = parseInt(m.id);
  });
 
  // Scroll xuống cuối
  box.scrollTop = box.scrollHeight;
}
 
// ── Gửi tin nhắn ─────────────────────────────
async function sendMessage() {
  const input   = document.getElementById('msn-input');
  const content = input.value.trim();
  if (!content || !currentUser) return;
 
  input.value = '';
  input.style.height = 'auto';
 
  const data = await apiPost('send', { to: currentUser.id, content });
  if (data.ok) {
    // Thêm luôn không cần đợi poll
    renderMessages([{
      id:          data.id,
      sender_id:   ME_ID,
      receiver_id: currentUser.id,
      content:     content,
      created_at:  new Date().toISOString().replace('T',' ').substring(0,19),
    }], false);
    lastMsgId = data.id;
  }
}
 
// ── Events ───────────────────────────────────
document.getElementById('msn-send-btn').addEventListener('click', sendMessage);
 
document.getElementById('msn-input').addEventListener('keydown', e => {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    sendMessage();
  }
});
 
// Auto resize textarea
document.getElementById('msn-input').addEventListener('input', function() {
  this.style.height = 'auto';
  this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});
 
// Tìm kiếm user
document.getElementById('msn-search').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.msn-user-item').forEach(el => {
    const name = el.querySelector('.msn-user-name').textContent.toLowerCase();
    el.style.display = name.includes(q) ? '' : 'none';
  });
});
 
// Boot
loadUserList();
</script>