if (!localStorage.getItem("token")) {
    window.location.href = "login.html";
}

const fileInput = document.getElementById("room_photo");
const previewArea = document.getElementById("preview_area");
const swatchArea = document.getElementById("color_swatches");
let extractedColors = [];

const rgbToHex = (r, g, b) => {
    const toHex = (n) => {
        let hex = n.toString(16);
        if (hex.length == 1) {
            hex = "0" + hex;
        }
        return hex;
    };
    return "#" + toHex(r) + toHex(g) + toHex(b);
};

const extractColors = (img) => {
    const canvas = document.createElement("canvas");
    const size = 100;
    canvas.width = size;
    canvas.height = size;
    const ctx = canvas.getContext("2d");
    ctx.drawImage(img, 0, 0, size, size);

    const data = ctx.getImageData(0, 0, size, size).data;
    const buckets = {};

    for (let i = 0; i < data.length; i += 16) {
        const r = Math.round(data[i] / 32) * 32;
        const g = Math.round(data[i + 1] / 32) * 32;
        const b = Math.round(data[i + 2] / 32) * 32;
        const key = r + "," + g + "," + b;

        if (buckets[key]) {
            buckets[key] = buckets[key] + 1;
        } else {
            buckets[key] = 1;
        }
    }

    const sorted = Object.keys(buckets).sort((a, b) => buckets[b] - buckets[a]);
    const topColors = sorted.slice(0, 5);
    const hexColors = [];

    for (let i = 0; i < topColors.length; i++) {
        const parts = topColors[i].split(",");
        const hex = rgbToHex(parseInt(parts[0]), parseInt(parts[1]), parseInt(parts[2]));
        hexColors.push(hex);
    }

    return hexColors;
};

const showSwatches = (colors) => {
    swatchArea.innerHTML = "";
    for (let i = 0; i < colors.length; i++) {
        const box = document.createElement("div");
        box.className = "swatch";
        box.style.backgroundColor = colors[i];
        swatchArea.appendChild(box);
    }
};

fileInput.addEventListener("change", () => {
    const file = fileInput.files[0];
    if (!file) {
        return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
        const img = new Image();
        img.onload = () => {
            previewArea.innerHTML = "";
            previewArea.appendChild(img);
            extractedColors = extractColors(img);
            showSwatches(extractedColors);
        };
        img.src = e.target.result;
        img.style.maxWidth = "100%";
    };
    reader.readAsDataURL(file);
});

const submitBtn = document.getElementById("submit_btn");

submitBtn.addEventListener("click", () => {
    const roomType = document.getElementById("room_type").value;
    const stylePref = document.getElementById("style_pref").value;
    const budget = document.getElementById("budget").value;
    const region = document.getElementById("region").value;

    if (budget == "" || budget <= 0) {
        alert("please enter a valid budget");
        return;
    }

    if (extractedColors.length == 0) {
        alert("please upload a room photo first");
        return;
    }

    const payload = {
        room_type: roomType,
        style_pref: stylePref,
        budget: budget,
        region: region,
        dominant_colors: extractedColors.join(",")
    };

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
});