let logoutbtn = document.getElementById("logout-btn");

logoutbtn.addEventListener("click", logoutUser);

async function logoutUser() {
    let response = await fetch("http://localhost/ca2-project/backend/user_logout.php", {
        method: "POST",
        credentials: "include",
    });

    let result = await response.json();

    if(result.success) {
        window.location.href = "login.html";
    }
    else {
        alert("An error occured.");
    }
}

