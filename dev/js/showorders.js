async function getorders(){
    const res = await fetch("../../connect.php", {
        method: "POST",
        body: JSON.stringify({
            method: 'GET',
            endpoint: ' /order/checkout-forms',
            search: {
                limit: 100,
                offset: 0,
                "fulfillment.status": "NEW",
                "status": "READY_FOR_PROCESSING",
            }
        })
    });
	const data = await res.json();
    showOrders(data);
}

function showOrders(orders){
    console.log(orders);
    var container = document.getElementsByClassName("orders")[1];
    orders.checkoutForms.forEach((order) => {
        var template = document.getElementById('order-ready');
        var offertemplate = template.content.cloneNode(true);
        var a = offertemplate.querySelectorAll('a');
        var span = offertemplate.querySelectorAll('span');
        span[0].innerText = order.payment.finishedAt;
        span[1].innerText = order.buyer.login;
        span[2].innerText = order.buyer.firstName + " " + order.buyer.lastName;
        span[3].innerText = order.delivery.method.name;
        span[4].innerText = order.summary.totalToPay.amount;
        container.appendChild(offertemplate);
    });
}