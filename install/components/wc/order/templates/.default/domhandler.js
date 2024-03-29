class WCSaleOrderDomHandler {
    constructor(params) {
        this.orderComponentContainer = params.orderComponentContainer;
        this.orderContainer = params.orderContainer;
        this.errorsContainer = params.errorsContainer;
    }

    processStart() {
        BX.showWait();
    }

    processEnd() {
        BX.closeWait();
    }

    processResponseError(response) {
        BX.cleanNode(this.errorsContainer);

        response.errors.forEach((error) => {
            BX.append(BX.create('div', {'text': error.message}), this.errorsContainer);
        });

        BX.scrollToNode(this.errorsContainer);
    }
}
