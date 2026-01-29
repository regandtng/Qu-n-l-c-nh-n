<link rel="stylesheet" href="/TEST/Public/Css/personal.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<div class="page-personnal">
    <!-- Header -->
    <div class="header">
        <div class="avartar">
            <img src="/Test/Public/Picture/avartar.png" alt="Avatar">
        </div>
        <div class="link-name">
            <p><?= $_SESSION['user']['fullname'] ?? '' ?></p>
            <a onclick="alert('Chức năng đang phát triển!')">Thay đổi ảnh</a>
        </div>
    </div>

    <!-- Thông tin cá nhân -->
    <div class="personnal-info" onclick="openModal()">
        <h2>Thông tin cá nhân <i class="fa-solid fa-pen-to-square"></i></h2>
        <p><b>Họ và tên:</b> <?= $_SESSION['user']['fullname'] ?? 'Chưa cập nhật' ?></p>
        <p><b>Email:</b> <?= $_SESSION['user']['email'] ?? 'Chưa cập nhật' ?></p>
        <p><b>Username:</b> <?= $_SESSION['user']['username'] ?? 'Chưa cập nhật' ?></p>
        <p><b>Password:</b> *******</p>
        <p><b>Số điện thoại:</b> <?= $_SESSION['user']['phone'] ?? 'Chưa cập nhật' ?></p>
        <p><b>Địa chỉ:</b> <?= $_SESSION['user']['address'] ?? 'Chưa cập nhật' ?></p>
    </div>
</div>

<!-- Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <!-- Header -->
        <div class="modal-header">
            <h2>Chỉnh sửa thông tin</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>

        <!-- Body -->
        <div class="modal-body">
            <div id="message"></div>
            
            <form id="editForm">
                <div class="form-group">
                    <label>Họ và tên:</label>
                    <input type="text" name="fullname" value="<?= $_SESSION['user']['fullname'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" value="<?= $_SESSION['user']['email'] ?? '' ?>" required>
                </div>
                
               <div class="form-group">
                    <label>Mật khẩu mới (để trống nếu không đổi):</label>
                    <div style="position: relative;">
                        <input type="password" name="password" id="password" placeholder="Nhập mật khẩu mới">
                        <i class="fa-solid fa-eye toggle-pass" onclick="togglePassword()"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Số điện thoại:</label>
                    <input type="text" name="phone" value="<?= $_SESSION['user']['phone'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label>Địa chỉ:</label>
                    <input type="text" name="address" value="<?= $_SESSION['user']['address'] ?? '' ?>">
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="confirmDelete()">Xóa tài khoản</button>
            <button class="btn btn-secondary" onclick="closeModal()">Hủy</button>
            <button class="btn btn-primary" onclick="saveChanges()">Lưu</button>
        </div>
    </div>
</div>
<!----------------------------------------------Phần AI làm ----------------------------------------------------------------------->
<script>
function openModal() {
    document.getElementById('editModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

window.onclick = function(event) {
    if (event.target == document.getElementById('editModal')) {
        closeModal();
    }
}

function togglePassword() {
    const input = document.getElementById('password');
    const icon = event.target;
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    }
}

function saveChanges() {
    const form = document.getElementById('editForm');
    const formData = new FormData(form);
    
    fetch('/Test/index.php?controller=HomeController&action=updateProfile', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        const msg = document.getElementById('message');
        msg.className = data.success ? 'success' : 'error';
        msg.textContent = data.message;
        msg.style.display = 'block';
        
        if (data.success) {
            setTimeout(() => location.reload(), 1500);
        }
    })
    .catch(err => {
        document.getElementById('message').textContent = 'Lỗi kết nối!';
        document.getElementById('message').className = 'error';
        document.getElementById('message').style.display = 'block';
    });
}

function confirmDelete() {
    if (confirm('⚠️ Bạn chắc chắn muốn xóa tài khoản?\n\nHành động này không thể hoàn tác!')) {
        fetch('/Test/index.php?controller=HomeController&action=deleteAccount', {
            method: 'POST'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Tài khoản đã được xóa!');
                window.location.href = '/Test/index.php?controller=AutController&action=index';
            } else {
                alert(data.message);
            }
        });
    }
}
</script>