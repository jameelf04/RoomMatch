if (!localStorage.getItem("token")) {
    window.location.href = "login.html";
}

const sessionsGrid = document.getElementById("sessions_grid");

const loadSessions = () => {
    sessionsGrid.innerHTML = "";

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
            const titleText = s.nickname != "" ? s.nickname : roomLabel;

            card.innerHTML = `
                <h3 class="room_title">${titleText}</h3>
                <p class="price_line">${s.style_pref} &mdash; ${s.budget} JOD &mdash; ${s.region}</p>
                <div id="color_swatches">${swatches}</div>
                <p class="explanation">${s.created_at}</p>
                <a href="results.html?session=${s.session_id}" class="buy_link">View Matches &rarr;</a>
                <div class="admin_actions">
                    <button class="chip rename_btn">Rename</button>
                    <button class="chip delete_btn">Delete</button>
                </div>
            `;

            const renameBtn = card.querySelector(".rename_btn");
            renameBtn.addEventListener("click", () => {
                const newName = prompt("Name this room:", s.nickname != "" ? s.nickname : roomLabel);
                if (newName == null) {
                    return;
                }

                fetch("../../server/php/rename_session.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Authorization": "Bearer " + localStorage.getItem("token")
                    },
                    body: JSON.stringify({ session_id: s.session_id, nickname: newName })
                })
                .then((res) => res.json())
                .then((data) => {
                    if (data.success) {
                        loadSessions();
                    }
                });
            });

            const deleteBtn = card.querySelector(".delete_btn");
            deleteBtn.addEventListener("click", () => {
                const confirmed = confirm("Delete this room and its matches?");
                if (!confirmed) {
                    return;
                }

                fetch("../../server/php/delete_session.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Authorization": "Bearer " + localStorage.getItem("token")
                    },
                    body: JSON.stringify({ session_id: s.session_id })
                })
                .then((res) => res.json())
                .then((data) => {
                    if (data.success) {
                        loadSessions();
                    }
                });
            });

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
};

loadSessions();