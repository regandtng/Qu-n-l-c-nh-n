<?php
class MessengerController extends Controller {
 
    private $msg;
 
    public function __construct() {
        if (!isset($_SESSION['user'])) {
            header("Location: /Test/index.php?controller=AutController&action=index");
            exit();
        }
        $this->msg = $this->Model("MessageModel");
    }
 
    /** Trang chat chính — nhúng vào layout Home */
    public function index() {
        $users = $this->msg->getUserList((int)$_SESSION['user']['id']);
        $this->View("Home", ["page" => "Messenger", "users" => $users]);
    }
 
    /** API: lấy lịch sử chat với người $otherId */
    public function getMessages() {
        $this->jsonHeader();
        $me      = (int)$_SESSION['user']['id'];
        $otherId = (int)($_GET['with'] ?? 0);
        if ($otherId <= 0) { echo $this->json(['ok'=>false]); return; }
 
        $this->msg->markRead($otherId, $me);
        $messages = $this->msg->getMessages($me, $otherId);
        echo $this->json(['ok' => true, 'messages' => $messages, 'me' => $me]);
    }
 
    /** API: polling — lấy tin mới hơn lastId */
    public function poll() {
        $this->jsonHeader();
        $me      = (int)$_SESSION['user']['id'];
        $otherId = (int)($_GET['with']   ?? 0);
        $lastId  = (int)($_GET['lastId'] ?? 0);
        if ($otherId <= 0) { echo $this->json(['ok'=>false]); return; }
 
        $this->msg->markRead($otherId, $me);
        $messages = $this->msg->getNewMessages($me, $otherId, $lastId);
        echo $this->json(['ok' => true, 'messages' => $messages, 'me' => $me]);
    }
 
    /** API: gửi tin nhắn */
    public function send() {
        $this->jsonHeader();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo $this->json(['ok'=>false,'error'=>'Method not allowed']); return;
        }
        $me      = (int)$_SESSION['user']['id'];
        $otherId = (int)($_POST['to']      ?? 0);
        $content = trim($_POST['content']  ?? '');
 
        if ($otherId <= 0 || $content === '') {
            echo $this->json(['ok'=>false,'error'=>'Invalid data']); return;
        }
 
        $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
        $ok = $this->msg->sendMessage($me, $otherId, $content);
        $newId = $ok ? $this->msg->getConnection()->insert_id : 0;
        echo $this->json(['ok' => $ok, 'id' => $newId]);
    }
 
    /** API: danh sách user + unread count */
    public function userList() {
        $this->jsonHeader();
        $me    = (int)$_SESSION['user']['id'];
        $users = $this->msg->getUserList($me);
        foreach ($users as &$u) {
            $u['unread'] = $this->msg->unreadCount((int)$u['id'], $me);
        }
        echo $this->json(['ok' => true, 'users' => $users, 'me' => $me]);
    }
 
    private function jsonHeader(): void {
        header('Content-Type: application/json; charset=utf-8');
    }
    private function json(array $d): string {
        return json_encode($d, JSON_UNESCAPED_UNICODE);
    }
}