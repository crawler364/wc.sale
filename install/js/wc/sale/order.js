class WCSaleOrder {
    constructor(params) {

    }

    init() {
        BX.ready(() => {
            BX.bindDelegate(
                BX('wc-order'),
                'click',
                {
                    attribute: {'data-action-refresh': ''}
                },
                this.testGetData.bind(this, BX('wc-order'))
            );

            BX.bindDelegate(
                document.body,
                'submit',
                BX('wc-order'),
                this.saveOrderAction.bind(this, BX('wc-order'))
            );

            BX.bind(BX('wc-order'), 'submit', this.saveOrderAction.bind(this));
        });
    }

    saveOrderAction(e) {
        BX.PreventDefault(e);

        BX.ajax.runComponentAction('wc:order', 'saveOrder', {
            mode: 'ajax',
            data: {
                orderData: BX.ajax.prepareForm(e.target).data
            }
        }).then((response) => {
            console.log(response);
        }, function (response) {
            console.log(response);
            // todo обработка ошибок
        });
    }

    testGetData(order, e) {
        BX.PreventDefault(e);

        OrderLoader.showWait();

        BX.ajax({
            url: '/local/components/wc/order/get.php',
            data: BX.ajax.prepareForm(order),
            method: 'POST',
            dataType: 'html',
            timeout: 30,
            async: true,
            processData: true,
            scriptsRunFirst: true,
            emulateOnload: true,
            start: true,
            cache: false,
            onsuccess: (data) => {
                BX('wc-order').innerHTML = data;
                OrderLoader.closeWait();
            },
            onfailure: (data) => {
                OrderLoader.closeWait();
            }
        });
    }
}
