class WCSaleBasket {
    constructor(params) {
        let actionObjects, basketAction, on, basketItems, basket, basketDom, basketItemDom;

        this.basketHandlerClass = params.basketHandlerClass;
        basket = BX('wc-basket');
        basketDom = this.getBasketDom(basket);
        basketItems = BX.findChild(basket, {'attribute': 'data-basket-item-id'}, true, true);

        basketItems.forEach((basketItem) => {
            basketItemDom = this.getBasketItemDom(basketItem);
            actionObjects = BX.findChild(basketItem, {'attribute': 'data-basket-item-action'}, true, true);

            actionObjects.forEach((actionObject) => {
                basketAction = actionObject.getAttribute('data-basket-item-action');

                if (basketAction == 'set') {
                    on = 'blur';
                } else {
                    on = 'click';
                }

                BX.bind(actionObject, on, BX.delegate(this.action.bind(this, basketAction, basketDom, basketItemDom)));
            });
        });
    }

    getBasketDom(basket) {
        let basketDom = {};

        basketDom.totalPrice = BX.findChild(basket, {
            'attribute': {'data-basket-price': ''}
        }, true, false);

        return basketDom;
    }

    getBasketItemDom(basketItem) {
        let basketItemDom = {};

        basketItemDom.ctn = basketItem;
        basketItemDom.id = basketItem.getAttribute('data-basket-item-id');
        basketItemDom.input = BX.findChild(basketItem, {
            'tag': 'input',
            'attribute': {'data-basket-item-action': 'set'}
        }, true, false);
        basketItemDom.price = BX.findChild(basketItem, {
            'attribute': {'data-basket-item-price': ''}
        }, true, false);
        basketItemDom.priceSum = BX.findChild(basketItem, {
            'attribute': {'data-basket-item-price-sum': ''}
        }, true, false);

        return basketItemDom;
    }

    action(basketAction, basketDom, basketItemDom) {
        let data = {
            basketAction: basketAction,
            product: {id: basketItemDom.id},
            basketHandlerClass: this.basketHandlerClass
        }

        if (basketAction == 'set') {
            data.product.quantity = basketItemDom.input.value;
        }

        BX.ajax.runComponentAction('wc:basket', 'process', {
            mode: 'class',
            data: data
        }).then(function (response) {
            console.log(response);

            let basketItem = response.data.basketItem;
            let basket = response.data.basket;

            // basketItemDom
            basketItemDom.input.value = basketItem.quantity;
            if (basketItem.quantity > 0) {
                BX.adjust(basketItemDom.priceSum, {html: basketItem.priceSumFormatted});
            } else {
                BX.remove(basketItemDom.ctn);
            }

            // basketDom
            BX.adjust(basketDom.totalPrice, {html: basket.info.totalPriceFormatted});
        }, function (response) {
            console.log(response);
        });
    }
}