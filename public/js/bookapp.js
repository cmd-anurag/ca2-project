const modalBackdrop = document.getElementById("modal-backdrop");
const modalContainer = document.getElementById("modal-container");
const modalTitle = document.getElementById("modal-title");
const modalMessage = document.getElementById("modal-message");
const successIcon = document.getElementById("success-icon");
const errorIcon = document.getElementById("error-icon");
const closeModal = document.getElementById("close-modal");
const modalCloseBtn = document.getElementById("modal-close-btn");
const loader = document.getElementById("loader-element");

//show modal
function showModal(success, message) {
    loader.classList.add("hidden");
    modalMessage.textContent = message;

    if (success) {
        modalTitle.textContent = "Appointment Booked";
        successIcon.classList.remove("hidden");
        errorIcon.classList.add("hidden");
        modalTitle.classList.remove("text-red-600");
        modalTitle.classList.add("text-green-600");
    } else {
        modalTitle.textContent = "Booking Failed";
        errorIcon.classList.remove("hidden");
        successIcon.classList.add("hidden");
        modalTitle.classList.remove("text-green-600");
        modalTitle.classList.add("text-red-600");
    }

    modalBackdrop.classList.remove("hidden");
    setTimeout(() => {
        modalBackdrop.classList.remove("opacity-0");
        modalContainer.classList.remove("scale-95");
        modalContainer.classList.add("scale-100");
    }, 10);
}

//hide modal
function hideModal() {
    modalBackdrop.classList.add("opacity-0");
    modalContainer.classList.remove("scale-100");
    modalContainer.classList.add("scale-95");
    setTimeout(() => {
        modalBackdrop.classList.add("hidden");
    }, 300);
}

// Event listeners to close modal
closeModal.addEventListener("click", hideModal);
modalCloseBtn.addEventListener("click", hideModal);
modalBackdrop.addEventListener("click", (e) => {
    if (e.target === modalBackdrop) {
        hideModal();
    }
});

let dateInput = document.getElementById("appdate");
let specializationInput = document.getElementById("specializationInput");
let remarksInput = document.getElementById("remarksInput");

let bookButton = document.getElementById("book-button");

bookButton.addEventListener("click", async () => {
    let dateValue = dateInput.value;
    let specializationValue = specializationInput.value;
    let remarksValue = remarksInput.value;

    if (!dateValue) {
        dateInput.classList.add("ring-2", "ring-red-500");
        return;
    }

    if (specializationValue === "Select a specialization") {
        specializationInput.classList.add("ring-2", "ring-red-500");
        return;
    }
    loader.classList.remove("hidden");

    const formdata = new FormData();
    formdata.append("date", dateValue);
    formdata.append("specialization", specializationValue);
    formdata.append("remarks", remarksValue);

    try {
        const res = await fetch(
            "http://localhost/ca2-project/backend/book_appointment.php",
            {
                method: "POST",
                credentials: "include",
                body: formdata,
            }
        );

        const result = await res.json();
        showModal(result.success, result.message);
        
    } catch (error) {
        showModal(false, "An unexpected error occurred. Please try again.");
        console.error("Error:", error);
    }
});

dateInput.addEventListener("focus", () => {
    dateInput.classList.remove("ring-2");
});

specializationInput.addEventListener("focus", () => {
    specializationInput.classList.remove("ring-2");
});
