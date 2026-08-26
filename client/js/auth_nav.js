const authNav = document.querySelector(".auth_nav");
const token = localStorage.getItem("token");
const username = localStorage.getItem("username");

const pagePrefix = window.location.pathname.includes("/pages/") ? "" : "client/pages/";

if (token) {
    let adminLink = "";
    if (localStorage.getItem("is_admin") == "1") {
        adminLink = `<a href="${pagePrefix}admin.html">Admin</a>`;
    }

    authNav.innerHTML = `
        <span class="nav_user">Hi, ${username}</span>
        ${adminLink}
        <a href="#" id="logout_btn">Log Out</a>
    `;

    const logoutBtn = document.getElementById("logout_btn");
    logoutBtn.addEventListener("click", (e) => {
        e.preventDefault();
        localStorage.removeItem("token");
        localStorage.removeItem("username");
        localStorage.removeItem("is_admin");
        window.location.href = pagePrefix == "" ? "../../index.html" : "index.html";
    });
} else {
    authNav.innerHTML = `
        <a href="${pagePrefix}login.html">Log In</a>
        <a href="${pagePrefix}signup.html" class="filled">Sign Up</a>
    `;
}

const startBtns = document.querySelectorAll(".start_btn");

for (let i = 0; i < startBtns.length; i++) {
    if (token) {
        startBtns[i].href = "client/pages/upload.html";
    }
}

const navToggleHtml = `<button id="nav_toggle" class="nav_toggle">&#9776;</button>`;
const header = document.querySelector(".site_header");
if (header && window.innerWidth <= 700) {
    header.insertAdjacentHTML("afterbegin", navToggleHtml);
    document.body.classList.add("nav_collapsed");

    const toggle = document.getElementById("nav_toggle");
    toggle.addEventListener("click", () => {
        header.classList.toggle("nav_open");
    });
}