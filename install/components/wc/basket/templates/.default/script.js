class WCSaleBasket {
    constructor() {
        let productId, actionObjects, action, param, on, basket, products;

        basket = BX('basket');
        products = BX.findChild(basket, {
                'tag': 'div',
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
                action = actionObject.getAttribute('data-basket-action');

                if (action == 'set') {
                    on = 'blur';
                } else {
                    on = 'click';
                }

                param = {action: action, productId: productId};
                BX.bind(actionObject, on, BX.delegate(this.action.bind(this, param, actionObject)));
            });
        });
    }

    action(param, actionObject) {
        let data = {
            act: param.action,
            product: {id: param.productId}
        }

        if (param.action == 'set') {
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