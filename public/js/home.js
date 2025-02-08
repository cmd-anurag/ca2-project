const mobileMenuBtn = document.getElementById("mobile-menu-btn");
const mobileMenu = document.getElementById("mobile-menu");

mobileMenuBtn.addEventListener("click", ()=>{
    mobileMenu.classList.toggle("hidden");
});

document.addEventListener("click", (event)=>{
    if (!mobileMenu.contains(event.target) && !mobileMenuBtn.contains(event.target)) {
        mobileMenu.classList.add("hidden");
    }
});


const items = document.querySelectorAll('.faq-question');

function toggleAccordion() {
    const answer = this.nextElementSibling;
    answer.classList.toggle('hidden');
}

items.forEach((item) => item.addEventListener('click', toggleAccordion));
