class WCSaleBasket {
    constructor(params) {
        this.parameters = params.parameters;
        this.signedParameters = params.signedParameters;
    }

    init() {
        BX.ready(() => {
            BX.bindDelegate(
                document.body,
                'change',
                {tag: 'input', attribute: 'data-basket-item-action'},
                this.processAction.bind(this)
            );

            BX.bindDelegate(
                document.body,
                'click',
                function (el) {
                    let attr = el.getAttribute('data-basket-item-action');
                    return attr === 'plus' || attr === 'minus' || attr === 'delete'
                },
                this.processAction.bind(this)
            );
        });
    }

    processAction(e) {
        BX.PreventDefault(e);

        this.prepareProductData(e.target);
        let basketNode = {
            arBasketFields: this.getBasketFields(),
            arBasketItems: this.getBasketItems()
        }

        BX.onCustomEvent('onBeforeBasketProcess', [basketNode]);

        BX.ajax.runComponentAction('wc:basket', 'process', {
            mode: 'ajax',
            data: {
                product: {
                    id: this.productId,
                    quantity: this.quantity,
                    action: this.action,
                },
            },
            signedParameters: this.signedParameters,
            getParameters: {
                parameters: this.parameters
            },
        }).then((response) => {
            if (this.parameters.ORDER_MODE === 'Y') {
                BX.onCustomEvent('onBasketUpdate');
            }
            BX.onCustomEvent('onBasketProcess', [basketNode, response]);
            this.setBasketFields(basketNode.arBasketFields, response.data.fields);
            this.setBasketItems(basketNode.arBasketItems, response.data.item);
            BX.onCustomEvent('onAfterBasketProcess', [basketNode, response]);
        }, (response) => {
            BX.onCustomEvent('onBasketProcess', [basketNode, response]);
            BX.onCustomEvent('onAfterBasketProcess', [basketNode, response]);
        });
    }

    prepareProductData(target) {
        let basketItem = BX.findParent(target, {
            attribute: {'data-container': 'basket-item'}
        });

        this.productId = basketItem.getAttribute('data-basket-item-id');
        this.action = target.getAttribute('data-basket-item-action');
        if (this.action === 'set') {
            this.quantity = BX.findChild(basketItem, {
                'attribute': {'data-basket-item-action': 'set'}
            }, true, false).value;
        } else {
            delete this.quantity;
        }
    }

    getBasketFields() {
        let basketFieldsNodes, arBasketFields = [], obBasketFields = {};

        basketFieldsNodes = BX.findChildren(document.body, {
            'attribute': {'data-container': 'basket-fields'}
        }, true);

        basketFieldsNodes.forEach((basketFieldsNode, key) => {
            obBasketFields = {
                node: basketFieldsNode,
                nodes: {
                    weight: BX.findChild(basketFieldsNode, {
                        'attribute': {'data-container': 'basket-weight'}
                    }, true, false),
                    count: BX.findChild(basketFieldsNode, {
                        'attribute': {'data-container': 'basket-count'}
                    }, true, false),
                    vatSum: BX.findChild(basketFieldsNode, {
                        'attribute': {'data-container': 'basket-vat-sum'}
                    }, true, false),
                    basePrice: BX.findChild(basketFieldsNode, {
                        'attribute': {'data-container': 'basket-base-price'}
                    }, true, false),
                    discountPrice: BX.findChild(basketFieldsNode, {
                        'attribute': {'data-container': 'basket-discount-price'}
                    }, true, false),
                    price: BX.findChild(basketFieldsNode, {
                        'attribute': {'data-container': 'basket-price'}
                    }, true, false),
                    empty: BX.findChild(basketFieldsNode, {
                        'attribute': {'data-container': 'basket-empty'}
                    }, true, false),
                }
            };

            arBasketFields[key] = obBasketFields;
        });

        return arBasketFields;
    }

    getBasketItems() {
        let basketItemsNodes, arBasketItems = [], obBasketItem = {};

        basketItemsNodes = BX.findChildren(document.body, {
            'attribute': {'data-container': 'basket-item', 'data-basket-item-id': this.productId}
        }, true);

        basketItemsNodes.forEach((basketItemNode, key) => {
            obBasketItem = {
                properties: {
                    action: this.action,
                    productId: this.productId,
                    quantity: this.quantity,
                },
                node: basketItemNode,
                nodes: {
                    body: BX.findChild(basketItemNode, {
                        'attribute': {'data-container': 'basket-item-body'}
                    }, true, false),
                    restoreButton: BX.findChild(basketItemNode, {
                        'attribute': {'data-container': 'basket-item-restore-button'}
                    }, true, false),
                    input: BX.findChild(basketItemNode, {
                        'attribute': {'data-container': 'basket-item-input'}
                    }, true, false),
                    priceSum: BX.findChild(basketItemNode, {
                        'attribute': {'data-container': 'basket-item-price-sum'}
                    }, true, false),
                    basePriceSum: BX.findChild(basketItemNode, {
                        'attribute': {'data-container': 'basket-item-base-price-sum'}
                    }, true, false),
                    discountPriceSum: BX.findChild(basketItemNode, {
                        'attribute': {'data-container': 'basket-item-discount-price-sum'}
                    }, true, false),
                }
            };

            arBasketItems[key] = obBasketItem;
        });

        return arBasketItems;
    }

    setBasketFields(arBasketFields, fields) {
        arBasketFields.forEach((obBasketFields) => {
            if (obBasketFields.nodes.weight) {
                BX.adjust(obBasketFields.nodes.weight, {html: fields.weightFormatted});
            }
            if (obBasketFields.nodes.count) {
                BX.adjust(obBasketFields.nodes.count, {html: fields.count});
            }
            if (obBasketFields.nodes.vatSum) {
                BX.adjust(obBasketFields.nodes.vatSum, {html: fields.vatSumFormatted});
            }
            if (obBasketFields.nodes.basePrice) {
                BX.adjust(obBasketFields.nodes.basePrice, {html: fields.basePriceFormatted});
            }
            if (obBasketFields.nodes.discountPrice) {
                BX.adjust(obBasketFields.nodes.discountPrice, {html: fields.discountPriceFormatted});
            }
            if (obBasketFields.nodes.price) {
                BX.adjust(obBasketFields.nodes.price, {html: fields.priceFormatted});
            }
        });
    }

    setBasketItems(arBasketItems, item) {
        arBasketItems.forEach((obBasketItem) => {
            if (obBasketItem.nodes.input) {
                obBasketItem.nodes.input.value = item.quantity;
            }
            if (obBasketItem.nodes.priceSum) {
                BX.adjust(obBasketItem.nodes.priceSum, {html: item.priceSumFormatted});
            }
            if (obBasketItem.nodes.basePriceSum) {
                BX.adjust(obBasketItem.nodes.basePriceSum, {html: item.basePriceSumFormatted});
            }
            if (obBasketItem.nodes.discountPriceSum) {
                BX.adjust(obBasketItem.nodes.discountPriceSum, {html: item.discountPriceSumFormatted});
            }
        });
    }
}
