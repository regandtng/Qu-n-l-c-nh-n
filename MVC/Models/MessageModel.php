<?php
class MessageModel extends ConnectDB {
 
    public function __construct() {
        parent::__construct();
    }
 
    /** Gửi tin nhắn */
    public function sendMessage(int $senderId, int $receiverId, string $content): bool {
        $stmt = $this->conn->prepare(
            "INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("iis", $senderId, $receiverId, $content);
        return $stmt->execute();
    }
 
    /** Lấy tin nhắn giữa 2 người (50 tin gần nhất) */
    public function getMessages(int $userId, int $otherId, int $limit = 50): array {
        $stmt = $this->conn->prepare("
            SELECT m.id, m.sender_id, m.receiver_id, m.content, m.created_at,
                   a.fullname AS sender_name
            FROM messages m
            JOIN accounts a ON a.id = m.sender_id
            WHERE (m.sender_id = ? AND m.receiver_id = ?)
               OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.created_at DESC
            LIMIT ?
        ");
        $stmt->bind_param("iiiii", $userId, $otherId, $otherId, $userId, $limit);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        return array_reverse($rows); // cũ → mới
    }
 
    /** Lấy tin nhắn mới hơn id đã biết (dùng cho polling) */
    public function getNewMessages(int $userId, int $otherId, int $lastId): array {
        $stmt = $this->conn->prepare("
            SELECT m.id, m.sender_id, m.receiver_id, m.content, m.created_at,
                   a.fullname AS sender_name
            FROM messages m
            JOIN accounts a ON a.id = m.sender_id
            WHERE ((m.sender_id = ? AND m.receiver_id = ?)
               OR  (m.sender_id = ? AND m.receiver_id = ?))
              AND m.id > ?
            ORDER BY m.created_at ASC
        ");
        $stmt->bind_param("iiiii", $userId, $otherId, $otherId, $userId, $lastId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
 
    /** Danh sách người dùng để chat (chỉ bạn bè) */
    public function getUserList(int $currentUserId): array {
        $stmt = $this->conn->prepare("
            SELECT a.id, a.fullname, a.username
            FROM accounts a
            JOIN friendships f
              ON (f.sender_id = ? AND f.receiver_id = a.id)
              OR (f.receiver_id = ? AND f.sender_id = a.id)
            WHERE f.status = 'accepted'
            ORDER BY a.fullname ASC
        ");
        $stmt->bind_param("ii", $currentUserId, $currentUserId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
 
    /** Đánh dấu đã đọc */
    public function markRead(int $senderId, int $receiverId): void {
        $stmt = $this->conn->prepare(
            "UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0"
        );
        $stmt->bind_param("ii", $senderId, $receiverId);
        $stmt->execute();
    }
 
    /** Đếm tin chưa đọc từ một người */
    public function unreadCount(int $fromId, int $toId): int {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) as cnt FROM messages WHERE sender_id = ? AND receiver_id = ? AND is_read = 0"
        );
        $stmt->bind_param("ii", $fromId, $toId);
        $stmt->execute();
        return (int)$stmt->get_result()->fetch_assoc()['cnt'];
    }
}