<link rel="stylesheet" href="/Test/Public/Css/ai.css">

<div class="Page-Ai">

    <div class="ai-chatbox">

        <div class="chat-header">
            <button class="clear-btn" onclick="clearChat()">Xóa chat</button>
        </div>

        <div id="chat-box"></div>

        <div class="ai-input">
            <input type="text" id="message" placeholder="Nhập câu hỏi..." onkeypress="handleKey(event)">
            <button onclick="sendMessage()">Gửi</button>
        </div>

    </div>

</div>


<script>

let chatBox = document.getElementById("chat-box");


// load chat khi mở trang
window.onload = function(){

let savedChat = localStorage.getItem("chatHistory");

if(savedChat){
chatBox.innerHTML = savedChat;
chatBox.scrollTop = chatBox.scrollHeight;
}

};


async function sendMessage(){

let message = document.getElementById("message").value.trim();

if(message === "") return;

chatBox.innerHTML += `
<div class="user-msg">
<span>${message}</span>
</div>
`;

document.getElementById("message").value = "";

saveChat();

try{

let response = await fetch("/Test/index.php?controller=AiController&action=chat",{
method:"POST",
headers:{
"Content-Type":"application/x-www-form-urlencoded"
},
body:"message="+encodeURIComponent(message)
});

let data = await response.json();

chatBox.innerHTML += `
<div class="ai-msg">
<span>${data.reply}</span>
</div>
`;

saveChat();

chatBox.scrollTop = chatBox.scrollHeight;

}catch(err){

chatBox.innerHTML += `
<div class="ai-msg">
<b>AI:</b> Lỗi kết nối AI
</div>
`;

}

}


// lưu chat
function saveChat(){

localStorage.setItem("chatHistory", chatBox.innerHTML);

}


// Enter gửi
function handleKey(e){

if(e.key === "Enter"){
sendMessage();
}

}


// xóa chat
function clearChat(){

if(confirm("Bạn có chắc muốn xóa chat?")){

localStorage.removeItem("chatHistory");

chatBox.innerHTML = "";

}

}

</script>