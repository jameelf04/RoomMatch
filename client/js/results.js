if (!localStorage.getItem("token")) {
    window.location.href = "login.html";
}

const params = new URLSearchParams(window.location.search);
const sessionId = params.get("session");
const grid = document.getElementById("results_grid");
const summary = document.getElementById("result_summary");
const modal = document.getElementById("item_modal");
const modalClose = document.getElementById("modal_close");

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
    summary.innerText = "no session found, please upload a room first";
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
            summary.innerText = "something went wrong loading your results";
            return;
        }

        if (items.length == 0) {
            summary.innerText = "no matching furniture found for your budget and preferences";
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

            card.innerHTML = `
                <img src="${item.image_url}" alt="${item.name}" class="card_img" />
                <span class="match_badge">${matchPercent}% match</span>
                <h3>${item.name}</h3>
                <p class="price_line">${item.price} JOD &mdash; ${item.store_name}</p>
                <p class="explanation">${item.explanation}</p>
                ${buyLink}
            `;

            card.addEventListener("click", (e) => {
                if (e.target.tagName != "A") {
                    openModal(item);
                }
            });

            grid.appendChild(card);
        }
    })
    .catch(() => {
        summary.innerText = "something went wrong loading your results";
    });
}