var AcimaCreditCheckout = {
    iframe: null,
    waiting: false,
    orderUpdated: false,
    redirect: false,
    transaction: null,
    customer: null,
};

AcimaCreditCheckout.onDOMContentLoaded = function () {
    if (AcimaCredit.getQueryString('acima-credit') && AcimaCredit.getQueryString('acima-credit') == '1') {
        AcimaCreditCheckout.iframe = document.getElementById('acima-credit-iframe-checkout');
        AcimaCredit.log('onDOMContentLoaded');
        var body = document.getElementsByTagName('body')[0];
        body.classList.add('frozen');
        AcimaCreditCheckout.start();
    } else {
        AcimaCredit.log('onDOMContentLoaded acima-credit not set');
    }
};

AcimaCreditCheckout.start = function () {
    AcimaCredit.log('AcimaCreditCheckout waiting=true');
    if (AcimaCredit.isReady) {
        AcimaCreditCheckout.init();
    } else {
        AcimaCreditCheckout.waiting = true;
    }
}

AcimaCreditCheckout.getCustomerInformation = function () {
    AcimaCredit.log('AcimaCreditCheckout.getCustomerInformation()');
    var orderId = AcimaCredit.getQueryString('order');
    var nonce = AcimaCredit.getQueryString('nonce');

    var data = {
        action: 'acima_credit_customer_info',
        order: orderId,
        nonce: nonce
    };

    AcimaCredit.ajaxCall(data, AcimaCreditCheckout.onLoadAjaxInformation);
};

AcimaCreditCheckout.getOrderInformation = function () {
    AcimaCredit.log('AcimaCreditCheckout.getOrderInformation()');
    var orderId = AcimaCredit.getQueryString('order');
    var nonce = AcimaCredit.getQueryString('nonce');

    var data = {
        action: 'acima_credit_order_info',
        order: orderId,
        nonce: nonce
    };

    AcimaCredit.ajaxCall(data, AcimaCreditCheckout.onLoadAjaxInformation);


    /// calling success callback (temp solution for API errors)
    /*AcimaCreditCheckout.success({
        type: AcimaCredit.MESSAGE_TYPE_CHECKOUT_SUCCESSFUL,
        leaseId: "000-000-0001-0028",
        transactionToken: "09284-192847-192747"
    });*/
};

AcimaCreditCheckout.onLoadAjaxInformation = function (response) {
    AcimaCredit.log('AcimaCreditCheckout.onLoadAjaxInformation()');
    if (response.success && response.type == AcimaCredit.TYPE_RETURN_AJAX_ORDER_INFO) {
        AcimaCreditCheckout.transaction = response.data;
    } else if (response.success && response.type == AcimaCredit.TYPE_RETURN_AJAX_CUSTOMER_INFO) {
        AcimaCreditCheckout.customer = response.data;
    }

    if (AcimaCreditCheckout.transaction && AcimaCreditCheckout.customer) {
        var message = {
            type: AcimaCredit.MESSAGE_TYPE_INITIALIZE,
            mode: AcimaCredit.MESSAGE_MODE_CHECKOUT,
            transaction: AcimaCreditCheckout.transaction,
            customer: AcimaCreditCheckout.customer,
            merchantId: acima_credit_settings.merchant_id
        };
        AcimaCredit.log('postMessage');
        AcimaCredit.log(message);
        AcimaCredit.postMessage(message, AcimaCreditCheckout.iframe);
    };
};

AcimaCreditCheckout.init = function () {
    AcimaCredit.log('AcimaCreditCheckout.init()');
    AcimaCreditCheckout.getCustomerInformation();
    AcimaCreditCheckout.getOrderInformation();
};

AcimaCreditCheckout.success = function (message) {
    AcimaCreditCheckout.waiting = true;

    var orderId = AcimaCredit.getQueryString('order');
    var nonce = AcimaCredit.getQueryString('nonce');
    var leaseId = message.leaseId;
    var checkoutToken = message.checkoutToken;

    var data = {
        action: 'acima_credit_checkout_successful',
        order: orderId,
        nonce: nonce,
        lease_id: leaseId,
        checkout_token: checkoutToken
    };

    AcimaCredit.ajaxCall(data, AcimaCreditCheckout.onUpdateOrder);
};

AcimaCreditCheckout.onUpdateOrder = function (data) {
    if (data.success) {
        AcimaCreditCheckout.orderUpdated = true;

        AcimaCreditCheckout.redirectToThankYouPage();
    } else {
        alert('There was an error to process your transaction, please contact our team.');
    }
};

AcimaCreditCheckout.redirectToThankYouPage = function () {
    if (AcimaCreditCheckout.orderUpdated && AcimaCreditCheckout.redirect) {
        var thank_you_page = AcimaCredit.getQueryString('redirect');   
        window.location.href = thank_you_page;
    }    
};

document.addEventListener('DOMContentLoaded', AcimaCreditCheckout.onDOMContentLoaded);