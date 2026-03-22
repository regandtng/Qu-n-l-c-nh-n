<?php
 
class AiController extends Controller {
 
    public function index() {
        
        if (!isset($_SESSION['user'])) {
            header("Location: /Test/index.php?controller=AutController&action=index");
            exit();
        }

        header('cache-control: no-cache, no-store, must-revalidate');
        header('pragma: no-cache');
        header('expires: 0');

        $this->View("Home", ["page" => "Ai"]);
    }
 
    public function chat() {
        header("Content-Type: application/json");
 
        // Nhận JSON body
        $input   = json_decode(file_get_contents("php://input"), true);
        $message = $input['message'] ?? '';
        $history = $input['history'] ?? [];
 
        if (empty($message)) {
            echo json_encode(["reply" => "Tin nhắn trống"]);
            return;
        }
 
        // Đọc API key từ .env
        $apiKey  = null;
        $envPath = "C:/xampp/htdocs/TEST/.env";
        if (file_exists($envPath)) {
            foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (strpos($line, '=') !== false) {
                    [$key, $value] = explode('=', $line, 2);
                    if (trim($key) === 'GEMINI_API_KEY') {
                        $apiKey = trim($value);
                        break;
                    }
                }
            }
        }
 
        if (!$apiKey) {
            echo json_encode(["reply" => "Chưa cấu hình GEMINI_API_KEY"]);
            return;
        }
 
        // Model ổn định, key của bạn support
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;
 
        // Giới hạn history 20 lượt
        if (count($history) > 20) {
            $history = array_slice($history, -20);
        }
 
        // Ghép history + tin nhắn mới
        $contents   = $history;
        $contents[] = ["role" => "user", "parts" => [["text" => $message]]];
 
        $payload = json_encode(["contents" => $contents]);
 
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 30,
        ]);
 
        $result   = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
 
        if (curl_errno($ch)) {
            $err = curl_error($ch);
            curl_close($ch);
            echo json_encode(["reply" => "Lỗi kết nối: $err"]);
            return;
        }
        curl_close($ch);
 
        $response = json_decode($result, true);
 
        // Xử lý lỗi HTTP
        if ($httpCode !== 200) {
            $msg  = $response['error']['message'] ?? "Lỗi không xác định";
            $code = $response['error']['code']    ?? $httpCode;
            $errorReply = match(true) {
                $httpCode === 429 => ["reply" => "⏳ API đang bận, thử lại sau vài giây.", "error_code" => 429],
                $httpCode === 403 => ["reply" => "API key không hợp lệ hoặc hết quyền."],
                $httpCode === 400 => ["reply" => "Yêu cầu không hợp lệ: $msg"],
                default           => ["reply" => "Lỗi API ($code): $msg"],
            };
            echo json_encode($errorReply);
            return;
        }
 
        $reply = $response["candidates"][0]["content"]["parts"][0]["text"] ?? "AI không trả lời";
        echo json_encode(["reply" => $reply]);
    }
}