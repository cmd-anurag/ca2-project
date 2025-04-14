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
  const typingIndicator = document.getElementById("typing-indicator");
  const username = document.getElementById("user_name")?.innerText || "You";

  if (!chatPopup || !chatToggleBtn || !chatMessages) return;

  // Show/hide chat popup with animation
  chatToggleBtn.addEventListener("click", () => {
    chatPopup.classList.toggle("hidden");
    if (!chatPopup.classList.contains("hidden")) {
      chatInputField.focus();
    }
  });

  // Close chat popup
  chatCloseBtn.addEventListener("click", () => {
    chatPopup.classList.add("hidden");
  });

  // Add a new message to the chat area
  function addMessage(sender, message, isAI = false) {
    const messageElem = document.createElement("div");

    if (isAI) {
      // AI message
      messageElem.className = "flex items-start mb-4";
      messageElem.innerHTML = `
        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-2 flex-shrink-0">
          <i class="fa-solid fa-robot text-blue-600 text-sm"></i>
        </div>
        <div class="bg-white rounded-lg rounded-tl-none py-2 px-3 max-w-[80%] shadow-sm">
          <p class="text-sm text-gray-800">${message}</p>
        </div>
      `;
    } else {
      // user message
      messageElem.className = "flex items-start justify-end mb-4";
      messageElem.innerHTML = `
        <div class="bg-blue-600 text-white rounded-lg rounded-tr-none py-2 px-3 max-w-[80%] shadow-sm">
          <p class="text-sm">${message}</p>
        </div>
        <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center ml-2 flex-shrink-0">
          <span class="text-white text-xs font-bold">${username
            .charAt(0)
            .toUpperCase()}</span>
        </div>
      `;
    }

    chatMessages.appendChild(messageElem);
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  async function sendMessage() {
    const userInput = chatInputField.value.trim();
    if (userInput === "") return;

    // Display user message
    addMessage(username, userInput, false);
    chatInputField.value = "";

    // Show typing indicator
    typingIndicator.classList.remove("hidden");
    chatMessages.scrollTop = chatMessages.scrollHeight;

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

      // Hide typing indicator
      typingIndicator.classList.add("hidden");

      const data = await response.json();
      const reply = data.candidates[0].content.parts[0].text;

      // Display AI message
      addMessage("AI", reply, true);
    } catch (error) {
      // Hide typing indicator
      typingIndicator.classList.add("hidden");

      console.error("Error:", error);
      addMessage(
        "AI",
        "Sorry, I'm having trouble processing your request. Please try again later.",
        true
      );
    }
  }

  // Event listeners for sending messages
  chatSendBtn.addEventListener("click", sendMessage);
  chatInputField.addEventListener("keypress", function (e) {
    if (e.key === "Enter") sendMessage();
  });
});

//  element references
const emergencyBtn = document.getElementById("emergency-btn");
const emergencyModal = document.getElementById("emergency-modal");
const confirmEmergencyBtn = document.getElementById("confirm-emergency");
const cancelEmergencyBtn = document.getElementById("cancel-emergency");
const emergencyStatusModal = document.getElementById("emergency-status-modal");
const statusContent = document.getElementById("status-content");
const closeStatusBtn = document.getElementById("close-status");

emergencyBtn.addEventListener("click", function () {
  emergencyModal.classList.remove("hidden");
});

cancelEmergencyBtn.addEventListener("click", function () {
  emergencyModal.classList.add("hidden");
});

closeStatusBtn.addEventListener("click", function () {
  emergencyStatusModal.classList.add("hidden");
});

confirmEmergencyBtn.addEventListener("click", function () {
  emergencyModal.classList.add("hidden");

  // Show initial status
  statusContent.innerHTML = `
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 text-blue-600 mb-4">
        <i class="fas fa-spinner fa-spin text-2xl"></i>
    </div>
    <h3 class="text-xl font-bold text-gray-900 mb-2">Getting Location & Sending Alert</h3>
    <p class="text-gray-600">Please wait...</p>
  `;
  emergencyStatusModal.classList.remove("hidden");

  // Try to get location, 
  if ("geolocation" in navigator) {
    
    navigator.geolocation.getCurrentPosition(
      sendAlert,  
      () => sendAlert(), 
      { timeout: 5000, maximumAge: 0 }
    );
  } else {
    sendAlert();
  }
  

  async function sendAlert(position = null) {
    
    const requestBody = {};
    if (position) {
      requestBody.latitude = position.coords.latitude;
      requestBody.longitude = position.coords.longitude;
    }
    
    try {
      const response = await fetch("../backend/emergencymail.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify(requestBody)
      });
      
      const data = await response.json();
      
      
      if (data.success) {
        statusContent.innerHTML = `
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-600 mb-4">
              <i class="fas fa-check-circle text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">Alert Sent Successfully</h3>
          <p class="text-gray-600">${data.message}</p>
        `;
      } else {
        statusContent.innerHTML = `
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 text-red-600 mb-4">
              <i class="fas fa-exclamation-circle text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">Alert Failed</h3>
          <p class="text-gray-600">${data.message || "Could not send emergency alert."}</p>
        `;
      }
    } catch (error) {
      console.error("Emergency alert error:", error);
      statusContent.innerHTML = `
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 text-red-600 mb-4">
            <i class="fas fa-exclamation-triangle text-2xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Network Error</h3>
        <p class="text-gray-600">Could not connect to server. Please try again later.</p>
      `;
    }
  }
});