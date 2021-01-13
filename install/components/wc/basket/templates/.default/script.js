class UpdateTemplateDom {
    static basketItemUpdate(dom) {
        if (dom.container) {
            BX.removeClass(dom.container, 'disabled');
        }
        if (dom.restoreButton) {
            BX.addClass(dom.restoreButton, 'hide');
        }
    }

    static basketItemDelete(dom) {
        if (dom.container) {
            BX.addClass(dom.container, 'disabled');
        }
        if (dom.restoreButton) {
            BX.removeClass(dom.restoreButton, 'hide');
        }
    }
}
