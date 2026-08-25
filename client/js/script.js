if (!localStorage.getItem("token")) {
    window.location.href = "login.html";
}

const fileInput = document.getElementById("room_photo");
const previewArea = document.getElementById("preview_area");
const swatchArea = document.getElementById("color_swatches");
const uploadError = document.getElementById("upload_error");
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

    uploadError.innerText = "";

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

const dropzone = document.getElementById("dropzone");

dropzone.addEventListener("dragover", (e) => {
    e.preventDefault();
    dropzone.classList.add("drag_active");
});

dropzone.addEventListener("dragleave", () => {
    dropzone.classList.remove("drag_active");
});

dropzone.addEventListener("drop", (e) => {
    e.preventDefault();
    dropzone.classList.remove("drag_active");

    const file = e.dataTransfer.files[0];
    if (!file) {
        return;
    }

    fileInput.files = e.dataTransfer.files;
    uploadError.innerText = "";

    const reader = new FileReader();
    reader.onload = (ev) => {
        const img = new Image();
        img.onload = () => {
            previewArea.innerHTML = "";
            previewArea.appendChild(img);
            extractedColors = extractColors(img);
            showSwatches(extractedColors);
        };
        img.src = ev.target.result;
        img.style.maxWidth = "100%";
    };
    reader.readAsDataURL(file);
});

const surpriseBtn = document.getElementById("surprise_btn");

surpriseBtn.addEventListener("click", () => {
    const styles = ["modern", "classic", "scandinavian", "boho", "industrial"];
    const regions = ["jordan", "gcc", "international"];
    const budgets = [200, 350, 500, 700, 900];

    const randomRegion = regions[Math.floor(Math.random() * regions.length)];
    const randomBudget = budgets[Math.floor(Math.random() * budgets.length)];

    document.getElementById("region").value = randomRegion;
    document.getElementById("budget").value = randomBudget;

    const checks = document.querySelectorAll(".style_check");
    for (let i = 0; i < checks.length; i++) {
        checks[i].checked = false;
    }

    const shuffled = styles.sort(() => Math.random() - 0.5);
    const pickCount = Math.random() < 0.5 ? 1 : 2;

    for (let i = 0; i < pickCount; i++) {
        for (let j = 0; j < checks.length; j++) {
            if (checks[j].value == shuffled[i]) {
                checks[j].checked = true;
            }
        }
    }

    uploadError.innerText = "";
});

const submitBtn = document.getElementById("submit_btn");

submitBtn.addEventListener("click", () => {
    const roomType = document.getElementById("room_type").value;
    const budget = document.getElementById("budget").value;
    const region = document.getElementById("region").value;
    const roomArea = document.getElementById("room_area").value;

    const checks = document.querySelectorAll(".style_check");
    const styles = [];
    for (let i = 0; i < checks.length; i++) {
        if (checks[i].checked) {
            styles.push(checks[i].value);
        }
    }

    uploadError.innerText = "";

    if (extractedColors.length == 0) {
        uploadError.innerText = "please upload a room photo first";
        return;
    }

    if (budget == "" || budget <= 0) {
        uploadError.innerText = "please enter a valid budget";
        return;
    }

    if (styles.length == 0) {
        uploadError.innerText = "please pick at least one style";
        return;
    }

    if (styles.length > 2) {
        uploadError.innerText = "please pick a maximum of 2 styles";
        return;
    }

    const payload = {
        room_type: roomType,
        style_pref: styles.join(","),
        budget: budget,
        region: region,
        room_area: roomArea,
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
            window.location.href = "login.html";
        } else {
            uploadError.innerText = "something went wrong saving your session";
        }
    })
        .catch(() => {
        showToast("Something went wrong, please try again", "error");
    });
});