class WCSaleBasket {
    constructor(params) {
        this.basketHandlerClass = params.basketHandlerClass;
    }

    init() {
        BX.ready(() => {
            BX.bindDelegate(
                document.body,
                'change',
                {
                    tag: 'input',
                    attribute: 'data-action-basket-item'
                },
                BX.delegate(this.processAction.bind(this))
            );

            BX.bindDelegate(
                document.body,
                'click',
                {
                    attribute: 'data-action-basket-item'
                },
                BX.delegate(this.processAction.bind(this))
            );
        });
    }

    getBasketContainersDom() {
        let basketContainers, basketContainersDom = [];

        basketContainers = BX.findChildren(document.body, {
            'attribute': {'data-wc-basket-container': ''}
        }, true);

        basketContainers.forEach((basketContainer, key) => {
            let basketContainerDom = {};

            basketContainerDom.weight = BX.findChild(basketContainer, {
                'attribute': {'data-basket-weight': ''}
            }, true, false);
            basketContainerDom.count = BX.findChild(basketContainer, {
                'attribute': {'data-basket-count': ''}
            }, true, false);
            basketContainerDom.vat = BX.findChild(basketContainer, {
                'attribute': {'data-basket-vat': ''}
            }, true, false);
            basketContainerDom.priceBase = BX.findChild(basketContainer, {
                'attribute': {'data-basket-price-base': ''}
            }, true, false);
            basketContainerDom.discount = BX.findChild(basketContainer, {
                'attribute': {'data-basket-discount': ''}
            }, true, false);
            basketContainerDom.price = BX.findChild(basketContainer, {
                'attribute': {'data-basket-price': ''}
            }, true, false);

            basketContainersDom[key] = basketContainerDom;
        });

        return basketContainersDom;
    }

    getBasketItemContainerDom(target) {
        let basketItemContainer;
        let basketItemContainerDom = {};

        basketItemContainer = BX.findParent(target, {
            attribute: {'data-basket-item-container': ''}
        });

        basketItemContainerDom.action = target.getAttribute('data-action-basket-item');
        basketItemContainerDom.container = basketItemContainer;
        basketItemContainerDom.productId = basketItemContainer.getAttribute('data-basket-item-id');
        basketItemContainerDom.basketItem = BX.findChild(basketItemContainer, {
            'attribute': {'data-basket-item': ''}
        }, true, false);
        basketItemContainerDom.input = BX.findChild(basketItemContainer, {
            'tag': 'input',
            'attribute': {'data-action-basket-item': 'set'}
        }, true, false);
        basketItemContainerDom.priceSum = BX.findChild(basketItemContainer, {
            'attribute': {'data-basket-item-price-sum': ''}
        }, true, false);
        basketItemContainerDom.priceBaseSum = BX.findChild(basketItemContainer, {
            'attribute': {'data-basket-item-price-base-sum': ''}
        }, true, false);
        basketItemContainerDom.discountSum = BX.findChild(basketItemContainer, {
            'attribute': {'data-basket-item-discount-sum': ''}
        }, true, false);
        basketItemContainerDom.restoreButton = BX.findChild(basketItemContainer, {
            'attribute': {'data-basket-item-restore-button': ''}
        }, true, false);

        return basketItemContainerDom;
    }

    setBasketContainersDom(basket, basketContainersDom) {
        basketContainersDom.forEach((basketContainerDom) => {
            if (basketContainerDom.weight) {
                BX.adjust(basketContainerDom.weight, {text: basket.info.weightFormatted});
            }
            if (basketContainerDom.count) {
                BX.adjust(basketContainerDom.count, {text: basket.info.count});
            }
            if (basketContainerDom.vat) {
                BX.adjust(basketContainerDom.vat, {text: basket.info.vatFormatted});
            }
            if (basketContainerDom.priceBase) {
                BX.adjust(basketContainerDom.priceBase, {text: basket.info.priceBaseFormatted});
            }
            if (basketContainerDom.discount) {
                BX.adjust(basketContainerDom.discount, {text: basket.info.discountFormatted});
            }
            if (basketContainerDom.price) {
                BX.adjust(basketContainerDom.price, {text: basket.info.priceFormatted});
            }

            if (basket.info.count > 0) {
                if (typeof UpdateBasketDom !== 'undefined' && typeof UpdateBasketDom.update === 'function') {
                    UpdateBasketDom.update(basketContainerDom);
                }
            } else {
                if (typeof UpdateBasketDom !== 'undefined' && typeof UpdateBasketDom.delete === 'function') {
                    UpdateBasketItemDom.delete(basketContainerDom);
                }
            }
        });
    }

    setBasketItemContainerDom(basketItem, basketItemContainerDom) {
        if (basketItemContainerDom.input) {
            basketItemContainerDom.input.value = basketItem.quantity;
        }
        if (basketItemContainerDom.priceSum) {
            BX.adjust(basketItemContainerDom.priceSum, {text: basketItem.priceSumFormatted});
        }
        if (basketItemContainerDom.priceBaseSum) {
            BX.adjust(basketItemContainerDom.priceBaseSum, {text: basketItem.priceBaseSumFormatted});
        }
        if (basketItemContainerDom.discountSum) {
            BX.adjust(basketItemContainerDom.discountSum, {text: basketItem.discountSumFormatted});
        }

        if (basketItem.quantity > 0) {
            if (typeof UpdateBasketItemDom !== 'undefined' && typeof UpdateBasketItemDom.update === 'function') {
                UpdateBasketItemDom.update(basketItemContainerDom);
            }
            if (typeof UpdateBasketProductDom !== 'undefined' && typeof UpdateBasketProductDom.update === 'function') {
                UpdateBasketProductDom.update(basketItemContainerDom);
            }
        } else {
            if (typeof UpdateBasketItemDom !== 'undefined' && typeof UpdateBasketItemDom.delete === 'function') {
                UpdateBasketItemDom.delete(basketItemContainerDom);
            }
            if (typeof UpdateBasketProductDom !== 'undefined' && typeof UpdateBasketProductDom.delete === 'function') {
                UpdateBasketProductDom.delete(basketItemContainerDom);
            }
        }
    }

    processAction(e) {
        BX.PreventDefault(e);

        let basketContainersDom = this.getBasketContainersDom();
        let basketItemContainerDom = this.getBasketItemContainerDom(e.target);

        let data = {
            basketAction: basketItemContainerDom.action,
            product: {
                id: basketItemContainerDom.productId
            },
            basketHandlerClass: this.basketHandlerClass
        }

        if (data.basketAction == 'set') {
            data.product.quantity = basketItemContainerDom.input.value;
        }

        BX.ajax.runComponentAction('wc:basket', 'process', {
            mode: 'ajax',
            data: data
        }).then((response) => {
            console.log(response);
            let basket = response.data.basket;
            let basketItem = response.data.basketItem;
            this.setBasketContainersDom(basket, basketContainersDom);
            this.setBasketItemContainerDom(basketItem, basketItemContainerDom);
        }, function (response) {
            console.log(response);
            // todo обработка ошибок
        });
    }
}