if (!localStorage.getItem("token")) {
    window.location.href = "login.html";
}

fetch("../../server/php/get_profile.php", {
    headers: {
        "Authorization": "Bearer " + localStorage.getItem("token")
    }
})
.then((res) => res.json())
.then((user) => {
    document.getElementById("p_username").value = user.username;
    document.getElementById("account_email").innerText = "Email: " + user.email;
    document.getElementById("account_since").innerText = "Member since: " + user.created_at;
});

const saveBtn = document.getElementById("save_profile_btn");
const profileError = document.getElementById("profile_error");

saveBtn.addEventListener("click", () => {
    const username = document.getElementById("p_username").value;
    const currentPassword = document.getElementById("p_current").value;
    const newPassword = document.getElementById("p_new").value;

    profileError.innerText = "";

    if (username == "" || currentPassword == "") {
        profileError.innerText = "username and current password are required";
        return;
    }

    fetch("../../server/php/update_profile.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + localStorage.getItem("token")
        },
        body: JSON.stringify({
            username: username,
            current_password: currentPassword,
            new_password: newPassword
        })
    })
    .then((res) => res.json())
    .then((data) => {
        if (data.success) {
            localStorage.setItem("username", data.username);
            showToast("Profile updated", "success");
            document.getElementById("p_current").value = "";
            document.getElementById("p_new").value = "";
        } else {
            profileError.innerText = data.message || "something went wrong";
        }
    })
    .catch(() => {
        profileError.innerText = "something went wrong";
    });
});