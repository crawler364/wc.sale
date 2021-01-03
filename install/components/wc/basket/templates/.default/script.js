class WCSaleBasket {
    constructor(params) {
        this.basketHandlerClass = params.basketHandlerClass;

        let actionObjects, basketAction, on, basket, products;

        basket = BX('wc-basket');
        products = BX.findChild(basket, {'attribute': 'data-basket-product-id'}, true, true);

        products.forEach((product) => {
            actionObjects = BX.findChild(product, {'attribute': 'data-basket-action'}, true, true);

            actionObjects.forEach((actionObject) => {
                basketAction = actionObject.getAttribute('data-basket-action');
                if (basketAction == 'set') {
                    on = 'blur';
                } else {
                    on = 'click';
                }

                BX.bind(actionObject, on, BX.delegate(this.action.bind(this, basketAction, product)));
            });
        });
    }

    action(basketAction, product) {
        let productId, $input;

        productId = product.getAttribute('data-basket-product-id');
        $input = BX.findChild(product, {
            'tag': 'input',
            'attribute': {'data-basket-action': 'set'}
        }, true, false);

        let data = {
            basketAction: basketAction,
            product: {id: productId},
            basketHandlerClass: this.basketHandlerClass
        }

        if (basketAction == 'set') {
            data.product.quantity = $input.value;
        }

        BX.ajax.runComponentAction('wc:basket', 'process', {
            mode: 'class',
            data: data
        }).then(function (response) {
            let item = response.data.item;
            let basket = response.data.basket;
            $input.value = item.quantity;
        }, function (response) {
            console.log(response);
        });
    }
}