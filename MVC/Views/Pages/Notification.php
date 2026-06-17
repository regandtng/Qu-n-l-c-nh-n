<link rel="stylesheet" href="/Test/Public/Css/Notification.css">
<div class="page-notification">
    <div class="noti-header">
        <div class="noti-title">
            <h1>Thông báo</h1>
            <p id="notificationSummary" class="notification-summary">Đang kiểm tra thông báo...</p>
        </div>
        <div class="noti-actions">
            <button class="tab-btn active" id="showAll">Tất cả</button>
            <button class="tab-btn" id="showUnread">Chưa đọc</button>
        </div>
    </div>
    <div class="noti-main" id="notiMain"></div>
</div>

<div class="notification-modal" id="notificationModal">
    <div class="notification-modal-backdrop" onclick="closeNotificationModal()"></div>
    <div class="notification-modal-content">
        <button class="notification-modal-close" type="button" onclick="closeNotificationModal()">×</button>
        <div class="notification-modal-header">
            <div class="notification-type" id="modalNotificationType"></div>
            <h3 id="modalNotificationTitle">Thông báo</h3>
            <div class="notification-meta" id="modalNotificationMeta"></div>
        </div>
        <div class="notification-modal-body" id="modalNotificationBody"></div>
    </div>
</div>

<script>
    const NOTE_STORAGE_KEY = 'scheduleNotes';
    const notiMain = document.getElementById('notiMain');
    const showAllBtn = document.getElementById('showAll');
    const showUnreadBtn = document.getElementById('showUnread');
    const notificationSummary = document.getElementById('notificationSummary');
    let filterUnread = false;
 
    function loadNotes() {
        const raw = localStorage.getItem(NOTE_STORAGE_KEY);
        return raw ? JSON.parse(raw) : [];
    }
 
    function saveNotes(notes) {
        localStorage.setItem(NOTE_STORAGE_KEY, JSON.stringify(notes));
    }
 
    function formatDisplayDate(iso) {
        const [year, month, day] = iso.split('-');
        return `${day}/${month}/${year}`;
    }
 
    function escapeHTML(text) {
        return String(text || '').replace(/[&<>"']/g, ch => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        })[ch]);
    }
 
    function getNotificationType(note) {
        return note.type || (note.date ? 'Thông báo ghi chú lịch' : 'Thông báo chung');
    }
 
    function markNoteRead(id) {
        const notes = loadNotes();
        const idx = notes.findIndex(item => item.id === id);
        if (idx !== -1) {
            notes[idx].read = true;
            saveNotes(notes);
            renderNotifications();
        }
    }
 
    function categorizeNotifications(notes) {
        const now = Date.now();
        const recent = [];
        const older = [];
        const threshold = 7 * 24 * 60 * 60 * 1000;
        notes.forEach(note => {
            const createdAt = note.createdAt || 0;
            if (now - createdAt <= threshold) {
                recent.push(note);
            } else {
                older.push(note);
            }
        });
        return { recent, older };
    }
 
    function renderNotificationItem(note) {
        const noteTime = note.notifyTime || note.time || '';
        const noteTitle = note.title || (note.description ? note.description.slice(0, 50) + (note.description.length > 50 ? '...' : '') : 'Thông báo');
        const noteType = getNotificationType(note);
        const shortText = note.description ? (note.description.length > 85 ? note.description.slice(0, 85) + '...' : note.description) : '';
        const noteDateText = note.date ? formatDisplayDate(note.date) : '';
        const metaText = [noteDateText, noteTime].filter(Boolean).join(' • ');
        return `
            <div class="notification-item ${note.read ? 'read' : 'unread'}" onclick="openNotificationModal('${note.id}')">
                <div class="notification-avatar"><i class="fa-solid fa-bell"></i></div>
                <div class="notification-item-body">
                    <div class="notification-item-title">${escapeHTML(noteTitle)}</div>
                    <div class="notification-item-text">${escapeHTML(shortText)}</div>
                    <div class="notification-item-meta">
                        <span>${escapeHTML(noteType)}</span>
                        <span>${escapeHTML(metaText)}</span>
                    </div>
                </div>
                <div class="notification-item-status">
                    <span class="status ${note.read ? 'read' : 'unread'}">${note.read ? 'Đã đọc' : 'Mới'}</span>
                </div>
            </div>`;
    }
 
    function openNotificationModal(id) {
        const notes = loadNotes();
        const note = notes.find(item => item.id === id);
        if (!note) return;
        if (!note.read) {
            note.read = true;
            saveNotes(notes);
        }
        const noteTime = note.notifyTime || note.time || '';
        const metaText = [note.date ? formatDisplayDate(note.date) : '', noteTime].filter(Boolean).join(' • ');
        document.getElementById('modalNotificationTitle').textContent = note.title || 'Thông báo';
        document.getElementById('modalNotificationType').textContent = getNotificationType(note);
        document.getElementById('modalNotificationMeta').textContent = metaText;
        document.getElementById('modalNotificationBody').textContent = note.description || 'Không có nội dung.';
        document.getElementById('notificationModal').classList.add('open');
        renderNotifications();
    }
 
    function closeNotificationModal() {
        document.getElementById('notificationModal').classList.remove('open');
    }
 
    function parseDateTime(date, time) {
        const [year, month, day] = date.split('-').map(Number);
        const [hours, minutes] = (time || '00:00').split(':').map(Number);
        return new Date(year, month - 1, day, hours, minutes, 0, 0);
    }
 
    function getDueNotifications() {
        const now = new Date();
        return loadNotes().filter(note => {
            if (!note.date) return false;
            const dueTime = parseDateTime(note.date, note.notifyTime || note.time || '00:00');
            return dueTime <= now;
        });
    }
 
    function updateNotificationSummary(notes) {
        const unreadCount = notes.filter(note => !note.read).length;
        if (notes.length === 0) {
            notificationSummary.textContent = 'Hiện không có thông báo.';
        } else if (unreadCount === 0) {
            notificationSummary.textContent = `Tất cả đã được đọc (${notes.length})`;
        } else {
            notificationSummary.textContent = `Có ${unreadCount} thông báo chưa đọc`;
        }
    }
 
    function setActiveTab() {
        showAllBtn.classList.toggle('active', !filterUnread);
        showUnreadBtn.classList.toggle('active', filterUnread);
    }
 
    function renderNotifications() {
        const notes = getDueNotifications().sort((a, b) => a.date.localeCompare(b.date) || a.createdAt - b.createdAt);
        updateNotificationSummary(notes);
        setActiveTab();
        const visible = filterUnread ? notes.filter(note => !note.read) : notes;
        if (visible.length === 0) {
            notiMain.innerHTML = '<div class="noti-empty">Không có thông báo nào.</div>';
            return;
        }
        const groups = categorizeNotifications(visible);
        let html = '';
        if (groups.recent.length) {
            html += '<div class="noti-group"><div class="noti-group-title">Mới</div>' + groups.recent.map(renderNotificationItem).join('') + '</div>';
        }
        if (groups.older.length) {
            html += '<div class="noti-group"><div class="noti-group-title">Trước đó</div>' + groups.older.map(renderNotificationItem).join('') + '</div>';
        }
        notiMain.innerHTML = html;
    }
 
    showAllBtn.addEventListener('click', () => {
        filterUnread = false;
        renderNotifications();
    });
    showUnreadBtn.addEventListener('click', () => {
        filterUnread = true;
        renderNotifications();
    });
 
    document.addEventListener('DOMContentLoaded', renderNotifications);
</script>
