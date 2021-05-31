class WCSaleOrder {
    constructor(params) {
        this.parameters = params.parameters;
        this.signedParameters = params.signedParameters;
        this.orderContainer = BX(`comp_${this.parameters.ajaxId}`);
    }

    init() {
        BX.ready(() => {
            BX.bindDelegate(
                this.orderContainer,
                'click',
                {attribute: {'data-action-refresh': ''}},
                this.refreshOrder.bind(this)
            );

            BX.bindDelegate(
                this.orderContainer,
                'blur',
                {'tag': 'input', 'attribute': {'name': 'LOCATION'}}, // todo NAME
                this.refreshOrder.bind(this)
            );

            BX.bindDelegate(
                this.orderContainer,
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
            signedParameters: this.signedParameters,
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
            url: '?AJAX=Y',
            data: formData,
            method: 'POST',
            dataType: 'html',
            timeout: 30,
            cache: false,
            preparePost: false,
            onsuccess: (data) => {
                BX.adjust(this.orderContainer, {html: data});
                OrderLoader.closeWait();
            },
            onfailure: (data) => {
                OrderLoader.closeWait();
            }
        });
    }
}
