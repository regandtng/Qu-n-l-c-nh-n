<?php
class FriendModel extends ConnectDB {
 
    public function __construct() { parent::__construct(); }
 
    // ── Trạng thái quan hệ giữa 2 người ──────────────
    // Trả về: null | ['status'=>'pending','sender_id'=>X]
    public function getRelation(int $me, int $other): ?array {
        $stmt = $this->conn->prepare("
            SELECT status, sender_id FROM friendships
            WHERE (sender_id=? AND receiver_id=?)
               OR (sender_id=? AND receiver_id=?)
            LIMIT 1
        ");
        $stmt->bind_param("iiii", $me, $other, $other, $me);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }
 
    // ── Gửi lời mời ──────────────────────────────────
    public function sendRequest(int $me, int $other): bool {
        $stmt = $this->conn->prepare(
            "INSERT IGNORE INTO friendships (sender_id, receiver_id, status) VALUES (?,?,'pending')"
        );
        $stmt->bind_param("ii", $me, $other);
        return $stmt->execute();
    }
 
    // ── Chấp nhận lời mời ────────────────────────────
    public function acceptRequest(int $me, int $sender): bool {
        $stmt = $this->conn->prepare("
            UPDATE friendships SET status='accepted'
            WHERE sender_id=? AND receiver_id=? AND status='pending'
        ");
        $stmt->bind_param("ii", $sender, $me);
        return $stmt->execute();
    }
 
    // ── Từ chối lời mời ──────────────────────────────
    public function declineRequest(int $me, int $sender): bool {
        $stmt = $this->conn->prepare("
            UPDATE friendships SET status='declined'
            WHERE sender_id=? AND receiver_id=? AND status='pending'
        ");
        $stmt->bind_param("ii", $sender, $me);
        return $stmt->execute();
    }
 
    // ── Hủy kết bạn / rút lời mời ───────────────────
    public function removeFriend(int $me, int $other): bool {
        $stmt = $this->conn->prepare("
            DELETE FROM friendships
            WHERE (sender_id=? AND receiver_id=?)
               OR (sender_id=? AND receiver_id=?)
        ");
        $stmt->bind_param("iiii", $me, $other, $other, $me);
        return $stmt->execute();
    }
 
    // ── Danh sách bạn bè ─────────────────────────────
    public function getFriends(int $me): array {
        $stmt = $this->conn->prepare("
            SELECT a.id, a.fullname, a.username, a.email
            FROM friendships f
            JOIN accounts a ON a.id = IF(f.sender_id=?, f.receiver_id, f.sender_id)
            WHERE (f.sender_id=? OR f.receiver_id=?) AND f.status='accepted'
            ORDER BY a.fullname ASC
        ");
        $stmt->bind_param("iii", $me, $me, $me);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
 
    // ── Lời mời đang chờ (người khác gửi cho mình) ───
    public function getPendingRequests(int $me): array {
        $stmt = $this->conn->prepare("
            SELECT a.id, a.fullname, a.username, f.created_at
            FROM friendships f
            JOIN accounts a ON a.id = f.sender_id
            WHERE f.receiver_id=? AND f.status='pending'
            ORDER BY f.created_at DESC
        ");
        $stmt->bind_param("i", $me);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
 
    // ── Lời mời mình đã gửi (đang chờ) ──────────────
    public function getSentRequests(int $me): array {
        $stmt = $this->conn->prepare("
            SELECT a.id, a.fullname, a.username, f.created_at
            FROM friendships f
            JOIN accounts a ON a.id = f.receiver_id
            WHERE f.sender_id=? AND f.status='pending'
            ORDER BY f.created_at DESC
        ");
        $stmt->bind_param("i", $me);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
 
    // ── Tìm kiếm người dùng theo tên ─────────────────
    public function searchUsers(int $me, string $keyword): array {
        $kw = '%' . $keyword . '%';
        $stmt = $this->conn->prepare("
            SELECT id, fullname, username
            FROM accounts
            WHERE id != ? AND (fullname LIKE ? OR username LIKE ?)
              AND (role='user' OR role='')
            ORDER BY fullname ASC
            LIMIT 20
        ");
        $stmt->bind_param("iss", $me, $kw, $kw);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
 
    // ── Gợi ý kết bạn (chưa có quan hệ) ─────────────
    public function getSuggestions(int $me, int $limit = 6): array {
        $stmt = $this->conn->prepare("
            SELECT a.id, a.fullname, a.username
            FROM accounts a
            WHERE a.id != ? AND (a.role='user' OR a.role='')
              AND a.id NOT IN (
                SELECT IF(sender_id=?, receiver_id, sender_id)
                FROM friendships
                WHERE sender_id=? OR receiver_id=?
              )
            ORDER BY RAND()
            LIMIT ?
        ");
        $stmt->bind_param("iiiii", $me, $me, $me, $me, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
 
    // ── Đếm lời mời chờ ──────────────────────────────
    public function countPending(int $me): int {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) as cnt FROM friendships WHERE receiver_id=? AND status='pending'"
        );
        $stmt->bind_param("i", $me);
        $stmt->execute();
        return (int)$stmt->get_result()->fetch_assoc()['cnt'];
    }
}