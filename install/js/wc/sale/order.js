class WCSaleOrder {
    constructor(params) {
        this.wcOrder = BX(`comp_${params.ajaxId}`);
    }

    init() {
        BX.ready(() => {
            BX.bindDelegate(
                this.wcOrder,
                'click',
                {attribute: {'data-action-refresh': ''}},
                this.refreshOrder.bind(this)
            );

            BX.bindDelegate(
                this.wcOrder,
                'blur',
                {'tag': 'input', 'attribute': {'NAME': 'PLACE'}},
                this.refreshOrder.bind(this)
            );

            BX.bindDelegate(
                this.wcOrder,
                'submit',
                BX('wc-order-form'),
                this.saveOrder.bind(this)
            );
        });
    }

    saveOrder(e) {
        BX.PreventDefault(e);
        OrderLoader.showWait();

        let formData = new FormData(e.target);

        BX.ajax.runComponentAction('wc:order', 'saveOrder', {
            mode: 'ajax',
            data: formData,
        }).then((response) => {
            console.log(response);
            OrderLoader.closeWait();
            if (response.status === 'success') {
                window.location.replace(window.location);
            }
        }, function (response) {
            console.log(response);
            OrderLoader.closeWait();
            // todo обработка ошибок
        });
    }

    refreshOrder(e) {
        BX.PreventDefault(e);
        OrderLoader.showWait();

        let form = BX.findChild(e.currentTarget, {
            'tag': 'form'
        }, true, false);
        let formData = new FormData(form);

        BX.ajax({
            url: '#',
            data: formData,
            method: 'POST',
            dataType: 'html',
            timeout: 30,
            cache: false,
            preparePost: false,
            onsuccess: (data) => {
                BX.adjust(this.wcOrder, {html: data});
                OrderLoader.closeWait();
            },
            onfailure: (data) => {
                OrderLoader.closeWait();
            }
        });
    }
}
