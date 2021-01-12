class WCSaleBasket {
    constructor(params) {
        let actionObjects, basketAction, on, basketItemsContainers, basketContainer, basketItemsContainer, basketDom, basketItemDom;

        this.basketHandlerClass = params.basketHandlerClass;
        basketContainer = BX('wc-basket-container');
        basketItemsContainer = BX('wc-basket-items-container');
        basketDom = this.getBasketDom(basketContainer);
        basketItemsContainers = BX.findChild(basketItemsContainer, {'attribute': 'data-basket-item-id'}, true, true);

        basketItemsContainers.forEach((basketItemContainer) => {
            basketItemDom = this.getBasketItemDom(basketItemContainer);
            actionObjects = BX.findChild(basketItemContainer, {'attribute': 'data-action-basket-item'}, true, true);

            actionObjects.forEach((actionObject) => {
                basketAction = actionObject.getAttribute('data-action-basket-item');

                if (basketAction == 'set') {
                    on = 'blur';
                } else {
                    on = 'click';
                }

                BX.bind(actionObject, on, BX.delegate(this.action.bind(this, basketAction, basketDom, basketItemDom)));
            });
        });
    }

    getBasketDom(basketContainer) {
        let basketDom = {};

        basketDom.weight = BX.findChild(basketContainer, {
            'attribute': {'data-basket-weight': ''}
        }, true, false);
        basketDom.count = BX.findChild(basketContainer, {
            'attribute': {'data-basket-count': ''}
        }, true, false);
        basketDom.vat = BX.findChild(basketContainer, {
            'attribute': {'data-basket-vat': ''}
        }, true, false);
        basketDom.priceBase = BX.findChild(basketContainer, {
            'attribute': {'data-basket-price-base': ''}
        }, true, false);
        basketDom.discount = BX.findChild(basketContainer, {
            'attribute': {'data-basket-discount': ''}
        }, true, false);
        basketDom.price = BX.findChild(basketContainer, {
            'attribute': {'data-basket-price': ''}
        }, true, false);

        return basketDom;
    }

    getBasketItemDom(basketItemContainer) {
        let basketItemDom = {};

        basketItemDom.container = basketItemContainer;
        basketItemDom.id = basketItemContainer.getAttribute('data-basket-item-id');
        basketItemDom.input = BX.findChild(basketItemContainer, {
            'tag': 'input',
            'attribute': {'data-action-basket-item': 'set'}
        }, true, false);
        basketItemDom.priceSum = BX.findChild(basketItemContainer, {
            'attribute': {'data-basket-item-price-sum': ''}
        }, true, false);
        basketItemDom.priceBaseSum = BX.findChild(basketItemContainer, {
            'attribute': {'data-basket-item-price-base-sum': ''}
        }, true, false);
        basketItemDom.discountSum = BX.findChild(basketItemContainer, {
            'attribute': {'data-basket-item-discount-sum': ''}
        }, true, false);

        return basketItemDom;
    }

    setBasketDom(basket, basketDom) {
        if (basketDom.weight) {
            BX.adjust(basketDom.weight, {html: basket.info.weightFormatted});
        }
        if (basketDom.count) {
            BX.adjust(basketDom.count, {html: basket.info.count});
        }
        if (basketDom.vat) {
            BX.adjust(basketDom.vat, {html: basket.info.vatFormatted});
        }
        if (basketDom.priceBase) {
            BX.adjust(basketDom.priceBase, {html: basket.info.priceBaseFormatted});
        }
        if (basketDom.discount) {
            BX.adjust(basketDom.discount, {html: basket.info.discountFormatted});
        }
        if (basketDom.price) {
            BX.adjust(basketDom.price, {html: basket.info.priceFormatted});
        }
    }

    setBasketItemDom(basketItem, basketItemDom) {
        if (basketItem.quantity > 0) {
            if (basketItemDom.input) {
                basketItemDom.input.value = basketItem.quantity;
            }
            if (basketItemDom.priceSum) {
                BX.adjust(basketItemDom.priceSum, {html: basketItem.priceSumFormatted});
            }
            if (basketItemDom.priceBaseSum) {
                BX.adjust(basketItemDom.priceBaseSum, {html: basketItem.priceBaseSumFormatted});
            }
            if (basketItemDom.discountSum) {
                BX.adjust(basketItemDom.discountSum, {html: basketItem.discountSumFormatted});
            }
        } else {
            BX.remove(basketItemDom.container);
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
            // todo обработка ошибок
        });
    }
}