class WCSaleBasket {
    constructor(params) {
        this.basketHandlerClass = params.basketHandlerClass;

        let productId, actionObjects, basketAction, param, on, basket, products;

        basket = BX('wc-basket');
        products = BX.findChild(basket, {
                'attribute': 'data-basket-product-id',
            },
            true,
            true,
        );

        products.forEach((product) => {
            productId = product.getAttribute('data-basket-product-id');

            actionObjects = BX.findChild(product, {
                    'attribute': 'data-basket-action',
                },
                true,
                true,
            );

            actionObjects.forEach((actionObject) => {
                basketAction = actionObject.getAttribute('data-basket-action');
                param = {basketAction: basketAction, productId: productId};

                if (basketAction == 'set') {
                    on = 'blur';
                } else {
                    on = 'click';
                }

                BX.bind(actionObject, on, BX.delegate(this.action.bind(this, param, actionObject)));
            });
        });
    }

    action(param, actionObject) {
        let data = {
            basketAction: param.basketAction,
            product: {id: param.productId},
            basketHandlerClass: this.basketHandlerClass
        }

        if (param.basketAction == 'set') {
            data.product.quantity = actionObject.value;
        }

        BX.ajax.runComponentAction('wc:basket', 'process', {
            mode: 'class',
            data: data
        }).then(function (response) {
            console.log(response);
        }, function (response) {
            console.log(response);
        });
    }
}