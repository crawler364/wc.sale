class WCSaleOrder {
    constructor(params) {
        this.wcOrder = BX('wc-order');
    }

    init() {
        BX.ready(() => {

            BX.bindDelegate(
                this.wcOrder,
                'click',
                {
                    attribute: {'data-action-refresh': ''}
                },
                this.testGetData.bind(this)
            );

            BX.bindDelegate(
                this.wcOrder,
                'blur',
                {
                    'tag': 'input',
                    'attribute': {'NAME': 'PLACE'}
                },
                this.testGetData.bind(this)
            );

            BX.bindDelegate(
                this.wcOrder,
                'submit',
                BX('wc-order-form'),
                this.saveOrderAction.bind(this)
            );
        });
    }

    saveOrderAction(e) {
        BX.PreventDefault(e);

        let formData = new FormData(e.target);

        BX.ajax.runComponentAction('wc:order', 'saveOrder', {
            mode: 'ajax',
            data: formData,
        }).then((response) => {
            console.log(response);
        }, function (response) {
            console.log(response);
            // todo обработка ошибок
        });
    }

    testGetData(e) {

        console.log(e.currentTarget);

        BX.PreventDefault(e);
        OrderLoader.showWait();
        let form = BX.findChild(e.currentTarget, {
            'tag': 'form'
        }, true, false);

        let formData = new FormData(form);

        BX.ajax({
            url: '/local/components/wc/order/get.php',
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
