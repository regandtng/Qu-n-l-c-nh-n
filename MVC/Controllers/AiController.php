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

            public function chat(){

            header("Content-Type: application/json");

            $message = $_POST['message'] ?? '';

            if(empty($message)){
            echo json_encode(["reply"=>"Tin nhắn trống"]);
            return;
            }

            $envPath="C:/xampp/htdocs/TEST/.env";
            $apiKey=null;

            if(file_exists($envPath)){

            $lines=file($envPath,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);

            foreach($lines as $line){

            if(strpos($line,'=')!==false){

            list($key,$value)=explode('=',$line,2);

            if(trim($key)==='GEMINI_API_KEY'){
            $apiKey=trim($value);
            break;
            }

            }

            }

            }

            if(!$apiKey){
            echo json_encode(["reply"=>"Chưa cấu hình GEMINI_API_KEY"]);
            return;
            }

           $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

            $data=[
            "contents"=>[
            [
            "parts"=>[
            ["text"=>$message]
            ]
            ]
            ]
            ];

            $ch=curl_init($url);

            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch,CURLOPT_POST,true);

            curl_setopt($ch,CURLOPT_HTTPHEADER,[
            "Content-Type: application/json"
            ]);

            curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($data));

            $result=curl_exec($ch);

            if(curl_errno($ch)){
            echo json_encode(["reply"=>"Lỗi CURL: ".curl_error($ch)]);
            curl_close($ch);
            return;
            }

            curl_close($ch);

            $response=json_decode($result,true);

            $reply=$response["candidates"][0]["content"]["parts"][0]["text"] ?? "AI không trả lời";

            echo json_encode(["reply"=>$reply]);

            }

}