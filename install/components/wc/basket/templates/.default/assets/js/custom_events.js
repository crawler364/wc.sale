BX.addCustomEvent('onBeforeBasketProcess', function (basketNode) {
    BX.showWait();
});

BX.addCustomEvent('onAfterBasketProcess', function (basketNode) {
    BX.closeWait();
});

BX.addCustomEvent('onBasketProcess', function (basketNode, response) {
    if (response.status === 'success') {
        let basket = response.data;
        let arBasketFields = basketNode.arBasketFields;
        let arBasketItems = basketNode.arBasketItems;

        // basketFields
        arBasketFields.forEach((obBasketFields) => {
            if (basket.fields.count > 0) {
                if (obBasketFields.nodes.empty) {
                    BX.addClass(obBasketFields.nodes.empty, 'hide');
                }
                if (obBasketFields.node) {
                    let tbody = BX.findChild(obBasketFields.node, {
                        'tag': 'tbody',
                    }, false, false);
                    BX.removeClass(tbody, 'hide');
                }
            } else {
                if (obBasketFields.nodes.empty) {
                    BX.removeClass(obBasketFields.nodes.empty, 'hide');
                }
                if (obBasketFields.node) {
                    let tbody = BX.findChild(obBasketFields.node, {
                        'tag': 'tbody',
                    }, false, false);
                    BX.addClass(tbody, 'hide');
                }
            }
        });

        // basketItems
        arBasketItems.forEach((obBasketItem) => {
            if (basket.item.quantity > 0) {
                if (obBasketItem.nodes.body) {
                    BX.removeClass(obBasketItem.nodes.body, 'disabled');
                }
                if (obBasketItem.nodes.restoreButton) {
                    BX.addClass(obBasketItem.nodes.restoreButton, 'hide');
                }
            } else {
                if (obBasketItem.nodes.body) {
                    BX.addClass(obBasketItem.nodes.body, 'disabled');
                }
                if (obBasketItem.nodes.restoreButton) {
                    BX.removeClass(obBasketItem.nodes.restoreButton, 'hide');
                }
            }
        });
    } else {
        response.errors.forEach((error) => {
            console.error(error);
        });
    }
});
