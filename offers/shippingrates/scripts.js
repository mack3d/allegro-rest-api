async function getShipping(){
    let dw = await fetch("../../connect.php", {
        method: "POST",
        body: JSON.stringify({
            method: 'GET',
            endpoint: "/sale/delivery-methods",
        })
    });
	const deliveryMethods = await dw.json();

    let res = await fetch("../../connect.php", {
        method: "POST",
        body: JSON.stringify({
            method: 'GET',
            endpoint: '/sale/shipping-rates',
        })
    });
	const data = await res.json();

    promisearray = [];
    for (item of data.shippingRates){
        let ship = getShippingDetails(item.id);
        promisearray.push(ship);
    }
    
    const allShipingRates = await Promise.all(promisearray);

    
    const names = document.getElementById("names");
    let li = document.createElement("li");
    //li.innerText = "\\";
    //names.appendChild(li);

    allShipingRates.forEach(item => {
        let ratesMethodData = []
        item.rates.forEach(rates => {
            let res = deliveryMethods.deliveryMethods.filter(method => method.id == rates.deliveryMethod.id);
            ratesMethodData.push(res[0]);
        });
        showData({item, ratesMethodData});
    });

}

function showData(data){
    const contener = document.getElementById("contener");
    const names = document.getElementById("names");
    const item = data.item;
    const methods = data.ratesMethodData;

    

}

async function getShippingDetails(shippingRates){
    let res = await fetch("../../connect.php", {
        method: "POST",
        body: JSON.stringify({
            method: 'GET',
            endpoint: "/sale/shipping-rates/"+shippingRates,
        })
    });
	const data = await res.json();
    return data;
}