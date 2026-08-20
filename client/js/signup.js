const signupBtn = document.getElementById("signup_btn");
const signupError = document.getElementById("signup_error");

signupBtn.addEventListener("click", () => {
    const username = document.getElementById("username").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    if (username == "" || email == "" || password == "") {
        signupError.innerText = "please fill in all fields";
        return;
    }

    const payload = {
        username: username,
        email: email,
        password: password
    };

    fetch("../../server/php/signup.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
    })
    .then((res) => res.json())
    .then((data) => {
        if (data.success) {
            localStorage.setItem("token", data.token);
            localStorage.setItem("username", data.username);
            window.location.href = "upload.html";
        } else {
            signupError.innerText = data.error;
        }
    })
    .catch(() => {
        signupError.innerText = "something went wrong";
    });
});