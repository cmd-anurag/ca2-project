document.querySelectorAll(".approve-btn").forEach((button) => {
    button.addEventListener("click", ()=> {
        if(button.hasAttribute('disabled')) return;

        const appointmentId = button.getAttribute('data-appointment-id');

        updateAppointmentStatus(appointmentId, 'approved');
    })
})

document.querySelectorAll(".reject-btn").forEach((button) => {
    button.addEventListener("click", () => {
        if(button.hasAttribute('disabled')) return;

        const appointmentId = button.getAttribute('data-appointment-id');
        updateAppointmentStatus(appointmentId, 'rejected');
    })
})

let loader = document.getElementById('loader-element');

const updateAppointmentStatus = async (id, status) => {

    let url = "";

    if(status === "approved") {
        url = "http://localhost/ca2-project/backend/approve_app.php";    
    }
    else if(status === "rejected") {
        url = "http://localhost/ca2-project/backend/reject_app.php";  
    }

    const formData = new FormData();
    formData.append("appointment_id", id);

    try {
        loader.classList.remove('hidden');

        let response = await fetch(url, {
            method: 'POST',
            credentials: 'include',
            body: formData
        });
    
        let result = await response.json();
    
        if(result.success) {
            window.location.reload();
        }
        else {
            alert(result.message);
        }
    }
    catch(e) {
        console.log(e);
    }
    finally {
        loader.classList.add('hidden');
    }

}