let signupbtn = document.getElementById("signupbtn");

const nameField = document.getElementById("name-input");
const emailField = document.getElementById("email-input");
const passwordField = document.getElementById("password-input");

const invalidName = document.getElementById("invalidname");
const invalidEmail = document.getElementById("invalidemail");
const invalidPassword = document.getElementById("invalidpass");
const registeredEmail = document.getElementById("registered-email");


let loader = document.getElementById("loader-element");

function isValidEmail(email) {
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailPattern.test(email);
}

// SIGNUP BUTTON CLICK LISTENER
signupbtn.addEventListener("click", async () => {
  if (!nameField.value) {
    invalidName.classList.remove("hidden");
    return;
  }
  if (!emailField.value || !isValidEmail(emailField.value)) {
    invalidEmail.classList.remove("hidden");
    return;
  }

  if (!passwordField.value || passwordField.value.length < 8) {
    invalidPassword.classList.remove("hidden");
    return;
  }

  loader.classList.remove("hidden");

  // check if the email already exists

  let emailres = false;

  try {
    const formdata = new FormData();
    formdata.append("email", emailField.value);
    emailres = await fetch(
      "http://localhost/ca2-project/backend/verify_email.php",
      {
        method: "POST",
        credentials: "include",
        body: formdata,
      }
    );

    const result = await emailres.json();

    if (!result.success) {
      registeredEmail.classList.remove('hidden');
      return;
    }
  } catch (error) {
    console.log(error);
  }
  finally{
    loader.classList.add("hidden");
  }

  let response;
  try {
    const formdata = new FormData();
    formdata.append("name", nameField.value);
    formdata.append("email", emailField.value);
    formdata.append("password", passwordField.value);

    response = await fetch(
      "http://localhost/ca2-project/backend/send_otp.php",
      {
        method: "POST",
        credentials: "include",
        body: formdata,
      }
    );
    const result = await response.json();

    if (result.success) {
      console.log("sent otp");
      window.location.href = result.redirect;
    } else {
      console.error("some error occured");
    }
  } catch (error) {
    console.log(error);
  } finally {
    loader.classList.add("hidden");
  }
});


nameField.addEventListener("focus", () => {
  invalidName.classList.add("hidden");
});
emailField.addEventListener("focus", () => {
  invalidEmail.classList.add('hidden');
  registeredEmail.classList.add('hidden');
});
passwordField.addEventListener("focus", () => {
  invalidPassword.classList.add('hidden');
});


const toggleBtns = document.querySelectorAll(".password-toggle");
toggleBtns.forEach((btn) => {
  btn.addEventListener("click", function () {
    const input = btn.previousElementSibling;
    const icon = btn.querySelector("i");

    if (input.type === "password") {
      input.type = "text";
      icon.classList.remove("fa-eye");
      icon.classList.add("fa-eye-slash");
    } else {
      input.type = "password";
      icon.classList.remove("fa-eye-slash");
      icon.classList.add("fa-eye");
    }
  });
});