class WCSaleOrder {
    constructor(params) {

    }

    init() {
        BX.ready(() => {
            console.log(this)
            BX.bind(BX('wc-order'), 'submit', this.testGetData.bind(this));
        });
    }

    saveOrderAction(e) {
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

    testGetData(e) {
        BX.PreventDefault(e);

        BX.ajax({
            url: '/local/components/wc/order/get.php',
            data: {},
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
                BX('wc-order').innerHTML += data
            }
        });
    }
}