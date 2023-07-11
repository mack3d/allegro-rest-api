function edit(elem) {
  var offerid = elem.parentNode.parentNode.parentNode.id
  window.open("https://allegro.pl/offer/" + offerid, "_blank")
}
function same(elem) {
  var offerid = elem.parentNode.parentNode.parentNode.id
  window.open("https://allegro.pl/offer/" + offerid + "/similar", "_blank")
}
function restore(offerid) {
  var offerid = elem.parentNode.parentNode.parentNode.id
  window.open("https://allegro.pl/offer/" + offerid + "/restore", "_blank")
}

function uuidv4() {
  return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, (c) =>
    (
      c ^
      (crypto.getRandomValues(new Uint8Array(1))[0] & (15 >> (c / 4)))
    ).toString(16)
  )
}

function setStatus(buttonElement) {
  const offer = buttonElement.parentNode.parentNode.parentNode
  let message = "status..."
  const uuid = uuidv4()
  information(offer, message, uuid, "uuid_price")
  message = ""
  fetch("./php/setstatus.php", {
    method: "POST",
    body: JSON.stringify({
      offer: offer.id,
      type: "status",
      uuid: uuid
    })
  })
    .then((res) => res.json())
    .then((data) => {
      let status = data
      let alertclass = "uuid_" + status.type
      if (status.response.taskCount.failed > 0) {
        if (status.new_status == "ACTIVATE") {
          message = "nie można wznowić!"
          alertclass = "uuid_alert"
        }
      }
      if (status.response.taskCount.success > 0) {
        if (status.new_status == "ACTIVATE") {
          message = "wznowiono"
          buttonElement.innerText = "zakończ"
        } else {
          message = "zakończono"
          buttonElement.innerText = "wznów"
        }
      }
      informationv2(message, uuid, alertclass)
    })
    .catch((error) => {
      console.log(error)
    })
}

function setPriceDbl(elem) {
  var offer = elem.parentNode.parentNode.parentNode
  var contener = offer.parentNode
  var fpp_price = contener.getElementsByClassName("fpp-price")[0].innerText
  var price = offer.getElementsByClassName("price")[0]
  price.value = fpp_price
  setPrice(elem)
}

function setPrice(elem) {
  var offer = elem.parentNode.parentNode.parentNode
  var price = offer.getElementsByClassName("price")[0]
  var massage = "zmiana ceny..."
  const uuid = uuidv4()
  information(offer, massage, uuid, "uuid_price")
  massage = "ustawiono cenę: "
  fetch("./php/setprice.php", {
    method: "POST",
    body: JSON.stringify({
      offer: offer.id,
      price: price.value,
      type: "price",
      uuid: uuid
    })
  })
    .then((res) => res.json())
    .then((data) => {
      const status = data
      var alertclass = "uuid_" + status.type
      if (status.response.taskCount.failed > 0) {
        massage = "nie można zmienić ceny"
        alertclass = "uuid_alert"
      }
      if (status.response.taskCount.success > 0) {
        massage = massage + price.value
      }
      informationv2(massage, uuid, alertclass)
      prowizja(offer)
    })
    .catch((error) => {
      console.log(error)
    })
}

function prowizja(offer = null) {
  let idsList = []
  if (offer === null) {
    let aukcje = document.getElementsByClassName("offer")
    for (let i = 0; i < aukcje.length; i++) {
      idsList.push(aukcje[i].id)
    }
  } else {
    idsList.push(offer.id)
  }

  fetch("./php/prowizja.php", {
    method: "POST",
    body: JSON.stringify({
      offer_ids: idsList
    })
  })
    .then((res) => res.json())
    .then((data) => {
      for (let i = 0; i < data.length; i++) {
        const info = data[i]
        const { id, shippingRatesId, feePreview, quotes } = info
        const shippingRatesNotSmart = [
          "1c473e41-6626-49f3-a73f-ec08ff7cb47c",
          "9567773b-05c6-416f-95ec-0b98b29900b4",
          "35d4a495-d9d5-480b-9e92-c1d9536a0012",
          "4ae91108-4dba-4765-b567-1769b042b46a"
        ] // id bez smart - leekie duze / lekiie duzo / tylko dpd
        const isSmart = shippingRatesNotSmart.includes(shippingRatesId)
          ? false
          : info.isSmart

        for (let f = 0; f < feePreview.commissions.length; f++) {
          if (feePreview.commissions[f].type == "commissionFee") {
            if (offer == null) {
              offer = document.getElementById(id)
            }
            const contener = offer.parentNode
            const offer_price_el = offer.getElementsByClassName("price")[0]
            let offer_price = offer_price_el.value
            const fpp_price =
              contener.getElementsByClassName("fpp-price")[0].innerText

            const procent = Number(
              (
                parseFloat(feePreview.commissions[f].fee.amount) /
                parseFloat(offer_price)
              ).toFixed(2)
            )

            let min_price = Number(
              (parseFloat(fpp_price) * (1 + procent * 1.1)).toFixed(2)
            )

            if (isSmart) {
              if (min_price < 60.0) {
                min_price += 1.1
              } else if (min_price < 80.0) {
                min_price += 1.7
              } else if (min_price < 200.0) {
                min_price += 4.2
              } else if (min_price < 300.0) {
                min_price += 7.0
              } else {
                min_price += 8.25
              }
            }
            min_price = min_price.toFixed(2)

            information(
              offer,
              "prowizja: " +
                feePreview.commissions[f].fee.amount +
                " (" +
                Number(procent * 100).toFixed(1) +
                " %)",
              "prowizja",
              "commissionFee"
            )

            if (parseFloat(offer_price) < parseFloat(min_price)) {
              information(
                offer,
                "min.cena: " + min_price,
                "minimalnacena",
                "minimalnacena"
              )
              offer_price_el.value = min_price.toString()
            }
          }
        }

        feePreview.quotes.forEach((quote) => {
          if (quote.fee.amount > 0) {
            information(
              offer,
              quote.name +
                " (" +
                quote.cycleDuration +
                "): " +
                quote.fee.amount,
              "quotes",
              "uuid_alert"
            )
          }
        })

        quotes.quotes.forEach((quote) => {
          if (quote.fee.amount > 0) {
            information(
              offer,
              quote.name +
                " (" +
                quote.nextDate.substring(0, 10) +
                "): " +
                quote.fee.amount,
              "quotes",
              "uuid_alert"
            )
          }
        })

        offer = null
      }
    })
    .catch((error) => {
      console.log(error)
    })
}

