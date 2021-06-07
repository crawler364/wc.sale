class WCSaleOrder {
    constructor(params) {
        this.parameters = params.parameters;
        this.signedParameters = params.signedParameters;
        this.orderComponentContainer = BX(`comp_${this.parameters.ajaxId}`);
    }

    init() {
        BX.ready(() => {
            BX.bindDelegate(
                this.orderComponentContainer,
                'click',
                {attribute: {'data-action': 'refresh'}},
                this.refreshOrder.bind(this)
            );

            BX.bindDelegate(
                this.orderComponentContainer,
                'click',
                {'class': 'bx-ui-sls-variant'},
                this.refreshOrder.bind(this)
            );

            BX.bindDelegate(
                this.orderComponentContainer,
                'submit',
                BX.findChild(this.orderComponentContainer, {attribute: {'data-container': 'order'}}, true, false),
                this.saveOrder.bind(this)
            );
        });
    }

    saveOrder(e) {
        BX.PreventDefault(e);
        OrderLoader.showWait();

        let errorsContainer = BX.findChild(this.orderComponentContainer, {attribute: {'data-container': 'errors'}}, true, false);
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
        }, (response) => {
            OrderLoader.closeWait();
            response.errors.forEach((error) => {
                BX.append(BX.create('div', {'text': error.message}), errorsContainer);
            });
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
                BX.adjust(this.orderComponentContainer, {html: data});
                OrderLoader.closeWait();
            },
            onfailure: (data) => {
                OrderLoader.closeWait();
            }
        });
    }
}
