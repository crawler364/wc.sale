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

        basketDom.weight = BX.findChild(basket, {
            'attribute': {'data-basket-weight': ''}
        }, true, false);
        basketDom.count = BX.findChild(basket, {
            'attribute': {'data-basket-count': ''}
        }, true, false);
        basketDom.vat = BX.findChild(basket, {
            'attribute': {'data-basket-vat': ''}
        }, true, false);
        basketDom.priceBase = BX.findChild(basket, {
            'attribute': {'data-basket-price-base': ''}
        }, true, false);
        basketDom.discount = BX.findChild(basket, {
            'attribute': {'data-basket-discount': ''}
        }, true, false);
        basketDom.price = BX.findChild(basket, {
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
        basketItemDom.priceSum = BX.findChild(basketItem, {
            'attribute': {'data-basket-item-price-sum': ''}
        }, true, false);
        basketItemDom.priceBaseSum = BX.findChild(basketItem, {
            'attribute': {'data-basket-item-price-base-sum': ''}
        }, true, false);
        basketItemDom.discountSum = BX.findChild(basketItem, {
            'attribute': {'data-basket-item-discount-sum': ''}
        }, true, false);

        return basketItemDom;
    }

    setBasketDom(basket, basketDom) {
        BX.adjust(basketDom.weight, {html: basket.info.weightFormatted});
        BX.adjust(basketDom.count, {html: basket.info.count});
        BX.adjust(basketDom.vat, {html: basket.info.vatFormatted});
        BX.adjust(basketDom.priceBase, {html: basket.info.priceBaseFormatted});
        BX.adjust(basketDom.discount, {html: basket.info.discountFormatted});
        BX.adjust(basketDom.price, {html: basket.info.priceFormatted});
    }

    setBasketItemDom(basketItem, basketItemDom) {
        if (basketItem.quantity > 0) {
            basketItemDom.input.value = basketItem.quantity;
            BX.adjust(basketItemDom.priceSum, {html: basketItem.priceSumFormatted});
            if (basketItemDom.priceBaseSum) {
                BX.adjust(basketItemDom.priceBaseSum, {html: basketItem.priceBaseSumFormatted});
            }
            if (basketItemDom.discountSum) {
                BX.adjust(basketItemDom.discountSum, {html: basketItem.discountSumFormatted});
            }

        } else {
            BX.remove(basketItemDom.ctn);
        }
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
        }).then((response) => {
            console.log(response);
            let basketItem = response.data.basketItem;
            let basket = response.data.basket;
            this.setBasketItemDom(basketItem, basketItemDom);
            this.setBasketDom(basket, basketDom)
        }, function (response) {
            console.log(response);
        });
    }
}