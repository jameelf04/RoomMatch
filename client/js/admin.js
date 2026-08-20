if (!localStorage.getItem("token") || localStorage.getItem("is_admin") != "1") {
    window.location.href = "login.html";
}

const statsGrid = document.getElementById("stats_grid");
const byRoomBox = document.getElementById("by_room");
const byStyleBox = document.getElementById("by_style");
const topFavsBox = document.getElementById("top_favs");

fetch("../../server/php/admin_stats.php", {
    headers: {
        "Authorization": "Bearer " + localStorage.getItem("token")
    }
})
.then((res) => res.json())
.then((stats) => {
    if (stats.error) {
        window.location.href = "login.html";
        return;
    }

    statsGrid.innerHTML = `
        <div class="stat_card"><span class="stat_num">${stats.users}</span><span class="stat_label">Users</span></div>
        <div class="stat_card"><span class="stat_num">${stats.sessions}</span><span class="stat_label">Room Sessions</span></div>
        <div class="stat_card"><span class="stat_num">${stats.items}</span><span class="stat_label">Catalog Items</span></div>
        <div class="stat_card"><span class="stat_num">${stats.favorites}</span><span class="stat_label">Favorites</span></div>
    `;

    let roomHtml = "";
    for (let i = 0; i < stats.by_room.length; i++) {
        const r = stats.by_room[i];
        roomHtml += `<div class="stat_row"><span>${r.room_type.replace("_", " ")}</span><strong>${r.c}</strong></div>`;
    }
    byRoomBox.innerHTML = roomHtml == "" ? "<p>No sessions yet.</p>" : roomHtml;

    let styleHtml = "";
    for (let i = 0; i < stats.by_style.length; i++) {
        const s = stats.by_style[i];
        styleHtml += `<div class="stat_row"><span>${s.style_pref}</span><strong>${s.c}</strong></div>`;
    }
    byStyleBox.innerHTML = styleHtml == "" ? "<p>No sessions yet.</p>" : styleHtml;

    let favHtml = "";
    for (let i = 0; i < stats.top_favorites.length; i++) {
        const f = stats.top_favorites[i];
        favHtml += `<div class="stat_row"><span>${f.name}</span><strong>${f.c}</strong></div>`;
    }
    topFavsBox.innerHTML = favHtml == "" ? "<p>No favorites yet.</p>" : favHtml;
})
.catch(() => {
    statsGrid.innerHTML = "<p>something went wrong loading stats</p>";
});