const showToast = (message, type) => {
    let container = document.getElementById("toast_container");
    if (!container) {
        container = document.createElement("div");
        container.id = "toast_container";
        document.body.appendChild(container);
    }

    const toast = document.createElement("div");
    toast.className = "toast " + type;
    toast.innerText = message;
    container.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3000);
};

const showConfirm = (title, message, onConfirm) => {
    let overlay = document.getElementById("confirm_overlay");
    if (!overlay) {
        overlay = document.createElement("div");
        overlay.className = "confirm_overlay";
        overlay.id = "confirm_overlay";
        overlay.innerHTML = `
            <div class="confirm_box">
                <h3 id="confirm_title"></h3>
                <p id="confirm_message"></p>
                <div class="confirm_actions">
                    <button class="chip" id="confirm_cancel">Cancel</button>
                    <button class="cta_button" id="confirm_ok">Confirm</button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);
    }

    document.getElementById("confirm_title").innerText = title;
    document.getElementById("confirm_message").innerText = message;
    overlay.classList.add("open");

    const okBtn = document.getElementById("confirm_ok");
    const cancelBtn = document.getElementById("confirm_cancel");

    const cleanUp = () => {
        overlay.classList.remove("open");
        okBtn.replaceWith(okBtn.cloneNode(true));
        cancelBtn.replaceWith(cancelBtn.cloneNode(true));
    };

    document.getElementById("confirm_ok").addEventListener("click", () => {
        cleanUp();
        onConfirm();
    });

    document.getElementById("confirm_cancel").addEventListener("click", () => {
        cleanUp();
    });
};

const showPrompt = (title, defaultValue, onSubmit) => {
    let overlay = document.getElementById("prompt_overlay");
    if (!overlay) {
        overlay = document.createElement("div");
        overlay.className = "confirm_overlay";
        overlay.id = "prompt_overlay";
        overlay.innerHTML = `
            <div class="confirm_box">
                <h3 id="prompt_title"></h3>
                <input type="text" id="prompt_input" />
                <div class="confirm_actions">
                    <button class="chip" id="prompt_cancel">Cancel</button>
                    <button class="cta_button" id="prompt_ok">Save</button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);
    }

    document.getElementById("prompt_title").innerText = title;
    const input = document.getElementById("prompt_input");
    input.value = defaultValue;
    overlay.classList.add("open");

    const okBtn = document.getElementById("prompt_ok");
    const cancelBtn = document.getElementById("prompt_cancel");

    const cleanUp = () => {
        overlay.classList.remove("open");
        okBtn.replaceWith(okBtn.cloneNode(true));
        cancelBtn.replaceWith(cancelBtn.cloneNode(true));
    };

    document.getElementById("prompt_ok").addEventListener("click", () => {
        const val = input.value;
        cleanUp();
        onSubmit(val);
    });

    document.getElementById("prompt_cancel").addEventListener("click", () => {
        cleanUp();
    });
};