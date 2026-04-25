<?php
 
class AiController extends Controller {
 
    public function index() {
        
        if (!isset($_SESSION['user'])) {
            header("Location: /Test/index.php?controller=AuthController&action=index");
            exit();
        }
 
        header('cache-control: no-cache, no-store, must-revalidate');
        header('pragma: no-cache');
        header('expires: 0');
 
        $this->View("Home", ["page" => "Ai"]);
    }
 
    /**
     * Tìm và đọc GEMINI_API_KEY từ nhiều nguồn khác nhau
     */
    private function getApiKey(): ?string {
        // 1. Ưu tiên $_ENV và getenv() (nếu server hỗ trợ)
        $key = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?? null;
        if ($key) return $key;
 
        // 2. Thử tìm file .env ở nhiều vị trí khác nhau
        $possiblePaths = [
            __DIR__ . '/../../.env',           // MVC/Controllers/ -> lên 2 cấp
            __DIR__ . '/../../../.env',         // lên 3 cấp (phòng trường hợp cấu trúc khác)
            $_SERVER['DOCUMENT_ROOT'] . '/.env',               // thư mục gốc web
            $_SERVER['DOCUMENT_ROOT'] . '/Test/.env',          // /Test/.env
            dirname($_SERVER['SCRIPT_FILENAME']) . '/.env',    // cùng thư mục index.php
        ];
 
        foreach ($possiblePaths as $path) {
            $realPath = realpath($path);
            if ($realPath && file_exists($realPath)) {
                $lines = file($realPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '' || $line[0] === '#') continue;
                    if (strpos($line, '=') !== false) {
                        [$k, $v] = explode('=', $line, 2);
                        if (trim($k) === 'GEMINI_API_KEY') {
                            return trim($v);
                        }
                    }
                }
            }
        }
 
        return null;
    }
 
    public function chat() {
        header("Content-Type: application/json");
 
        $input   = json_decode(file_get_contents("php://input"), true);
        $message = $input['message'] ?? '';
        $history = $input['history'] ?? [];
 
        if (empty($message)) {
            echo json_encode(["reply" => "Tin nhắn trống"]);
            return;
        }
 
        $apiKey = $this->getApiKey();
 
        if (!$apiKey) {
            echo json_encode(["reply" => "Chưa cấu hình GEMINI_API_KEY"]);
            return;
        }
 
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;
 
        if (count($history) > 20) {
            $history = array_slice($history, -20);
        }
 
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
            CURLOPT_SSL_VERIFYPEER => false, // Một số shared hosting cần dòng này
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
 