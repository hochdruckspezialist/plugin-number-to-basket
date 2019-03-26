function sendBasketRequest(successMessage, errorMessage) {

    const url = '/number_to_basket?number=' + document.getElementById('item-number').value + '&quantity=1';
    let xhr = new XMLHttpRequest();
    vueApp.$store.commit("setIsBasketLoading", true);

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                vueApp.$store.commit("setBasketItems", JSON.parse(xhr.response));
                document.dispatchEvent(new CustomEvent("showShopNotification", {
                    detail: {
                        type: "success",
                        message: successMessage
                    }
                }));
            }
            else {
                console.log("Error", xhr.statusText);
                document.dispatchEvent(new CustomEvent("showShopNotification", {
                    detail: {
                        type: "error",
                        message: errorMessage
                    }
                }));
            }
            vueApp.$store.commit("setIsBasketLoading", false);
        }
    }

    xhr.open("POST", url, true);
    xhr.send("");
}