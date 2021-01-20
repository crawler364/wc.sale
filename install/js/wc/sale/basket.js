class WCSaleBasket {
    basketHandlerClass;
    productId;
    action;
    quantity;

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
                this.processAction.bind(this)
            );

            BX.bindDelegate(
                document.body,
                'click',
                {
                    attribute: 'data-action-basket-item'
                },
                this.processAction.bind(this)
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

            basketContainerDom.nodes = {
                container: basketContainer,
                weight: BX.findChild(basketContainer, {
                    'attribute': {'data-basket-weight': ''}
                }, true, false),
                count: BX.findChild(basketContainer, {
                    'attribute': {'data-basket-count': ''}
                }, true, false),
                vat: BX.findChild(basketContainer, {
                    'attribute': {'data-basket-vat': ''}
                }, true, false),
                priceBase: BX.findChild(basketContainer, {
                    'attribute': {'data-basket-price-base': ''}
                }, true, false),
                discount: BX.findChild(basketContainer, {
                    'attribute': {'data-basket-discount': ''}
                }, true, false),
                price: BX.findChild(basketContainer, {
                    'attribute': {'data-basket-price': ''}
                }, true, false),
                empty: BX.findChild(basketContainer, {
                    'attribute': {'data-basket-empty': ''}
                }, true, false),
            }

            basketContainersDom[key] = basketContainerDom;
        });

        return basketContainersDom;
    }

    getBasketItemContainersDom(target) {
        let currentBasketItemContainer, basketItemContainers, basketItemContainersDom = [];

        currentBasketItemContainer = BX.findParent(target, {
            attribute: {'data-basket-item-container': ''}
        });

        this.productId = currentBasketItemContainer.getAttribute('data-basket-item-id');
        this.action = target.getAttribute('data-action-basket-item');
        this.quantity = BX.findChild(currentBasketItemContainer, {
            'attribute': {'data-action-basket-item': 'set'}
        }, true, false).value;

        basketItemContainers = BX.findChildren(document.body, {
            'attribute': {'data-basket-item-container': '', 'data-basket-item-id': this.productId}
        }, true);

        basketItemContainers.forEach((basketItemContainer, key) => {
            let basketItemContainerDom = {};

            basketItemContainerDom.propertys = {
                action: this.action,
                productId: this.productId,
                quantity: this.quantity,
            };

            basketItemContainerDom.nodes = {
                container: basketItemContainer,
                basketItem: BX.findChild(basketItemContainer, {
                    'attribute': {'data-basket-item': ''}
                }, true, false),
                input: BX.findChild(basketItemContainer, {
                    'attribute': {'data-action-basket-item': 'set'}
                }, true, false),
                priceSum: BX.findChild(basketItemContainer, {
                    'attribute': {'data-basket-item-price-sum': ''}
                }, true, false),
                priceBaseSum: BX.findChild(basketItemContainer, {
                    'attribute': {'data-basket-item-price-base-sum': ''}
                }, true, false),
                discountSum: BX.findChild(basketItemContainer, {
                    'attribute': {'data-basket-item-discount-sum': ''}
                }, true, false),
                restoreButton: BX.findChild(basketItemContainer, {
                    'attribute': {'data-basket-item-restore-button': ''}
                }, true, false),
            };

            basketItemContainersDom[key] = basketItemContainerDom;
        });

        return basketItemContainersDom;
    }

    setBasketContainersDom(basketContainersDom, basket) {
        basketContainersDom.forEach((basketContainerDom) => {
            if (basketContainerDom.nodes.weight) {
                BX.adjust(basketContainerDom.nodes.weight, {text: basket.info.weightFormatted});
            }
            if (basketContainerDom.nodes.count) {
                BX.adjust(basketContainerDom.nodes.count, {text: basket.info.count});
            }
            if (basketContainerDom.nodes.vat) {
                BX.adjust(basketContainerDom.nodes.vat, {text: basket.info.vatFormatted});
            }
            if (basketContainerDom.nodes.priceBase) {
                BX.adjust(basketContainerDom.nodes.priceBase, {text: basket.info.priceBaseFormatted});
            }
            if (basketContainerDom.nodes.discount) {
                BX.adjust(basketContainerDom.nodes.discount, {text: basket.info.discountFormatted});
            }
            if (basketContainerDom.nodes.price) {
                BX.adjust(basketContainerDom.nodes.price, {text: basket.info.priceFormatted});
            }

            if (basket.info.count > 0) {
                if (typeof UpdateBasketDom !== 'undefined' && typeof UpdateBasketDom.update === 'function') {
                    UpdateBasketDom.update(basketContainerDom, basket);
                }
            } else {
                if (typeof UpdateBasketDom !== 'undefined' && typeof UpdateBasketDom.delete === 'function') {
                    UpdateBasketDom.delete(basketContainerDom, basket);
                }
            }
        });
    }

    setBasketItemContainersDom(basketItemContainersDom, basketItem) {
        basketItemContainersDom.forEach((basketItemContainerDom) => {
            if (basketItemContainerDom.nodes.input) {
                basketItemContainerDom.nodes.input.value = basketItem.quantity;
            }
            if (basketItemContainerDom.nodes.priceSum) {
                BX.adjust(basketItemContainerDom.nodes.priceSum, {text: basketItem.priceSumFormatted});
            }
            if (basketItemContainerDom.nodes.priceBaseSum) {
                BX.adjust(basketItemContainerDom.nodes.priceBaseSum, {text: basketItem.priceBaseSumFormatted});
            }
            if (basketItemContainerDom.nodes.discountSum) {
                BX.adjust(basketItemContainerDom.nodes.discountSum, {text: basketItem.discountSumFormatted});
            }

            if (basketItem.quantity > 0) {
                if (typeof UpdateBasketItemDom !== 'undefined' && typeof UpdateBasketItemDom.update === 'function') {
                    UpdateBasketItemDom.update(basketItemContainerDom, basketItem);
                }
            } else {
                if (typeof UpdateBasketItemDom !== 'undefined' && typeof UpdateBasketItemDom.delete === 'function') {
                    UpdateBasketItemDom.delete(basketItemContainerDom, basketItem);
                }
            }
        });
    }

    processAction(e) {
        BX.PreventDefault(e);

        let basketContainersDom = this.getBasketContainersDom();
        let basketItemContainersDom = this.getBasketItemContainersDom(e.target);

        let data = {
            basketAction: this.action,
            product: {
                id: this.productId,
                quantity: this.action === 'set' ? this.quantity : '',
            },
            basketHandlerClass: this.basketHandlerClass
        };

        BX.ajax.runComponentAction('wc:basket', 'process', {
            mode: 'ajax',
            data: data
        }).then((response) => {
            console.log(response);
            let basket = response.data.basket;
            let basketItem = response.data.basketItem;
            this.setBasketContainersDom(basketContainersDom, basket);
            this.setBasketItemContainersDom(basketItemContainersDom, basketItem);
        }, function (response) {
            console.log(response);
            // todo обработка ошибок
        });
    }
}