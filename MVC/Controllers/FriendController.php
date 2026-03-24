<?php
class FriendController extends Controller {
 
    private $friend;
 
    public function __construct() {
        if (!isset($_SESSION['user'])) {
            header("Location: /Test/index.php?controller=AuthController&action=index");
            exit();
        }
        $this->friend = $this->Model("FriendModel");
    }
 
    private function me(): int {
        return (int)$_SESSION['user']['id'];
    }
    private function jsonHeader(): void {
        header('Content-Type: application/json; charset=utf-8');
    }
    private function json(array $d): string {
        return json_encode($d, JSON_UNESCAPED_UNICODE);
    }
 
    // ── Trang chính ──────────────────────────────────
    public function index() {
        $this->View("Home", ["page" => "Friend"]); // ← "Friends" không phải "Friend"
    }
 
    // ── API: tìm kiếm ────────────────────────────────
    public function search() {
        $this->jsonHeader();
        $me  = $this->me();
        $kw  = trim($_GET['q'] ?? '');
        if (strlen($kw) < 1) {
            echo $this->json(['ok'=>true,'users'=>[]]); return;
        }
        $users = $this->friend->searchUsers($me, $kw);
        foreach ($users as &$u) {
            $u['friendship'] = $this->friend->getRelation($me, (int)$u['id']);
        }
        echo $this->json(['ok'=>true,'users'=>$users,'me'=>$me]);
    }
 
    // ── API: gợi ý kết bạn ───────────────────────────
    public function suggestions() {
        $this->jsonHeader();
        $me    = $this->me();
        $users = $this->friend->getSuggestions($me);
        foreach ($users as &$u) $u['friendship'] = null;
        echo $this->json(['ok'=>true,'users'=>$users,'me'=>$me]);
    }
 
    // ── API: danh sách bạn bè ────────────────────────
    public function friends() {
        $this->jsonHeader();
        echo $this->json(['ok'=>true,'friends'=>$this->friend->getFriends($this->me())]);
    }
 
    // ── API: lời mời nhận được ───────────────────────
    public function requests() {
        $this->jsonHeader();
        $pending = $this->friend->getPendingRequests($this->me());
        echo $this->json(['ok'=>true,'requests'=>$pending]);
    }
 
    // ── API: gửi lời mời (Friends.php gọi action=send) ──
    public function send() {
        $this->jsonHeader();
        $me    = $this->me();
        $other = (int)($_POST['to'] ?? 0);
        if ($other <= 0 || $other === $me) {
            echo $this->json(['ok'=>false,'error'=>'Invalid']); return;
        }
        $ok = $this->friend->sendRequest($me, $other);
        echo $this->json(['ok'=>$ok]);
    }
 
    // ── API: chấp nhận (Friends.php gọi action=accept) ──
    public function accept() {
        $this->jsonHeader();
        $me     = $this->me();
        $sender = (int)($_POST['from'] ?? 0);
        $ok = $this->friend->acceptRequest($me, $sender);
        echo $this->json(['ok'=>$ok]);
    }
 
    // ── API: từ chối (Friends.php gọi action=reject) ────
    public function reject() {
        $this->jsonHeader();
        $me     = $this->me();
        $sender = (int)($_POST['from'] ?? 0);
        $ok = $this->friend->declineRequest($me, $sender);
        echo $this->json(['ok'=>$ok]);
    }
 
    // ── API: hủy kết bạn (Friends.php gọi action=unfriend) ──
    public function unfriend() {
        $this->jsonHeader();
        $me    = $this->me();
        $other = (int)($_POST['with'] ?? 0);
        $ok = $this->friend->removeFriend($me, $other);
        echo $this->json(['ok'=>$ok]);
    }
 
    // ── API: đếm lời mời chưa đọc (Home.php polling) ────
    public function countPending() {
        $this->jsonHeader();
        $count = $this->friend->countPending($this->me());
        echo $this->json(['ok'=>true,'count'=>$count]);
    }
}