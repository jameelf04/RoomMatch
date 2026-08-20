if (!localStorage.getItem("token")) {
    window.location.href = "login.html";
}

const sessionsGrid = document.getElementById("sessions_grid");

fetch("../../server/php/get_sessions.php", {
    headers: {
        "Authorization": "Bearer " + localStorage.getItem("token")
    }
})
.then((res) => res.json())
.then((sessions) => {
    if (sessions.error == "unauthorized") {
        window.location.href = "login.html";
        return;
    }

    if (sessions.length == 0) {
        sessionsGrid.innerHTML = `
            <div class="empty_card">
                <h3>No rooms yet</h3>
                <p>Upload your first room photo to get matched furniture.</p>
                <a href="upload.html" class="cta_button">Upload a Room</a>
            </div>
        `;
        return;
    }

    for (let i = 0; i < sessions.length; i++) {
        const s = sessions[i];
        const card = document.createElement("div");
        card.className = "result_card";

        const colors = s.dominant_colors.split(",");
        let swatches = "";
        for (let j = 0; j < colors.length; j++) {
            swatches += `<div class="swatch" style="background:${colors[j]}"></div>`;
        }

        const roomLabel = s.room_type.replace("_", " ");

        card.innerHTML = `
            <h3>${roomLabel}</h3>
            <p class="price_line">${s.style_pref} &mdash; ${s.budget} JOD &mdash; ${s.region}</p>
            <div id="color_swatches">${swatches}</div>
            <p class="explanation">${s.created_at}</p>
            <a href="results.html?session=${s.session_id}" class="buy_link">View Matches &rarr;</a>
        `;

        sessionsGrid.appendChild(card);
    }
})
.catch(() => {
    sessionsGrid.innerHTML = `
        <div class="empty_card">
            <h3>Something went wrong</h3>
            <p>We couldn't load your rooms right now.</p>
        </div>
    `;
});