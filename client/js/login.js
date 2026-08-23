
if (localStorage.getItem("token")) {
    window.location.href = "../../index.html";
}

const loginBtn = document.getElementById("login_btn");
const loginError = document.getElementById("login_error");

loginBtn.addEventListener("click", () => {
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    if (email == "" || password == "") {
        loginError.innerText = "please fill in all fields";
        return;
    }

    const payload = {
        email: email,
        password: password
    };

    fetch("../../server/php/login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
    })
    .then((res) => res.json())
    .then((data) => {
                if (data.success) {
            localStorage.setItem("token", data.token);
            localStorage.setItem("username", data.username);
            localStorage.setItem("is_admin", data.is_admin);
            window.location.href = "../../index.html";
        } else {
            loginError.innerText = data.error;
        }
    })
    .catch(() => {
        loginError.innerText = "something went wrong";
    });
});