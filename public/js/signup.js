let signupbtn = document.getElementById("signupbtn");

const nameField = document.getElementById("name-input");
const emailField = document.getElementById("email-input");
const passwordField = document.getElementById("password-input");

let loader = document.getElementById("loader-element");

function isValidEmail(email) {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailPattern.test(email);
}

signupbtn.addEventListener("click", async () => {    

    if(!nameField.value) {
        nameField.classList.add("border-2", "border-red-500", "text-red-500");
        nameField.placeholder = "A name is required.";
        return
    }
    if(!emailField.value) {
        emailField.classList.add("border-2", "border-red-500", "text-red-500");
        emailField.placeholder = "Email cannot be empty.";
        return;
    }

    if(!isValidEmail(emailField.value)) {
        emailField.classList.add("border-2", "border-red-500", "text-red-500");
        emailField.placeholder = "Please enter a valid email.";
        emailField.value = "";
        return;
    }

    if(!passwordField.value) {
        passwordField.classList.add("border-2", "border-red-500", "text-red-500");
        passwordField.placeholder = "Password is required.";
        return;
    }

    // alert("validated");

    loader.classList.remove("hidden");


    let response;
    try {
        // send the form data to backend
        const formdata = new FormData();
        formdata.append("name", nameField.value);
        formdata.append("email", emailField.value);
        formdata.append("password", passwordField.value);
    
        response = await fetch("http://localhost/ca2-project/backend/send_otp.php", {
            method : "POST",
            credentials: "include",
            body : formdata
        })
        const result = await response.json();
    
        if(result.success) {
            console.log("sent otp");
            window.location.href = result.redirect;
        }
        else {
            console.error("some error occured");
        }
    }
    catch(error) {
        console.log(error);
    }

})


nameField.addEventListener("focus", () => {
    nameField.placeholder = "";
    nameField.classList.remove("border-2", "border-red-500", "text-red-500");
})
emailField.addEventListener("focus", () => {
    emailField.placeholder = "";
    emailField.classList.remove("border-2", "border-red-500", "text-red-500");
})
passwordField.addEventListener("focus", () => {
    passwordField.placeholder = "";
    passwordField.classList.remove("border-2", "border-red-500", "text-red-500");
})