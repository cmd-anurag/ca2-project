let loginbutton = document.getElementById("loginbutton");

let emailField = document.getElementById("emailinput");
let passwordField = document.getElementById("passwordinput");

let loader = document.getElementById("loader-element");

let emailError = document.getElementById("emailError");
let passwordError = document.getElementById("passwordError")

// a basic email vaidation function
function isValidEmail(email) {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailPattern.test(email);
}

loginbutton.addEventListener("click", async () => {

    if(emailField.value === "" || !isValidEmail(emailField.value)) {
        emailError.classList.add("max-h-20");
        emailError.classList.add("mt-1");
        return;
    }

    if(passwordField.value === "") {
        passwordError.classList.add('max-h-20');
        passwordError.classList.add('mt-1');
        return;
    }
    // validated
    loader.classList.remove("hidden");
    const formdata = new FormData();

    formdata.append("email", emailField.value);
    formdata.append("password", passwordField.value);

    let response;
    try {
        response = await fetch("http://localhost/ca2-project/backend/user_login.php", {
            method: "POST",
            credentials: "include",
            body: formdata
        })

        let result = await response.json();
        
        if(result.success) {
            window.location.href = "dashboard.php";
            // loader.classList.add('hidden');
        }
        else {
            loader.classList.add('hidden');
            alert(result.message);
        }
    }
    catch(exception) {
        loader.classList.add('hidden');
        console.log(exception);
    }

})

emailField.addEventListener('focus', () => {
    emailError.classList.remove("max-h-20");
    emailError.classList.remove("mt-1");
})

passwordField.addEventListener('focus', () => {
    passwordError.classList.remove('max-h-20');
    passwordError.classList.remove('mt-1');
})

document.getElementById("togglePassword").addEventListener("click", function () {
    const passwordInput = document.getElementById("passwordinput");
    const eyeIcon = document.getElementById("eyeIcon");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        eyeIcon.classList.remove("fa-eye");
        eyeIcon.classList.add("fa-eye-slash");
    } else {
        passwordInput.type = "password";
        eyeIcon.classList.remove("fa-eye-slash");
        eyeIcon.classList.add("fa-eye"); 
    }
});
