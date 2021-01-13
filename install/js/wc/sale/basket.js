class WCSaleBasket {
    constructor(params) {
        let actionObjects, basketAction, on, basketItemsContainers, basketTopDom, basketDom, basketItemDom,
            restoreButton;

        this.basketHandlerClass = params.basketHandlerClass;

        basketDom = this.getBasketDom(BX('wc-basket-top-container'));
        basketTopDom = this.getBasketDom(BX('wc-basket-container'));

        basketItemsContainers = BX.findChild(BX('wc-basket-items-container'), {'attribute': 'data-basket-item-id'}, true, true);

        if (basketItemsContainers) {
            basketItemsContainers.forEach((basketItemContainer) => {
                basketItemDom = this.getBasketItemDom(basketItemContainer);
                actionObjects = BX.findChild(basketItemContainer, {'attribute': 'data-action-basket-item'}, true, true);

                restoreButton = BX.findNextSibling(basketItemContainer, {'class': 'restore-button'});
                actionObjects.push(restoreButton);
                basketItemDom.restoreButton = restoreButton;

                actionObjects.forEach((actionObject) => {
                    basketAction = actionObject.getAttribute('data-action-basket-item');

                    if (basketAction == 'set') {
                        on = 'blur';
                    } else {
                        on = 'click';
                    }

                    BX.bind(actionObject, on, BX.delegate(this.basketActionHandler.bind(
                        this,
                        basketAction,
                        basketTopDom,
                        basketDom,
                        basketItemDom
                    )));
                });
            });
        }
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
        basketItemDom.productId = basketItemContainer.getAttribute('data-basket-item-id');
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
            BX.adjust(basketDom.weight, {text: basket.info.weightFormatted});
        }
        if (basketDom.count) {
            BX.adjust(basketDom.count, {text: basket.info.count});
        }
        if (basketDom.vat) {
            BX.adjust(basketDom.vat, {text: basket.info.vatFormatted});
        }
        if (basketDom.priceBase) {
            BX.adjust(basketDom.priceBase, {text: basket.info.priceBaseFormatted});
        }
        if (basketDom.discount) {
            BX.adjust(basketDom.discount, {text: basket.info.discountFormatted});
        }
        if (basketDom.price) {
            BX.adjust(basketDom.price, {text: basket.info.priceFormatted});
        }
    }

    setBasketItemDom(basketItem, basketItemDom) {
        if (basketItem.quantity > 0) {
            if (basketItemDom.input) {
                basketItemDom.input.value = basketItem.quantity;
            }
            if (basketItemDom.priceSum) {
                BX.adjust(basketItemDom.priceSum, {text: basketItem.priceSumFormatted});
            }
            if (basketItemDom.priceBaseSum) {
                BX.adjust(basketItemDom.priceBaseSum, {text: basketItem.priceBaseSumFormatted});
            }
            if (basketItemDom.discountSum) {
                BX.adjust(basketItemDom.discountSum, {text: basketItem.discountSumFormatted});
            }
            if (typeof UpdateTemplateDom.basketItemUpdate === 'function') {
                UpdateTemplateDom.basketItemRestore(basketItemDom);
            }
        } else {
            if (typeof UpdateTemplateDom.basketItemDelete === 'function') {
                UpdateTemplateDom.basketItemDelete(basketItemDom);
            }
        }
    }

    basketActionHandler(basketAction, basketTopDom, basketDom, basketItemDom) {
        let data = {
            basketAction: basketAction,
            product: {id: basketItemDom.productId},
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
            let basket = response.data.basket;
            let basketItem = response.data.basketItem;
            this.setBasketDom(basket, basketTopDom);
            this.setBasketDom(basket, basketDom);
            this.setBasketItemDom(basketItem, basketItemDom);
        }, function (response) {
            console.log(response);
            // todo обработка ошибок
        });
    }
}