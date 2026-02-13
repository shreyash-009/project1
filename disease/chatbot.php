<?php include "db.php"; ?>
<!DOCTYPE html>
<html>
<head>
    <title>AI Symptom Checker</title>
    <link rel="stylesheet" href="../main.css">
    <style>
        body { font-family: Arial, sans-serif; margin:0; padding:0; }
        .container { max-width: 800px; margin: 100px auto 50px; padding:20px; }
        .chatbox {
            border:1px solid #ccc; border-radius:10px;
            padding:-30px; height:400px; overflow-y:auto;
            background:#f5f5f5;
            display:flex; flex-direction:column;
        }
        .message { padding:10px 15px; border-radius:20px; margin:5px 0; max-width:75%; word-wrap:break-word; }
        .user { align-self:flex-end; background:#0b5394; color:white; }
        .bot { align-self:flex-start; background:#38761d; color:white; }
        .disclaimer { font-size:12px; color:#555; margin-top:10px; text-align:center; }
        input { width:70%; padding:10px; font-size:16px; border-radius:5px; border:1px solid #ccc; }
        button { padding:10px 20px; font-size:16px; border:none; border-radius:5px; background:#0b5394; color:white; cursor:pointer; }
        form { display:flex; gap:10px; margin-top:10px; }
     
    </style>
</head>
<body>

<div class="container">
    <h1>AI Symptom Checker</h1>

    <div class="chatbox" id="chatbox"></div>

    <form id="chat-form">
        <input type="text" id="message" placeholder="Enter your symptoms (e.g., fever and headache)">
        <button type="submit">Send</button>
    </form>
    <p class="disclaimer">
        ⚠️ This platform is for educational purposes only. Results may not be accurate. Please consult a qualified healthcare provider.
    </p>
</div>

<script>
const chatbox = document.getElementById('chatbox');
const form = document.getElementById('chat-form');

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const message = document.getElementById('message').value.trim();
    if(!message) return;

    appendMessage(message, 'user');
    appendMessage('Loading...', 'bot', true);

    try {
        const formData = new FormData();
        formData.append('message', message);

        const res = await fetch('api_checker.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();
        removeLoading();

        if(data.conditions && data.conditions.length > 0){
            data.conditions.forEach(d => {
                appendMessage(`${d.name} (Probability: ${(d.probability*100).toFixed(1)}%)`, 'bot');
            });
            appendMessage("⚠️ This is not a medical diagnosis. Consult a doctor for proper advice.", 'bot', true);
        } else if(data.error){
            appendMessage(`API Error: ${data.error}`, 'bot');
            showFallback(message);
        } else {
            showFallback(message);
        }

    } catch(err){
        console.error(err);
        removeLoading();
        appendMessage("Error fetching API data.", 'bot');
        showFallback(message);
    }

    document.getElementById('message').value = '';
});

// ===== Helper Functions =====
function appendMessage(text, type, temp=false){
    const p = document.createElement('p');
    p.classList.add('message', type);
    if(temp) p.classList.add('temp');
    p.innerHTML = text;
    chatbox.appendChild(p);
    chatbox.scrollTop = chatbox.scrollHeight;
}

function removeLoading(){
    const temp = chatbox.querySelector('.temp');
    if(temp) temp.remove();
}

// ===== Fallback to local DB if API fails =====
async function showFallback(userInput){
    try {
        const res = await fetch('local_checker.php', {
            method: 'POST',
            body: new URLSearchParams({message: userInput})
        });
        const data = await res.json();
        if(data.length > 0){
            appendMessage("Bot: Possible diseases:", 'bot');
            data.forEach(d => {
                appendMessage(`- ${d.name} (Match Score: ${d.match_count})`, 'bot');
            });
            appendMessage("⚠️ This is not a medical diagnosis. Consult a doctor for proper advice.", 'bot', true);
        } else {
            appendMessage("Bot (Local DB): No matching diseases found. ⚠️ Please consult a doctor.", 'bot', true);
        }
    } catch(err){
        console.error(err);
        appendMessage("Local DB also failed.", 'bot');
    }
}
</script>

</body>
</html>
