const profileForm = document.getElementById("profileForm");
const statusMessage = document.getElementById("statusMessage");



function isValidEmail(email) {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailPattern.test(email);
}

profileForm.addEventListener("submit", function (e) {
  e.preventDefault();

  //  phone number validation
  const phoneInput = document.getElementById("phone");
  // regex just looks ugly oof
  if (!/^\d{10}$/.test(phoneInput.value)) {
    statusMessage.innerHTML =
      '<i class="fa-solid fa-circle-exclamation mr-2"></i> Phone number must be 10 digits';
    statusMessage.classList.remove(
      "hidden",
      "bg-green-100",
      "text-green-700",
      "border-green-500"
    );
    statusMessage.classList.add("bg-red-100", "text-red-700", "border-red-500");
    statusMessage.scrollIntoView({ behavior: "smooth", block: "center" });
    return;
  }

  // Emergency contact email validation
  const emergencyContactInput = document.getElementById("emergency_contact");
  if (!isValidEmail(emergencyContactInput.value)) {
    statusMessage.innerHTML =
      '<i class="fa-solid fa-circle-exclamation mr-2"></i> Emergency contact must be a valid email address';
    statusMessage.classList.remove(
      "hidden",
      "bg-green-100",
      "text-green-700",
      "border-green-500"
    );
    statusMessage.classList.add("bg-red-100", "text-red-700", "border-red-500");
    statusMessage.scrollIntoView({ behavior: "smooth", block: "center" });
    return;
  }

  //  loading state
  const submitButton = profileForm.querySelector('button[type="submit"]');
  const originalButtonText = submitButton.innerHTML;
  submitButton.innerHTML =
    '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Saving...';
  submitButton.disabled = true;

  //  form data
  const formData = new FormData(profileForm);

  // submit using fetch
  fetch("../backend/profileapi.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      statusMessage.innerHTML = data.success
        ? '<i class="fa-solid fa-circle-check mr-2"></i> ' + data.message
        : '<i class="fa-solid fa-circle-exclamation mr-2"></i> ' + data.message;

      statusMessage.classList.remove(
        "hidden",
        "bg-red-100",
        "text-red-700",
        "bg-green-100",
        "text-green-700",
        "border-red-500",
        "border-green-500"
      );

      if (data.success) {
        statusMessage.classList.add(
          "bg-green-100",
          "text-green-700",
          "border-green-500"
        );
      } else {
        statusMessage.classList.add(
          "bg-red-100",
          "text-red-700",
          "border-red-500"
        );
      }

      submitButton.innerHTML = originalButtonText;
      submitButton.disabled = false;

 
      statusMessage.scrollIntoView({ behavior: "smooth", block: "center" });

      if (data.success) {
        setTimeout(() => {
          statusMessage.classList.add("hidden");
        }, 5000);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      statusMessage.innerHTML =
        '<i class="fa-solid fa-circle-exclamation mr-2"></i> An error occurred. Please try again.';
      statusMessage.classList.remove(
        "hidden",
        "bg-green-100",
        "text-green-700",
        "border-green-500"
      );
      statusMessage.classList.add(
        "bg-red-100",
        "text-red-700",
        "border-red-500"
      );


      submitButton.innerHTML = originalButtonText;
      submitButton.disabled = false;
    });
});

// Logout button 
document.getElementById("logout-btn").addEventListener("click", function () {
  window.location.href = "logout.php";
});
