const authNav = document.querySelector(".auth_nav");
const token = localStorage.getItem("token");
const username = localStorage.getItem("username");

const pagePrefix = window.location.pathname.includes("/pages/") ? "" : "client/pages/";

if (token) {
    authNav.innerHTML = `
        <span class="nav_user">Hi, ${username}</span>
        <a href="#" id="logout_btn">Log Out</a>
    `;

    const logoutBtn = document.getElementById("logout_btn");
    logoutBtn.addEventListener("click", (e) => {
        e.preventDefault();
        localStorage.removeItem("token");
        localStorage.removeItem("username");
        if (pagePrefix == "") {
            window.location.href = "../../index.html";
        } else {
            window.location.href = "index.html";
        }
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