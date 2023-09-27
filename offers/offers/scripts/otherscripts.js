function changeOffset(elem) {
  const offset = document.getElementById("ooffset")

  if (elem.value == "PREV" && offset.value > 0) {
    offset.innerText = offset.value--
  } else {
    offset.innerText = offset.value++
  }

  const oloffset = document.getElementById("oloffset")
  if (parseInt(offset.value) <= 0) {
    offset.value = "0"
    oloffset.setAttribute("disabled", "disabled")
  } else {
    oloffset.removeAttribute("disabled")
  }
  getoffers()
}

function verifyCode(data) {
  let dane = data.trim()
  let resp = { codes: [], offerids: [], type: "offers" }
  if (dane.search("utrzymaniowa") > -1) {
    var offerids = dane.match(/\d{9,14}/g)
    resp.offerids = offerids
    resp.type = "utrzymaniowa"
  } else if (dane.search(" ") > -1) {
    resp.codes = dane.split(" ")
    resp.type = "products"
  } else if (dane != "") {
    resp.codes = [dane]
    resp.type = "products"
  }
  return resp
}

function checkValueIsInt(numberValue) {
  let regexPattern = /^-?[0-9]+$/
  let result = regexPattern.test(numberValue)
  return result
}

function externalIdToArr(external) {
  let externalIds = external.split(" ")
  for (let i = 0; i < externalIds.length; i++) {
    const matchData = externalIds[i].match(/\d{1,5}/g)
    const code = matchData[0]
    const count = matchData[1] ?? "1"
    externalIds[i] = { code, count }
  }
  return externalIds
}

function checkStock() {
  const verGroup = ["39", "21", "41", "30"]
  const verCode = ["2353", "3412", "76453", "2350", "2351", "3668"]
  const containers = document.getElementsByClassName("container")
  for (const item of containers) {
    const offers = item.getElementsByClassName("offer")
    for (const offer of offers) {
      const offerName = offer.getElementsByClassName("name")[0].innerText
      const offerExternalid =
        offer.getElementsByClassName("externalid")[0].value
      const externalIds = externalIdToArr(offerExternalid)

      let offerStock = offer.getElementsByClassName("stock")[0]
      const fppStock = item.getElementsByClassName("fpp-stock")[0].innerHTML
      const fppCode = item.getElementsByClassName("fpp-code")[0].innerHTML
      if (
        fppStock != 0 &&
        checkValueIsInt(fppStock) &&
        !verGroup.includes(fppCode.substring(0, 2)) &&
        !verCode.includes(fppCode)
      ) {
        offerStock.focus()
        if (
          externalIds[0].code == fppCode.toString().trim() &&
          offerStock.value * externalIds[0].count != fppStock
        ) {
          setStockDbl(offerStock)
        }

        if (
          externalIds[0].code != fppCode.toString().trim() &&
          offerStock.value != fppStock
        ) {
          const quest = confirm(
            `${fppCode} - ${offerName} - ${offerStock.value} > ${Math.floor(
              fppStock / externalIds[0].count
            )}`
          )
          if (quest) {
            setStockDbl(offerStock)
          }
        }
      }
    }
  }
}

async function getParameters(offer) {
  const res = await fetch(
    "./php/getparameters.php" +
      new URLSearchParams({ categoryid: offer.category.id })
  )
  const data = await res.json()
  showparameters(offer, data)
}

function getEanFromSote(elem) {
  const offer = elem.parentNode.parentNode.parentNode
  const eancode = offer.getElementsByClassName("eancode")[0]
  const externalid = offer.getElementsByClassName("externalid")[0]
  const code = externalid.value.match(/^\d{4,5}/g)[0]

  fetch("./php/getsoteproductdata.php?" + new URLSearchParams({ code }))
    .then((res) => res.json())
    .then(({ man_code }) => {
      eanvalue = man_code != "" ? man_code : "brak EAN"
      eancode.value = eanvalue.trim()
    })
}
