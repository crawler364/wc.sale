class WCSaleOrder {
    constructor(params) {

    }

    init() {
        BX.ready(() => {
            console.log(this)

            BX.bindDelegate(
                BX('wc-order'),
                'click',
                {
                    attribute: {'data-action-refresh': ''}
                },
                this.testGetData.bind(this, BX('wc-order'))
            );

            BX.bindDelegate(
                BX('wc-order'),
                'click',
                {
                    attribute: {'data-action-submit': ''}
                },
                this.saveOrderAction.bind(this, BX('wc-order'))
            );

            //BX.bind(BX('wc-order'), 'submit', this.saveOrderAction.bind(this));
        });
    }

    saveOrderAction(order,e) {
        BX.PreventDefault(e);

        BX.ajax.runComponentAction('wc:order', 'saveOrder', {
            mode: 'ajax',
            data: {
                formData: BX.ajax.prepareForm(e.target)
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

        BX('notes').innerHTML='123';

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
            onsuccess: function (data) {
                BX('wc-order').innerHTML = data;

                BX('notes').innerHTML='done';
            },
            onfailure: function (data){
                BX('notes').innerHTML='done';
            }
        });
    }
}