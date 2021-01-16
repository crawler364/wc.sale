class UpdateBasketItemDom {
    static update(dom) {
        if (dom.container) {
            BX.removeClass(dom.basketItem, 'disabled');
        }
        if (dom.restoreButton) {
            BX.addClass(dom.restoreButton, 'hide');
        }
    }

    static delete(dom) {
        if (dom.container) {
            BX.addClass(dom.basketItem, 'disabled');
        }
        if (dom.restoreButton) {
            BX.removeClass(dom.restoreButton, 'hide');
        }
    }
}

class UpdateBasketProductDom {
    static update(dom) {
    }

    static delete(dom) {
    }
}