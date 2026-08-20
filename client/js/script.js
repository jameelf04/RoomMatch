    if (!localStorage.getItem("token")) {
    window.location.href = "login.html";
}
    
    fetch("../../server/php/save_session.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + localStorage.getItem("token")
        },
        body: JSON.stringify(payload)
    })
    .then((res) => res.json())
    .then((data) => {
        if (data.session_id) {
            window.location.href = "results.html?session=" + data.session_id;
        } else if (data.error == "unauthorized") {
            alert("please log in first");
            window.location.href = "login.html";
        } else {
            alert("something went wrong saving your session");
        }
    })
    .catch(() => {
        alert("something went wrong saving your session");
    });