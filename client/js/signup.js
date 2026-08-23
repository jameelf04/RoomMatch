if (localStorage.getItem("token")) {
    window.location.href = "../../index.html";
}

const signupBtn = document.getElementById("signup_btn");
const signupError = document.getElementById("signup_error");
const passwordField = document.getElementById("password");
const strengthFill = document.getElementById("strength_fill");
const strengthLabel = document.getElementById("strength_label");

const checkStrength = (pass) => {
    let score = 0;

    if (pass.length >= 8) {
        score = score + 1;
    }
    if (pass.match(/[A-Z]/)) {
        score = score + 1;
    }
    if (pass.match(/[0-9]/)) {
        score = score + 1;
    }
    if (pass.match(/[^A-Za-z0-9]/)) {
        score = score + 1;
    }

    return score;
};

passwordField.addEventListener("input", () => {
    const score = checkStrength(passwordField.value);

    if (passwordField.value == "") {
        strengthFill.style.width = "0%";
        strengthLabel.innerText = "";
        return;
    }

    if (score <= 1) {
        strengthFill.style.width = "25%";
        strengthFill.style.backgroundColor = "#B34A3C";
        strengthLabel.innerText = "Weak";
    } else if (score == 2) {
        strengthFill.style.width = "50%";
        strengthFill.style.backgroundColor = "#C9A876";
        strengthLabel.innerText = "Fair";
    } else if (score == 3) {
        strengthFill.style.width = "75%";
        strengthFill.style.backgroundColor = "#7C8B78";
        strengthLabel.innerText = "Good";
    } else {
        strengthFill.style.width = "100%";
        strengthFill.style.backgroundColor = "#4A5A48";
        strengthLabel.innerText = "Strong";
    }
});

signupBtn.addEventListener("click", () => {
    const username = document.getElementById("username").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirm_password").value;

    signupError.innerText = "";

    if (username == "" || email == "" || password == "") {
        signupError.innerText = "please fill in all fields";
        return;
    }

    if (password.length < 8) {
        signupError.innerText = "password must be at least 8 characters";
        return;
    }

    if (password != confirmPassword) {
        signupError.innerText = "passwords do not match";
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
            localStorage.setItem("is_admin", data.is_admin);
            window.location.href = "../../index.html";
        } else {
            signupError.innerText = data.message;
        }
    })
    .catch(() => {
        signupError.innerText = "something went wrong";
    });
});