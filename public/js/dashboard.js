let logoutbtn = document.getElementById("logout-btn");
logoutbtn.addEventListener("click", logoutUser);

async function logoutUser() {
  let response = await fetch(
    "http://localhost/ca2-project/backend/user_logout.php",
    {
      method: "POST",
      credentials: "include",
    }
  );

  let result = await response.json();

  if (result.success) {
    window.location.href = "login.html";
  } else {
    alert("An error occured.");
  }
}

document.addEventListener("DOMContentLoaded", function () {
  const chatPopup = document.getElementById("chat-popup");
  const chatToggleBtn = document.getElementById("chat-toggle-btn");
  const chatCloseBtn = document.getElementById("chat-close-btn");
  const chatSendBtn = document.getElementById("chat-send-btn");
  const chatInputField = document.getElementById("chat-input-field");
  const chatMessages = document.getElementById("chat-messages");
  const username = document.getElementById("user_name").innerText;

  
  chatToggleBtn.addEventListener("click", () => {
    chatPopup.classList.toggle("hidden");
  });

  
  chatCloseBtn.addEventListener("click", () => {
    chatPopup.classList.add("hidden");
  });

  // add a new message to the chat area
  function addMessage(sender, message) {
    const messageElem = document.createElement("div");
    messageElem.innerHTML = `<span class="font-bold">${sender}:</span> ${message}`;
    chatMessages.appendChild(messageElem);
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  async function sendMessage() {
    const userInput = chatInputField.value.trim();
    if (userInput === "") return;
    addMessage(username, userInput);
    chatInputField.value = "";

    const formData = new FormData();
    formData.append("userinput", userInput);

    try {
      const response = await fetch(
        "http://localhost/ca2-project/backend/ai_api.php",
        {
          method: "POST",
          body: formData,
        }
      );
      const data = await response.json();
      const reply = data.candidates[0].content.parts[0].text;
      addMessage("AI", reply);
    } catch (error) {
      console.error("Error:", error);
      addMessage("AI", "Error processing request");
    }
  }

  // listener for send button and enter key
  chatSendBtn.addEventListener("click", sendMessage);
  chatInputField.addEventListener("keypress", function (e) {
    if (e.key === "Enter") sendMessage();
  });
});
