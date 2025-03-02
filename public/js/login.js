let loginbutton = document.getElementById("loginbutton");

let emailField = document.getElementById("emailinput");
let passwordField = document.getElementById("passwordinput");

// a basic email vaidation function
function isValidEmail(email) {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailPattern.test(email);
}

loginbutton.addEventListener("click", async () => {

    if(emailField.value === "") {
        emailField.classList.add("ring-2", "ring-red-500", "text-red-700");
        emailField.placeholder = "Enter your Email!";
        return;
    }
    if(!isValidEmail(emailField.value)) {
        emailField.classList.add("ring-2", "ring-red-500", "text-red-700");
        emailField.value = "";
        emailField.placeholder = "Enter a valid Email!";
        return;
    }
    if(passwordField.value === "") {
        passwordField.classList.add("ring-2", "ring-red-500", "text-red-700");
        passwordField.placeholder = "Enter your Password!";
        return;
    }
    // validated

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
        }
        else {
            alert(result.message);
        }
    }
    catch(exception) {
        console.log(exception);
    }

})

emailField.addEventListener("focus", () => {
    emailField.placeholder = "";
    emailField.classList.remove("ring-2", "ring-red-500", "text-red-700");
})
passwordField.addEventListener("focus", () => {
    passwordField.placeholder = "";
    passwordField.classList.remove("ring-2", "ring-red-500", "text-red-700");
})