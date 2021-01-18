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
        if (dom.container) {
            BX.addClass(dom.nodes.basketItem, 'disabled');
        }
        if (dom.nodes.restoreButton) {
            BX.removeClass(dom.nodes.restoreButton, 'hide');
        }
    }
}

class UpdateBasketProductDom {
    static update(dom, basketProduct) {
    }

    static delete(dom, basketProduct) {
    }
}

class UpdateBasketDom {
    static update(dom, basket) {
        if (dom.nodes.empty) {
            BX.addClass(dom.nodes.empty, 'hide');
        }
        if (dom.container) {
            let tbody = BX.findChild(dom.container, {
                'tag': 'tbody',
            }, false, false);
            BX.removeClass(tbody, 'hide');
        }
    }

    static delete(dom) {
        if (dom.nodes.empty) {
            BX.removeClass(dom.nodes.empty, 'hide');
        }
        if (dom.container) {
            let tbody = BX.findChild(dom.container, {
                'tag': 'tbody',
            }, false, false);
            BX.addClass(tbody, 'hide');
        }
    }
}