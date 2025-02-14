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

