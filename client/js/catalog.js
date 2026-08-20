if (!localStorage.getItem("token")) {
    window.location.href = "login.html";
}

const catalogGrid = document.getElementById("catalog_grid");
const chipBox = document.getElementById("filter_chips");
const sortSelect = document.getElementById("sort_select");

let allItems = [];
let favIds = [];
let activeRoom = "all";

fetch("../../server/php/get_favorites.php", {
    headers: {
        "Authorization": "Bearer " + localStorage.getItem("token")
    }
})
.then((res) => res.json())
.then((favs) => {
    if (!favs.error) {
        for (let i = 0; i < favs.length; i++) {
            favIds.push(favs[i].item_id);
        }
    }
});

const toggleFavorite = (itemId, btn) => {
    fetch("../../server/php/toggle_favorite.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + localStorage.getItem("token")
        },
        body: JSON.stringify({ item_id: itemId })
    })
    .then((res) => res.json())
    .then((data) => {
        if (data.favorited) {
            btn.classList.add("faved");
            btn.innerHTML = "&#10084;";
        } else {
            btn.classList.remove("faved");
            btn.innerHTML = "&#9825;";
        }
    });
};

const renderCatalog = () => {
    catalogGrid.innerHTML = "";

    let items = [];
    for (let i = 0; i < allItems.length; i++) {
        if (activeRoom == "all" || allItems[i].room_type == activeRoom) {
            items.push(allItems[i]);
        }
    }

    const sortVal = sortSelect.value;
    items.sort((a, b) => {
        if (sortVal == "price_low") {
            return a.price - b.price;
        }
        if (sortVal == "price_high") {
            return b.price - a.price;
        }
        if (a.name < b.name) {
            return -1;
        }
        return 1;
    });

    for (let i = 0; i < items.length; i++) {
        const item = items[i];
        const card = document.createElement("div");
        card.className = "result_card";

        const catLabel = item.category.replace("_", " ");

        let buyLink = "";
        if (item.purchase_url != "" && item.purchase_url != null) {
            buyLink = `<a href="${item.purchase_url}" target="_blank" class="buy_link">View &amp; Buy &rarr;</a>`;
        }

        const isFaved = favIds.includes(item.item_id);
        const heartChar = isFaved ? "&#10084;" : "&#9825;";
        const heartClass = isFaved ? "fav_btn faved" : "fav_btn";

        card.innerHTML = `
            <img src="${item.image_url}" alt="${item.name}" class="card_img" />
            <button class="${heartClass}">${heartChar}</button>
            <span class="match_badge">${catLabel}</span>
            <h3>${item.name}</h3>
            <p class="price_line">${item.price} JOD &mdash; ${item.store_name} &mdash; ${item.style}</p>
            ${buyLink}
        `;

        const favBtn = card.querySelector(".fav_btn");
        favBtn.addEventListener("click", () => {
            toggleFavorite(item.item_id, favBtn);
        });

        catalogGrid.appendChild(card);
    }
};

chipBox.innerHTML = `
    <button class="chip active" data-room="all">All</button>
    <button class="chip" data-room="living_room">Living Room</button>
    <button class="chip" data-room="bedroom">Bedroom</button>
`;

const chips = chipBox.querySelectorAll(".chip");
for (let i = 0; i < chips.length; i++) {
    chips[i].addEventListener("click", () => {
        for (let j = 0; j < chips.length; j++) {
            chips[j].classList.remove("active");
        }
        chips[i].classList.add("active");
        activeRoom = chips[i].dataset.room;
        renderCatalog();
    });
}

sortSelect.addEventListener("change", () => {
    renderCatalog();
});

fetch("../../server/php/get_catalog.php", {
    headers: {
        "Authorization": "Bearer " + localStorage.getItem("token")
    }
})
.then((res) => res.json())
.then((items) => {
    if (items.error) {
        window.location.href = "login.html";
        return;
    }
    allItems = items;
    renderCatalog();
})
.catch(() => {
    catalogGrid.innerHTML = `
        <div class="empty_card">
            <h3>Something went wrong</h3>
            <p>We couldn't load the catalog right now.</p>
        </div>
    `;
});