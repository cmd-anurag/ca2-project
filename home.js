const mobileMenuBtn = document.getElementById("mobile-menu-btn");
const closeBtn = document.getElementById("close-btn");
const mobileMenu = document.getElementById("mobile-menu");

mobileMenuBtn.addEventListener("click", () => {
    mobileMenu.classList.toggle("hidden");
});
document.addEventListener("click", (event) => {
    if (!mobileMenu.contains(event.target) && !mobileMenuBtn.contains(event.target)) {
        mobileMenu.classList.add("hidden");
    }
});

closeBtn.addEventListener("click", () => {
    mobileMenu.classList.add("hidden");
});
