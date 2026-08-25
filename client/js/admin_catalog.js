if (!localStorage.getItem("token") || localStorage.getItem("is_admin") != "1") {
    window.location.href = "login.html";
}

const catalogGrid = document.getElementById("catalog_grid");
const formTitle = document.getElementById("form_title");
const formError = document.getElementById("form_error");
const saveBtn = document.getElementById("save_btn");
const cancelBtn = document.getElementById("cancel_btn");

const loadItems = () => {
    catalogGrid.innerHTML = "";

    fetch("../../server/php/admin_items.php", {
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

        for (let i = 0; i < items.length; i++) {
            const item = items[i];
            const card = document.createElement("div");
            card.className = "result_card";

            card.innerHTML = `
                <img src="${item.image_url}" alt="${item.name}" class="card_img" />
                <h3>${item.name}</h3>
                <p class="price_line">${item.category.replace("_", " ")} &mdash; ${item.style} &mdash; ${item.price} JOD</p>
                <p class="price_line">${item.store_name} &mdash; ${item.region}</p>
                <div class="admin_actions">
                    <button class="chip edit_btn">Edit</button>
                    <button class="chip delete_btn">Delete</button>
                </div>
            `;

            const editBtn = card.querySelector(".edit_btn");
            editBtn.addEventListener("click", () => {
                document.getElementById("item_id").value = item.item_id;
                document.getElementById("f_name").value = item.name;
                document.getElementById("f_room").value = item.room_type;
                document.getElementById("f_category").value = item.category;
                document.getElementById("f_style").value = item.style;
                document.getElementById("f_color").value = item.color_hex;
                document.getElementById("f_price").value = item.price;
                document.getElementById("f_region").value = item.region;
                document.getElementById("f_store").value = item.store_name;
                document.getElementById("f_image").value = item.image_url;
                document.getElementById("f_purchase").value = item.purchase_url;
                document.getElementById("f_area").value = item.min_room_area;

                formTitle.innerText = "Edit item";
                cancelBtn.style.display = "inline-block";
                window.scrollTo(0, 0);
            });

            const deleteBtn = card.querySelector(".delete_btn");
                        const deleteBtn = card.querySelector(".delete_btn");
            deleteBtn.addEventListener("click", () => {
                showConfirm("Delete this item?", "This removes it from the catalog and any favorites.", () => {
                    fetch("../../server/php/admin_delete_item.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Authorization": "Bearer " + localStorage.getItem("token")
                        },
                        body: JSON.stringify({ item_id: item.item_id })
                    })
                    .then((res) => res.json())
                    .then((data) => {
                        if (data.success) {
                            showToast("Item deleted", "success");
                            loadItems();
                        } else {
                            showToast("Something went wrong", "error");
                        }
                    });
                });
            });

            catalogGrid.appendChild(card);
        }
    });
};

const resetForm = () => {
    document.getElementById("item_id").value = "";
    document.getElementById("f_name").value = "";
    document.getElementById("f_price").value = "";
    document.getElementById("f_store").value = "";
    document.getElementById("f_image").value = "";
    document.getElementById("f_purchase").value = "";
    document.getElementById("f_area").value = "";
    formTitle.innerText = "Add new item";
    formError.innerText = "";
    cancelBtn.style.display = "none";
};

cancelBtn.addEventListener("click", () => {
    resetForm();
});

saveBtn.addEventListener("click", () => {
    const name = document.getElementById("f_name").value;
    const price = document.getElementById("f_price").value;
    const store = document.getElementById("f_store").value;

    formError.innerText = "";

    if (name == "" || price == "" || price <= 0 || store == "") {
        formError.innerText = "please fill in name, a valid price, and store";
        return;
    }

    const payload = {
        item_id: document.getElementById("item_id").value,
        name: name,
        room_type: document.getElementById("f_room").value,
        category: document.getElementById("f_category").value,
        style: document.getElementById("f_style").value,
        color_hex: document.getElementById("f_color").value,
        price: price,
        region: document.getElementById("f_region").value,
        store_name: store,
        image_url: document.getElementById("f_image").value,
        purchase_url: document.getElementById("f_purchase").value,
        min_room_area: document.getElementById("f_area").value
    };

    fetch("../../server/php/admin_save_item.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + localStorage.getItem("token")
        },
        body: JSON.stringify(payload)
    })
    .then((res) => res.json())
    .then((data) => {
        if (data.success) {
            showToast("Item saved", "success");
            resetForm();
            loadItems();
        } else {
            showToast("Something went wrong saving the item", "error");
        }
    })
    .catch(() => {
        formError.innerText = "something went wrong saving the item";
    });
});

loadItems();