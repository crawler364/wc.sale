class UpdateBasketItemDom {
    static update(dom) {
        if (dom.nodes.basketItem) {
            BX.removeClass(dom.nodes.basketItem, 'disabled');
        }
        if (dom.nodes.restoreButton) {
            BX.addClass(dom.nodes.restoreButton, 'hide');
        }
    }

    static delete(dom) {
        if (dom.container) {
            BX.addClass(dom.nodes.basketItem, 'disabled');
        }
        if (dom.nodes.restoreButton) {
            BX.removeClass(dom.nodes.restoreButton, 'hide');
        }
    }
}

class UpdateBasketProductDom {
    static update(dom) {
    }

    static delete(dom) {
    }
}

class UpdateBasketDom {
    static update(dom) {
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