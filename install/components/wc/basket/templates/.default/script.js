class WCSaleBasket {
    constructor() {
        let productId, actionObjects, action, actionData, on, basket, products;

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
                actionData = {'productId': productId, 'action': action};

                if (action == 'set') {
                    on = 'blur';
                } else {
                    on = 'click';
                }

                BX.bind(actionObject, on, BX.delegate(this.action.bind(this, actionData), this));
            });
        });
    }

    action(param) {
        console.log(param);
    }
}