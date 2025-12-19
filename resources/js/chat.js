import axios from "axios";

document.addEventListener("DOMContentLoaded", () => {
    const chatBox = document.getElementById("chatBox");
    const messageInput = document.getElementById("messageInput");
    const sendBtn = document.getElementById("sendBtn");
    const activeChatId = document.getElementById("activeChatId")?.value;

    // Auto scroll ke bawah
    const scrollToBottom = () => {
        chatBox.scrollTop = chatBox.scrollHeight;
    };

    scrollToBottom();

    // Kirim pesan
    const sendMessage = async () => {
        const text = messageInput.value.trim();
        if (!text || !activeChatId) return;

        // Tampilkan pesan user
        chatBox.innerHTML += `
            <div class="user-message">${text}</div>
        `;
        scrollToBottom();

        messageInput.value = "";
        sendBtn.disabled = true;

        // Tambahkan efek typing
        const typing = document.createElement("div");
        typing.classList.add("typing-indicator");
        typing.innerHTML = `
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
        `;
        chatBox.appendChild(typing);
        scrollToBottom();

        try {
            const response = await axios.post(`/chat/${activeChatId}/send`, {
                message: text,
            });

            typing.remove();

            chatBox.innerHTML += `
                <div class="bot-message">
                    ${response.data.reply}
                </div>
            `;

            scrollToBottom();
        } catch (error) {
            typing.remove();
            chatBox.innerHTML += `
                <div class="bot-message" style="color:red;">Gagal menghubungi server ❌</div>
            `;
            scrollToBottom();
        }

        sendBtn.disabled = false;
    };

    // Tombol klik
    sendBtn.addEventListener("click", sendMessage);

    // Enter untuk kirim
    messageInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") sendMessage();
    });
});
