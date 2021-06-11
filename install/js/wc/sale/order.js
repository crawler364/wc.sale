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
                'click',
                {attribute: {'data-action': 'submit'}},
                this.saveOrder.bind(this)
            );
        });
    }

    saveOrder(e) {
        BX.PreventDefault(e);

        this.errorsContainer = BX.findChild(this.orderComponentContainer, {attribute: {'data-container': 'errors'}}, true, false);
        this.orderContainer = BX.findChild(this.orderComponentContainer, {attribute: {'data-container': 'order'}}, true, false);
        let formData = new FormData(this.orderContainer);
        let orderDomHandler = this.getOrderDomHandler();

        if (typeof orderDomHandler === 'object' && typeof orderDomHandler.processStart === 'function') {
            orderDomHandler.processStart();
        }

        BX.ajax.runComponentAction('wc:order', 'saveOrder', {
            mode: 'ajax',
            data: formData,
            signedParameters: this.signedParameters,
        }).then((response) => {
            if (typeof orderDomHandler === 'object' && typeof orderDomHandler.processStart === 'function') {
                orderDomHandler.processEnd();
            }
            if (response.status === 'success') {
                window.location.replace(window.location);
            }
        }, (response) => {
            if (typeof orderDomHandler === 'object' && typeof orderDomHandler.processStart === 'function') {
                orderDomHandler.processEnd();
            }
            if (typeof orderDomHandler === 'object' && typeof orderDomHandler.processStart === 'function') {
                orderDomHandler.processResponseError(response);
            }
        });
    }

    refreshOrder(e) {
        BX.PreventDefault(e);

        this.errorsContainer = BX.findChild(this.orderComponentContainer, {attribute: {'data-container': 'errors'}}, true, false);
        this.orderContainer = BX.findChild(this.orderComponentContainer, {attribute: {'data-container': 'order'}}, true, false);
        let formData = new FormData(this.orderContainer);
        let orderDomHandler = this.getOrderDomHandler();

        if (typeof orderDomHandler === 'object' && typeof orderDomHandler.processStart === 'function') {
            orderDomHandler.processStart();
        }

        BX.ajax({
            url: '?AJAX=Y',
            data: formData,
            method: 'POST',
            dataType: 'html',
            timeout: 30,
            cache: false,
            preparePost: false,
            onsuccess: (response) => {
                BX.adjust(this.orderComponentContainer, {html: response});
                if (typeof orderDomHandler === 'object' && typeof orderDomHandler.processStart === 'function') {
                    orderDomHandler.processEnd();
                }
            },
            onfailure: (response) => {
                if (typeof orderDomHandler === 'object' && typeof orderDomHandler.processStart === 'function') {
                    orderDomHandler.processEnd();
                }
                if (typeof orderDomHandler === 'object' && typeof orderDomHandler.processStart === 'function') {
                    orderDomHandler.processResponseError(response);
                }
            }
        });
    }

    getOrderDomHandler() {
        let orderDomHandler;

        if (typeof WCSaleOrderDomHandler === 'function') {
            orderDomHandler = new WCSaleOrderDomHandler({
                orderComponentContainer: this.orderComponentContainer,
                orderContainer: this.orderContainer,
                errorsContainer: this.errorsContainer
            });
        }

        return orderDomHandler;
    }
}
