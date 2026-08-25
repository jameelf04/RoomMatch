if (!localStorage.getItem("token")) {
    window.location.href = "login.html";
}

const favGrid = document.getElementById("favorites_grid");

fetch("../../server/php/get_favorites.php", {
    headers: {
        "Authorization": "Bearer " + localStorage.getItem("token")
    }
})
.then((res) => res.json())
.then((items) => {
    if (items.error == "unauthorized") {
        window.location.href = "login.html";
        return;
    }

    if (items.length == 0) {
        favGrid.innerHTML = `
            <div class="empty_card">
                <h3>No favorites yet</h3>
                <p>Heart items on your results to save them here.</p>
                <a href="myrooms.html" class="cta_button">View My Rooms</a>
            </div>
        `;
        return;
    }

    for (let i = 0; i < items.length; i++) {
        const item = items[i];
        const card = document.createElement("div");
        card.className = "result_card";

        let buyLink = "";
        if (item.purchase_url != "" && item.purchase_url != null) {
            buyLink = `<a href="${item.purchase_url}" target="_blank" class="buy_link">View &amp; Buy &rarr;</a>`;
        }

        card.innerHTML = `
            <img src="${item.image_url}" alt="${item.name}" class="card_img" />
            <button class="fav_btn faved">&#10084;</button>
            <h3>${item.name}</h3>
            <p class="price_line">${item.price} JOD &mdash; ${item.store_name}</p>
            ${buyLink}
        `;

        const favBtn = card.querySelector(".fav_btn");
                favBtn.addEventListener("click", () => {
            fetch("../../server/php/toggle_favorite.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": "Bearer " + localStorage.getItem("token")
                },
                body: JSON.stringify({ item_id: item.item_id })
            })
            .then((res) => res.json())
            .then(() => {
                showToast("Removed from favorites", "success");
                card.remove();
                if (favGrid.children.length == 0) {
                    favGrid.innerHTML = `
                        <div class="empty_card">
                            <h3>No favorites yet</h3>
                            <p>Heart items on your results to save them here.</p>
                            <a href="myrooms.html" class="cta_button">View My Rooms</a>
                        </div>
                    `;
                }
            });
        });

        favGrid.appendChild(card);
    }
})
.catch(() => {
    favGrid.innerHTML = `
        <div class="empty_card">
            <h3>Something went wrong</h3>
            <p>We couldn't load your favorites right now.</p>
        </div>
    `;
});