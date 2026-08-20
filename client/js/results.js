if (!localStorage.getItem("token")) {
    window.location.href = "login.html";
}

const params = new URLSearchParams(window.location.search);
const sessionId = params.get("session");
const grid = document.getElementById("results_grid");
const summary = document.getElementById("result_summary");
const modal = document.getElementById("item_modal");
const modalClose = document.getElementById("modal_close");

let favIds = [];

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

const openModal = (item) => {
    document.getElementById("modal_img").src = item.image_url;
    document.getElementById("modal_name").innerText = item.name;
    document.getElementById("modal_price").innerText = item.price + " JOD — " + item.store_name + " — " + item.region;
    document.getElementById("modal_explanation").innerText = item.explanation;

    document.getElementById("bar_style").style.width = (item.style_score * 100) + "%";
    document.getElementById("bar_color").style.width = (item.color_score * 100) + "%";
    document.getElementById("bar_price").style.width = (item.price_score * 100) + "%";

    document.getElementById("val_style").innerText = Math.round(item.style_score * 100) + "%";
    document.getElementById("val_color").innerText = Math.round(item.color_score * 100) + "%";
    document.getElementById("val_price").innerText = Math.round(item.price_score * 100) + "%";

    const buyBtn = document.getElementById("modal_buy");
    if (item.purchase_url != "" && item.purchase_url != null) {
        buyBtn.href = item.purchase_url;
        buyBtn.style.display = "inline-block";
    } else {
        buyBtn.style.display = "none";
    }

    modal.classList.add("open");
};

modalClose.addEventListener("click", () => {
    modal.classList.remove("open");
});

modal.addEventListener("click", (e) => {
    if (e.target == modal) {
        modal.classList.remove("open");
    }
});

if (!sessionId) {
    summary.innerText = "";
    grid.innerHTML = `
        <div class="empty_card">
            <h3>No session found</h3>
            <p>Upload a room photo first to get your matches.</p>
            <a href="upload.html" class="cta_button">Go to Upload</a>
        </div>
    `;
} else {
    fetch("../../server/php/get_matches.php?session=" + sessionId, {
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

        if (items.error) {
            summary.innerText = "";
            grid.innerHTML = `
                <div class="empty_card">
                    <h3>Something went wrong</h3>
                    <p>We couldn't load your results. Try uploading your room again.</p>
                    <a href="upload.html" class="cta_button">Back to Upload</a>
                </div>
            `;
            return;
        }

        if (items.length == 0) {
            summary.innerText = "";
            grid.innerHTML = `
                <div class="empty_card">
                    <h3>No matches found</h3>
                    <p>Nothing in the catalog fits your current budget and preferences. Try raising your budget or picking a different style.</p>
                    <a href="upload.html" class="cta_button">Try Again</a>
                </div>
            `;
            return;
        }

        summary.innerText = items.length + " items matched to your room";

        for (let i = 0; i < items.length; i++) {
            const item = items[i];
            const card = document.createElement("div");
            card.className = "result_card";
            card.style.cursor = "pointer";

            const matchPercent = Math.round(item.match_score * 100);

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
                <span class="match_badge">${matchPercent}% match</span>
                <h3>${item.name}</h3>
                <p class="price_line">${item.price} JOD &mdash; ${item.store_name}</p>
                <p class="explanation">${item.explanation}</p>
                ${buyLink}
            `;

            const favBtn = card.querySelector(".fav_btn");
            favBtn.addEventListener("click", (e) => {
                e.stopPropagation();
                toggleFavorite(item.item_id, favBtn);
            });

            card.addEventListener("click", (e) => {
                if (e.target.tagName != "A" && e.target.tagName != "BUTTON") {
                    openModal(item);
                }
            });

            grid.appendChild(card);
        }
    })
    .catch(() => {
        summary.innerText = "";
        grid.innerHTML = `
            <div class="empty_card">
                <h3>Something went wrong</h3>
                <p>We couldn't load your results. Try uploading your room again.</p>
                <a href="upload.html" class="cta_button">Back to Upload</a>
            </div>
        `;
    });
}

const bundleGrid = document.getElementById("bundle_grid");
const budgetBar = document.getElementById("budget_bar");

if (sessionId) {
    fetch("../../server/php/get_bundle.php?session=" + sessionId, {
        headers: {
            "Authorization": "Bearer " + localStorage.getItem("token")
        }
    })
    .then((res) => res.json())
    .then((data) => {
        if (data.error || data.items.length == 0) {
            document.querySelector(".bundle_section").style.display = "none";
            return;
        }

        const usedPercent = Math.round((data.total / data.budget) * 100);

        budgetBar.innerHTML = `
            <div class="budget_text">
                <span>Bundle total: <strong>${data.total} JOD</strong></span>
                <span>Budget: ${data.budget} JOD</span>
                <span>Remaining: ${data.remaining} JOD</span>
            </div>
            <div class="budget_track"><div class="budget_fill" style="width:${usedPercent}%"></div></div>
        `;

        for (let i = 0; i < data.items.length; i++) {
            const item = data.items[i];
            const card = document.createElement("div");
            card.className = "result_card";

            const catLabel = item.category.replace("_", " ");

            let buyLink = "";
            if (item.purchase_url != "" && item.purchase_url != null) {
                buyLink = `<a href="${item.purchase_url}" target="_blank" class="buy_link">View &amp; Buy &rarr;</a>`;
            }

            card.innerHTML = `
                <img src="${item.image_url}" alt="${item.name}" class="card_img" />
                <span class="match_badge">${catLabel}</span>
                <h3>${item.name}</h3>
                <p class="price_line">${item.price} JOD &mdash; ${item.store_name}</p>
                ${buyLink}
            `;

            bundleGrid.appendChild(card);
        }
    })
    .catch(() => {
        document.querySelector(".bundle_section").style.display = "none";
    });
}