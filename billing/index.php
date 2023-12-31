<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Allegro iSAT Lista patności</title>
    <meta name="author" content="Maciej Krupiński">
    <link rel="stylesheet" href="style.css">
</head>

<body onload="lista()">
    <input id="limit" type="number" onchange="lista()" value="20" min="1" max="100">
    <input id="offset" type="number" onchange="lista()" value="1" min="1" max="1000">
    <select id="group" onchange="lista(this.selectedIndex);">
        <option value="ALL">Wszystkie</option>
        <option value="INCOME">Wpłaty</option>
        <option value="OUTCOME" selected>Wypłaty</option>
        <option value="REFUND">Zwroty</option>
    </select>
    <input id="gte" type="date" onchange="lista()" value="<?php echo date('Y-m-d', strtotime('-7 day')); ?>">
    <input id="lte" type="date" onchange="lista()" value="<?php echo date('Y-m-d'); ?>">
    <select id="operator" onchange="lista(this.selectedIndex);">
        <option value="ALL" selected>Wszystkie</option>
        <option value="PAYU">PayU</option>
        <option value="P24">Przelewy24</option>
    </select>
    <input id="login" type="text" onchange="lista()" placeholder="Login">
    <input type="button" value="Pokaż" onchange="lista()">
    <form action="tmppdf.php" target="_blank">
        <input id="drukujwybrane" type="submit" value="Drukuj wybrane">
        <input id="zaznaczwszystko" onclick="getValue()" type="button" value="Zaznacz wszystkie">
        <table id="lista"></table>
    </form>
</body>

</html>

<template id="outcome">
    <tr class="payment">
        <td></td>
        <td><a href="" target="_blank"></a></td>
        <td></td>
        <td></td>
        <td>wypłata środków</td>
        <td colspan="2"></td>
    </tr>
</template>

<template id="income">
    <tr class="payment">
        <td></td>
        <td><a href="" target="_blank"></a></td>
        <td></td>
        <td></td>
        <td></td>
        <td colspan="2"></td>
    </tr>
</template>

<template id="refund">
    <tr class="payment">
        <td></td>
        <td><a href="" target="_blank"></a></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td><input type="checkbox" class="checks" /></td>
    </tr>
</template>

<script>
    function getValue() {
        const checks = document.getElementsByClassName('checks');
        for (i = 0; i < checks.length; i++) {
            checks[i].checked = true;
        }
    }

    async function lista() {
        const obrot = document.getElementById("obrot");
        const limit = document.getElementById("limit").value;
        const offset = document.getElementById("offset").value;
        const group = document.getElementById("group").value;
        const lte = document.getElementById("lte").value;
        const gte = document.getElementById("gte").value;
        const login = document.getElementById("login").value;
        const operator = document.getElementById("operator").value;
        const lista = document.getElementById("lista");
        const drukujwybrane = document.getElementById("drukujwybrane");
        const zaznaczwszystko = document.getElementById("zaznaczwszystko");

        if (group == "REFUND") {
            drukujwybrane.style.visibility = "visible";
            zaznaczwszystko.style.visibility = "visible";
        } else {
            drukujwybrane.style.visibility = "hidden";
            zaznaczwszystko.style.visibility = "hidden";
        }

        lista.innerText = '';

        if (limit != '') {
            const res = await fetch("./get.php", {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "Content-type": "application/x-www-form-urlencoded"
                },
                body: JSON.stringify({
                    limit: limit,
                    group: group,
                    offset: offset,
                    gte: gte,
                    lte: lte,
                    login: login,
                    operator: operator
                })
            })
            const data = await res.json()
            const propertyValues = Object.values(data);
            propertyValues.reverse()
            const container = document.getElementById("lista")
            const incomeTemplate = document.getElementById("income")
            const refundTemplate = document.getElementById("refund")
            const outcomeTemplate = document.getElementById("outcome")
            let paymentTemp;
            for (let i = 0; i < propertyValues.length; i++) {
                const payment = propertyValues[i]
                switch (payment.group) {
                    case 'REFUND':
                        paymentTemp = addRefund(i, refundTemplate, payment)
                        break;
                    case 'INCOME':
                        paymentTemp = addIncome(i, incomeTemplate, payment)
                        break;
                    default:
                        paymentTemp = addOutcome(i, outcomeTemplate, payment)
                }
                container.appendChild(paymentTemp)
            }
        }
    }

    function addIncome(i, template, payment) {
        const paymentTemp = template.content.cloneNode(true)
        const paymentDate = payment.occurredAt.substring(0, 4) + '-' + payment.occurredAt.substring(5, 7) + '-' + payment.occurredAt.substring(8, 10)

        const tds = paymentTemp.querySelectorAll("td")
        const elem_a = paymentTemp.querySelectorAll("a")[0]
        tds[0].innerText = parseInt(i) + 1
        elem_a.innerText = payment.payment.id
        elem_a.setAttribute("href", '../orders/order.php?paymentid=' + payment.payment.id);
        tds[2].innerText = paymentDate
        tds[3].innerText = payment.value.amount
        tds[4].innerText = payment.participant.login
        tds[5].innerText = payment.wallet.paymentOperator

        return paymentTemp
    }

    function addRefund(i, template, payment) {
        const paymentTemp = template.content.cloneNode(true)
        const paymentDate = payment.occurredAt.substring(0, 4) + '-' + payment.occurredAt.substring(5, 7) + '-' + payment.occurredAt.substring(8, 10)

        const tds = paymentTemp.querySelectorAll("td")
        const elem_a = paymentTemp.querySelectorAll("a")[0]
        const chbox = paymentTemp.querySelectorAll("input")[0]
        tds[0].innerText = parseInt(i) + 1
        elem_a.innerText = payment.payment.id
        elem_a.setAttribute("href", '../orders/order.php?paymentid=' + payment.payment.id);
        tds[2].innerText = paymentDate
        tds[3].innerText = payment.value.amount
        tds[4].innerText = payment.participant.login
        tds[5].innerText = payment.wallet.paymentOperator
        chbox.setAttribute("value", payment.payment.id)
        chbox.setAttribute("id", payment.payment.id)
        chbox.setAttribute("name", payment.payment.id)

        return paymentTemp
    }

    function addOutcome(i, template, payment) {
        const paymentTemp = template.content.cloneNode(true)
        const paymentDate = payment.occurredAt.substring(0, 4) + '-' + payment.occurredAt.substring(5, 7) + '-' + payment.occurredAt.substring(8, 10)

        const tds = paymentTemp.querySelectorAll("td")
        const elem_a = paymentTemp.querySelectorAll("a")[0]
        tds[0].innerText = parseInt(i) + 1
        elem_a.innerText = payment.payout.id
        elem_a.setAttribute("href", 'test.php?numer=' + payment.payout.id + '&operator=' + payment.wallet.paymentOperator + '&data=' + payment.occurredAt + '&suma=' + payment.value.amount)
        tds[2].innerText = paymentDate
        tds[3].innerText = Math.abs(payment.value.amount)
        tds[5].innerText = payment.wallet.paymentOperator

        return paymentTemp
    }
</script>