class UpdateBasketItemDom {
    static update(dom, basketItem) {
        if (dom.nodes.basketItem) {
            BX.removeClass(dom.nodes.basketItem, 'disabled');
        }
        if (dom.nodes.restoreButton) {
            BX.addClass(dom.nodes.restoreButton, 'hide');
        }
    }

    static delete(dom, basketItem) {
        if (dom.nodes.basketItem) {
            BX.addClass(dom.nodes.basketItem, 'disabled');
        }
        if (dom.nodes.restoreButton) {
            BX.removeClass(dom.nodes.restoreButton, 'hide');
        }
    }
}

class UpdateBasketDom {
    static update(dom, basket) {
        if (dom.nodes.empty) {
            BX.addClass(dom.nodes.empty, 'hide');
        }
        if (dom.nodes.container) {
            let tbody = BX.findChild(dom.nodes.container, {
                'tag': 'tbody',
            }, false, false);
            BX.removeClass(tbody, 'hide');
        }
    }

    static delete(dom, basket) {
        if (dom.nodes.empty) {
            BX.removeClass(dom.nodes.empty, 'hide');
        }
        if (dom.nodes.container) {
            let tbody = BX.findChild(dom.nodes.container, {
                'tag': 'tbody',
            }, false, false);
            BX.addClass(tbody, 'hide');
        }
    }
}

class ResponseHandler {
    static success(response) {
    }

    static error(response) {
    }
}

class BasketLoader {
    static showWait() {
        BX.showWait();
    }

    static closeWait() {
        BX.closeWait();
    }
}