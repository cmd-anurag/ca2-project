window.onload = function() {
    document.getElementById("otpinp1").focus();
}

const form = document.getElementById("otp-form");


// Event listener for filling
form.addEventListener("input", function(event) {
    const otpinputs = [...form.getElementsByClassName("otp-input")];
    const currentInput = event.target;
    const currentIndex = otpinputs.indexOf(currentInput);

    if(currentIndex !== -1 && currentInput.value.length > 0) {
        const nextInput = otpinputs[currentIndex + 1];
        if(nextInput) {
            nextInput.focus();
        }
    }
}) 

// Event listener for deleting
form.addEventListener("keydown", function(event) {
    if(event.key == "Backspace") {
        const otpinputs = [...form.getElementsByClassName("otp-input")];
        const currentInput = event.target;
        const currentIndex = otpinputs.indexOf(currentInput);

        if(currentIndex > 0 && currentInput.value == "") {
            otpinputs[currentIndex-1].focus();
        }
    }
})

let verifybtn = document.getElementById("verifybtn");
verifybtn.addEventListener("click", async () => {
    
    const otpinputs = [...form.getElementsByClassName("otp-input")];

    for (let i = 0; i < otpinputs.length; i++) {
        const element = otpinputs[i];
        if (element.value === "") {
            alert("Incomplete OTP");
            return;
        }
    }

    const formdata = new FormData();

    for (let i = 0; i < otpinputs.length; ++i) {
        const element = otpinputs[i];

        formdata.append(i , element.value);
    }

    let response;
    try {
        response = await fetch("http://localhost/ca2-project/backend/verify_otp.php", {
            method: "POST",
            body: formdata
        })

        const result = await response.json();

        // i'll fix the frontend later. works for now.

        if(result.success) {
            // redirect to dashboard
            console.log(response);
            alert(result.message);
        }
        else {
            // show reason
            console.log(response);
            alert(result.message);
        }
    }
    catch(exception) {
        console.log(exception);
    }
    
})