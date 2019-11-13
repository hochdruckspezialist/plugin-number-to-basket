function sendBasketRequest(successMessage, errorMessage) {

    var url = '/number_to_basket?number=' + document.getElementById('item-number').value + '&quantity=1';
    var xhr = new XMLHttpRequest();
    vueApp.$store.commit("setIsBasketLoading", true);

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.response);

                vueApp.$store.commit("setBasketItems", response.basketItems);
                vueApp.$store.commit("setBasket", response.basket);
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
    };

    xhr.open("POST", url, true);
    xhr.send("");
}