function getQuantityInOffer(externalId) {
  const firstElemOfExternal = externalId.value.split(" ")[0]
  const count = firstElemOfExternal.replace(/(\d{4,5}|[xX])/g, "") || 1
  return count
}

function setStockDbl(elem) {
  const offer = elem.parentNode.parentNode.parentNode
  const externalId = offer.getElementsByClassName("externalid")[0]
  const count = getQuantityInOffer(externalId)

  let contener = offer.parentNode
  let fpp_stock = contener.getElementsByClassName("fpp-stock")[0].innerText
  let stock = offer.getElementsByClassName("stock")[0]
  stock.value = Math.floor(fpp_stock / count)
  setStock(elem)
}

function setStock(elem) {
  var offer = elem.parentNode.parentNode.parentNode
  var stock = offer.getElementsByClassName("stock")[0]
  var massage = "zmiana ilości..."
  const uuid = uuidv4()
  information(offer, massage, uuid, "uuid_stock")
  massage = "ilość: "
  fetch("./php/setstock.php", {
    method: "POST",
    body: JSON.stringify({
      offer: offer.id,
      stock: stock.value,
      type: "stock",
      uuid: uuid
    })
  })
    .then((res) => res.json())
    .then((data) => {
      const status = data
      var alertclass = "uuid_" + status.type
      if (status.response.taskCount.failed > 0) {
        massage = "błąd zmiany ilości"
        alertclass = "uuid_alert"
      }
      if (status.response.taskCount.success > 0) {
        massage = massage + stock.value
        stock.classList.remove("alert")
      }
      informationv2(massage, uuid, alertclass)
    })
    .catch((error) => {
      console.log(error)
    })
}

function setShipping(elem) {
  var offer = elem.parentNode.parentNode
  fetch("./php/setshipping.php", {
    method: "POST",
    body: JSON.stringify({
      offer: offer.id,
      shipping: elem.value,
      type: "shipping"
    })
  })
    .then((res) => res.json())
    .then((data) => {
      const status = data
      if (status.response.taskCount.failed > 0) {
        massage = "błąd zmiany dostawy"
      }
      if (status.response.taskCount.success > 0) {
        massage = "zmieniono cennik dostawy"
      }
      information(offer, massage, status.uuid, "uuid_" + status.type)
    })
    .catch((error) => {
      console.log(error)
    })
}

function setExternalId(elem) {
  var offer = elem.parentNode.parentNode.parentNode
  var externalid = offer.getElementsByClassName("externalid")[0].value
  fetch("./php/setexternalid.php", {
    method: "POST",
    body: JSON.stringify({
      offer: offer.id,
      externalid: externalid,
      type: "externalid"
    })
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.errors) {
        information(offer, data.errors[0].message, "", "newcode")
      } else {
        massage =
          externalid != data.external.id
            ? "nie udało sie ustawić kodu"
            : "ustawiono: " + data.external.id
        information(offer, massage, "", "newcode")
      }
    })
    .catch((error) => {
      console.log(error)
    })
}

function setEAN(elem) {
  var offer = elem.parentNode.parentNode.parentNode
  var eancode = offer.getElementsByClassName("eancode")[0].value
  fetch("./php/setean.php", {
    method: "POST",
    body: JSON.stringify({
      offer: offer.id,
      ean: eancode,
      type: "eancode"
    })
  })
    .then((res) => res.json())
    .then((data) => {
      massage =
        typeof data.errors != "undefined"
          ? data.errors[0].message
          : "ustawiono EAN"
      information(offer, massage, "", "newean")
    })
    .catch((error) => {
      console.log(error)
    })
}
