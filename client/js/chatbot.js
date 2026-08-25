const chatToggle = document.getElementById("chat_toggle");
const chatPanel = document.getElementById("chat_panel");
const chatMessages = document.getElementById("chat_messages");
const chatInput = document.getElementById("chat_input");
const chatSend = document.getElementById("chat_send");

const chatPathPrefix = window.location.pathname.includes("/pages/") ? "../../" : "";

chatToggle.addEventListener("click", () => {
    chatPanel.classList.toggle("open");
});

const addMessage = (text, sender) => {
    const bubble = document.createElement("div");
    bubble.className = "chat_bubble " + sender;
    bubble.innerText = text;
    chatMessages.appendChild(bubble);
    chatMessages.scrollTop = chatMessages.scrollHeight;
};

const sendMessage = () => {
    const text = chatInput.value;
    if (text == "") {
        return;
    }

    addMessage(text, "user");
    chatInput.value = "";

    fetch(chatPathPrefix + "server/php/chat.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ message: text })
    })
    .then((res) => res.json())
    .then((data) => {
        addMessage(data.reply, "bot");
    })
    .catch(() => {
        addMessage("Sorry, something went wrong.", "bot");
    });
};

chatSend.addEventListener("click", sendMessage);

chatInput.addEventListener("keypress", (e) => {
    if (e.key == "Enter") {
        sendMessage();
    }
});