<link rel="stylesheet" href="/Test/Public/Css/ai.css">
 
<div class="Page-Ai">
    
    <div class="ai-chatbox">
 
        <div class="chat-header">
            <button class="clear-btn" onclick="clearChat()">🗑 Xóa chat</button>
        </div>
 
        <div id="chat-box"></div>
 
        <div class="ai-input">
            <input type="text" id="message" placeholder="Nhập tin nhắn..." onkeydown="handleKey(event)">
            <button onclick="sendMessage()">Gửi</button>
        </div>
 
    </div>

</div>
 
<script>
    let chatBox     = document.getElementById("chat-box");
    let chatHistory = [];
    let isSending   = false;
 
    window.onload = function () {
        let savedHTML = localStorage.getItem("chatHistory");
        let savedData = localStorage.getItem("chatData");
        if (savedHTML) { chatBox.innerHTML = savedHTML; chatBox.scrollTop = chatBox.scrollHeight; }
        if (savedData) { try { chatHistory = JSON.parse(savedData); } catch(e) { chatHistory = []; } }
    };
 
    async function sendMessage() {
        if (isSending) return;
        let message = document.getElementById("message").value.trim();
        if (message === "") return;
 
        isSending = true;
        document.getElementById("message").value = "";
 
        appendMsg("user-msg", message);
        chatHistory.push({ role: "user", parts: [{ text: message }] });
 
        let loadingEl = addLoading();
 
        try {
            let response = await fetch("/Test/index.php?controller=AiController&action=chat", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ message: message, history: chatHistory })
            });
 
            let data = await response.json();
            loadingEl.remove();
 
            if (data.error_code === 429) {
                // Hiện thông báo, KHÔNG retry tự động
                appendMsg("ai-msg", "⏳ API đang bận, hãy chờ 30 giây rồi gửi lại.");
                // Xóa tin nhắn user vừa push ra khỏi history vì chưa được trả lời
                chatHistory.pop();
            } else {
                appendMsg("ai-msg", data.reply);
                chatHistory.push({ role: "model", parts: [{ text: data.reply }] });
                saveChat();
            }
 
        } catch (err) {
            loadingEl.remove();
            appendMsg("ai-msg", "⚠️ Lỗi kết nối, vui lòng thử lại.");
            chatHistory.pop();
        }
 
        isSending = false;
    }
 
    function addLoading() {
        let div  = document.createElement("div");
        div.className = "ai-msg";
        div.innerHTML = "<span>✦ AI đang trả lời...</span>";
        chatBox.appendChild(div);
        chatBox.scrollTop = chatBox.scrollHeight;
        return div;
    }
 
    function appendMsg(className, text) {
        let div  = document.createElement("div");
        div.className = className;
        let span = document.createElement("span");
        span.innerHTML = formatText(text);
        div.appendChild(span);
        chatBox.appendChild(div);
        chatBox.scrollTop = chatBox.scrollHeight;
    }
 
    function formatText(text) {
        return text
            .replace(/\*\*(.+?)\*\*/g, "<strong>$1</strong>")
            .replace(/\*(.+?)\*/g, "<em>$1</em>")
            .replace(/\n/g, "<br>");
    }
 
    function saveChat() {
        try {
            localStorage.setItem("chatHistory", chatBox.innerHTML);
            localStorage.setItem("chatData", JSON.stringify(chatHistory));
        } catch(e) {}
    }
 
    function handleKey(e) {
        if (e.key === "Enter" && !e.shiftKey) { e.preventDefault(); sendMessage(); }
    }
 
    function clearChat() {
        if (confirm("Bạn có chắc muốn xóa toàn bộ chat?")) {
            try {
                localStorage.removeItem("chatHistory");
                localStorage.removeItem("chatData");
            } catch(e) {}
            chatHistory = [];
            chatBox.innerHTML = "";
        }
    }
</script>