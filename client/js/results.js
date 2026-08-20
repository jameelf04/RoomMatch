const params = new URLSearchParams(window.location.search);
const sessionId = params.get("session");
const grid = document.getElementById("results_grid");
const summary = document.getElementById("result_summary");

if (!sessionId) {
    summary.innerText = "no session found, please upload a room first";
} else {
    fetch("../../server/php/get_matches.php?session=" + sessionId)
    .then((res) => res.json())
    .then((items) => {
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
            const article = document.createElement("article");

            const matchPercent = Math.round(item.match_score * 100);

            article.innerHTML = `
                <header>
                    <h3>${item.name}</h3>
                </header>
                <p>${item.price} JOD &mdash; ${item.store_name}</p>
                <p>${matchPercent}% match</p>
                <p>${item.explanation}</p>
                <ul class="actions">
                    <li><a href="${item.purchase_url}" target="_blank" class="button">View & Buy</a></li>
                </ul>
            `;

            grid.appendChild(article);
        }
    })
    .catch(() => {
        summary.innerText = "something went wrong loading your results";
    });
}