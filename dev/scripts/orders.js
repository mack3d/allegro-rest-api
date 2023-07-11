async function getorders(){
    const res = await fetch("../connect.php", {
        method: "POST",
        body: JSON.stringify({
            method: 'GET',
            endpoint: '/order/checkout-forms',
            search: {
                limit: 30,
            }
        })
    });
	const data = await res.json();
    console.log(data);
}