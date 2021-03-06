class WCSaleBasketDomHandler {
    constructor(params) {
        this.basketContainersDom = params.basketContainersDom;
        this.basketItemContainersDom = params.basketItemContainersDom;
    }

    processStart() {
        BX.showWait();
    }

    processEnd() {
        BX.closeWait();
    }

    processResponse(response) {
        if (response.status === 'success') {
            this.basket = response.data.basket;
            this.basketItem = response.data.basketItem;

            // basket DOM
            this.basketContainersDom.forEach((basketContainerDom) => {
                if (this.basket.info.count > 0) {
                    if (basketContainerDom.nodes.empty) {
                        BX.addClass(basketContainerDom.nodes.empty, 'hide');
                    }
                    if (basketContainerDom.nodes.container) {
                        let tbody = BX.findChild(basketContainerDom.nodes.container, {
                            'tag': 'tbody',
                        }, false, false);
                        BX.removeClass(tbody, 'hide');
                    }
                } else {
                    if (basketContainerDom.nodes.empty) {
                        BX.removeClass(basketContainerDom.nodes.empty, 'hide');
                    }
                    if (basketContainerDom.nodes.container) {
                        let tbody = BX.findChild(basketContainerDom.nodes.container, {
                            'tag': 'tbody',
                        }, false, false);
                        BX.addClass(tbody, 'hide');
                    }
                }
            });

            // basketItem DOM
            this.basketItemContainersDom.forEach((basketItemContainerDom) => {
                if (this.basketItem.quantity > 0) {
                    if (basketItemContainerDom.nodes.basketItem) {
                        BX.removeClass(basketItemContainerDom.nodes.basketItem, 'disabled');
                    }
                    if (basketItemContainerDom.nodes.restoreButton) {
                        BX.addClass(basketItemContainerDom.nodes.restoreButton, 'hide');
                    }
                } else {
                    if (basketItemContainerDom.nodes.basketItem) {
                        BX.addClass(basketItemContainerDom.nodes.basketItem, 'disabled');
                    }
                    if (basketItemContainerDom.nodes.restoreButton) {
                        BX.removeClass(basketItemContainerDom.nodes.restoreButton, 'hide');
                    }
                }
            });
        } else if (response.status === 'error') {

        }
    }
}
