class Offers {
  constructor() {
    this.searchText = document.getElementById("search")
    this.loadJson = document.getElementById("dostawa").checked
    this.statusOffers = document.getElementById("ostatus").value
    this.allCodesElement = document.getElementById("all_code")
    this.searchData = {
      offerids: null,
      codes: null,
      dostawa: null,
      name: null,
      utrzymaniowa: 0
    }
  }

  async loadDeliveryData() {
    if (this.loadJson) {
      this.searchData.dostawa = await readJsonfile()
      this.searchData.codes = this.searchData.dostawa.codes
      this.searchText.value = ""
    }
  }

  parseSearchValue() {
    const searchValue = this.searchText.value.trim()
    if (searchValue.length > 1) {
      this.searchData.offerids = searchValue.match(/\d{9,18}/g)
      const utrzymaniowa = searchValue.match(/utrzymaniowa/g)
      this.searchData.utrzymaniowa =
        utrzymaniowa != null ? utrzymaniowa.length : 0
      this.searchData.codes = searchValue.match(/\d{4,5}/g)
      this.searchData.name = searchValue.match(/[A-Za-z0-9]{2,20}/gi)
    }
    this.searchData.codes =
      this.searchData.offerids != null ? [] : this.searchData.codes
    this.searchData.name =
      this.searchData.codes == null ? this.searchData.name : null
  }

  async fetchAllegroOffers() {
    const allegro = await getAllegroOffers(
      this.searchData.offerids,
      this.searchData.codes,
      this.searchData.name,
      this.searchData.utrzymaniowa
    )
    return allegro
  }

  async fetchFppProducts(allegro) {
    const products = await getFppProducts(
      this.searchData.codes,
      allegro.allcodes
    )
    return products
  }

  async showOffersOrProducts(allegro, products) {
    if (this.searchData.codes != null || this.searchData.offerids != null) {
      await showproducts(allegro, products, this.searchData)
      await addButtonCreateNew()
    } else {
      await showOffers(allegro, products)
      await addButtonCreateNew()
    }
  }

  async showAdminStatus() {
    if (this.statusOffers == "ADMIN") {
      const offers = document.getElementsByClassName("id")
      for (const offer of offers) {
        const offerData = await getOffer(offer.innerText)
        const parent = offer.parentNode.parentNode
        //console.log(offer.innerText, offerData.publication.endedBy)
        if (offerData.publication.endedBy == "ADMIN") {
          const stat = parent.getElementsByClassName("stats")[0]
          const par = document.createElement("p")
          par.innerText = "ADMIN"
          stat.appendChild(par)
        }
      }
      console.log("done")
    }
  }

  async getOffers() {
    await this.loadDeliveryData()
    this.parseSearchValue()
    const allegro = await this.fetchAllegroOffers()
    const products = await this.fetchFppProducts(allegro)
    document.getElementById("showoffers").innerHTML = ""
    document.getElementById("totalcount").innerText = allegro.totalCount
    this.allCodesElement.value = allegro.allcodes.join(" ")
    await this.showOffersOrProducts(allegro, products)
    await this.showAdminStatus()
  }
}

async function getoffers() {
  const offers = new Offers()
  offers.getOffers()
}

function searchData() {
  document.getElementById("ooffset").value = 0
  getoffers()
}

function searchByCodes() {
  const allCodesElement = document.getElementById("all_code").value
  document.getElementById("search").value = allCodesElement
  getoffers()
}

async function readJsonfile() {
  let data = {}
  const res = await fetch("./dostawatowaru.json", { cache: "no-store" })
  data = await res.json()
  return data
}

async function getOffer(id) {
  const res = await fetch("./php/getallegrooffer.php", {
    method: "POST",
    body: JSON.stringify({ id })
  })
  const data = await res.json()
  return data
}

async function getShipping() {
  const res = await fetch("../../connect.php", {
    method: "POST",
    body: JSON.stringify({
      method: "GET",
      endpoint: "/sale/shipping-rates"
    })
  })
  const data = await res.json()
  return data.shippingRates
}

async function getAllegroOffers(
  offerids = [],
  codes = [],
  name = [],
  utrzymaniowa = 0
) {
  const status =
    document.getElementById("ostatus").value == "ADMIN"
      ? "ENDED"
      : document.getElementById("ostatus").value
  const res = await fetch("./php/getallegrooffers.php", {
    method: "POST",
    headers: {
      Accept: "application/json"
    },
    body: JSON.stringify({
      offset: document.getElementById("ooffset").value,
      limit: document.getElementById("olimit").value,
      cenaod: document.getElementById("ocenaod").value,
      cenado: document.getElementById("ocenado").value,
      status: status,
      sort: document.getElementById("osort").value,
      offerids: offerids,
      codes: codes,
      name: name,
      utrzymaniowa: utrzymaniowa
    })
  })
  const data = await res.json()
  if (!Array.isArray(data.allcodes)) {
    data.allcodes = Object.values(data.allcodes)
  }
  return data
}

async function getFppProducts(codes, allegroCodes) {
  const res = await fetch("./php/getfppproductsv2.php", {
    method: "POST",
    body: JSON.stringify({
      codes: codes,
      allegroCodes: allegroCodes
    })
  })
  const data = await res.json()
  return data
}
/* ---- pobieranie danych ---- */

/* ---- wyświetlanie danych ---- */
async function showOffers(allegro, fpp) {
  const offers = allegro.offers
  const ostock = document.getElementById("ostock").value
  const shippingRates = await getShipping()

  let showcodes = ""
  offers.forEach((offer, i) => {
    const externalcodes = externalids(offer.name, offer.external)
    let fppList = []
    let stock = { notempty: "", empty: "", toomuch: "", pricetolower: "" }
    let externalidlist = externalcodes.idlist
    let loops = 0
    while (loops < 2) {
      externalidlist.forEach((code) => {
        const fppproducts = fpp.filter((product) => product.kodn == code)

        fppList = fppList.concat(fppproducts)
        fppproducts.forEach((product) => {
          if (product.ilosc >= offer.stock.available) {
            stock["notempty"] = code
          }
          if (product.ilosc == 0) {
            stock["empty"] = code
          }
          if (product.ilosc < offer.stock.available) {
            stock["toomuch"] = code
          }
          if (product.cena > offer.sellingMode.price.amount) {
            stock["pricetolower"] = code
          }
        })
      })
      if (fppList.length == 0 && externalidlist[0].length == 5) {
        externalidlist.push(externalidlist[0].substring(0, 4))
        loops++
      } else {
        break
      }
    }
    fppList.sort(compare)
    fppList.reverse()

    if (
      (ostock == "pricetolower" && stock.pricetolower != "") ||
      (ostock == "notempty" && stock.empty == "") ||
      (ostock == "empty" && stock.empty != "") ||
      (ostock == "toomuch" && stock.toomuch != "") ||
      ostock == "all"
    ) {
      createContainer(i)
      createOffer(i, offer, externalcodes, shippingRates)
      createProduct(i, fppList)
      alerts(i, offer, fppList)
    }
    showcodes += externalcodes.idlist[0] + " "
  })
  //console.log(showcodes);
}

async function showproducts(allegro, fpp, search) {
  let allCodeList =
    search.codes != "" && search.codes !== null
      ? search.codes
      : allegro.allcodes

  const shippingRates = await getShipping()

  const codes_list = [...new Set(allCodeList)]
  const dostawa = search.dostawa != null ? search.dostawa.products : null

  codes_list.forEach((productCode) => {
    const productData = fpp.filter((product) => product.kodn == productCode)
    productData.forEach((product) => {
      createContainer(productCode)
      createProduct(productCode, [product])
      if (dostawa != null) {
        createProduct(productCode, [dostawa[productCode]], "dostawa")
      }
    })
  })

  allegro.offers.forEach((offer) => {
    const externalcodes = externalids(offer.name, offer.external)
    externalcodes.idlist.forEach((code) => {
      let resp = createOffer(code, offer, externalcodes, shippingRates)
      if (typeof resp.status == "code_not_found" && code.length > 4) {
        code = code.substring(0, 4)
        resp = createOffer(code, offer, externalcodes, shippingRates)
      }
      alertproducts(code, offer)
    })
  })
}

function addButtonCreateNew() {
  const containers = Array.prototype.slice.call(
    document.getElementsByClassName("container")
  )
  containers.forEach((container) => {
    const offers = Array.prototype.slice.call(
      container.getElementsByClassName("offer")
    )
    const product = Array.prototype.slice.call(
      container.getElementsByClassName("product")
    )
    if (offers.length == 0 && product.length > 0) {
      try {
        const product = container.getElementsByClassName("product")[0]
        const spans = product.getElementsByTagName("span")
        const href =
          "http://sat.pl/newallegro/offers/fromsote/?kod=" + spans[0].innerText
        const a = document.createElement("a")
        a.href = href
        a.setAttribute("target", "_blank")
        a.innerText = "Utwórz nowy"
        spans[spans.length - 1].appendChild(a)
      } catch (err) {
        console.log(err)
      }
    }
  })
}

function createContainer(item) {
  const showoffers = document.getElementById("showoffers")
  const container = document.createElement("div")
  container.classList.add("container")
  container.id = item
  showoffers.appendChild(container)
}

function createOffer(item, offer, externalcodes, shipping) {
  const container = document.getElementById(item)
  let status_create = { status: "code_not_found", code: item }

  if (container != null) {
    const template = document.getElementById("offer-data")
    let offertemplate = template.content.cloneNode(true)

    let offerdata = offertemplate.querySelectorAll("div")
    offerdata[0].id = offer.id
    offerdata[0].classList.add(offer.id)
    offerdata[1]
      .querySelector("img")
      .setAttribute("src", offer.primaryImage.url)
    let desc = offerdata[2]
    let stats = offerdata[3]
    let action = offerdata[4]
    let a = desc.querySelectorAll("a")
    a[0].innerText = offer.name
    a[0].href = "https://allegro.pl/oferta/" + offer.id
    let p = desc.querySelectorAll("p")
    p[0].innerText = offer.id
    p[1].innerText = externalcodes.id
    let sel = desc.querySelectorAll("select")

    shipping.forEach((item) => {
      const opt = document.createElement("option")
      opt.setAttribute("value", item.id)
      opt.innerText = item.name
      if (offer.delivery.shippingRates != null) {
        item.id == offer.delivery.shippingRates.id
          ? opt.setAttribute("selected", "selected")
          : ""
      }
      sel[0].add(opt, null)
    })

    var paragrafStats = stats.querySelectorAll("p")
    paragrafStats[0].innerText = "sprzedano " + offer.stock.sold
    paragrafStats[1].innerText = "obserw " + offer.stats.watchersCount
    paragrafStats[2].innerText = "wizyt " + offer.stats.visitsCount
    let inp = action.querySelectorAll("input")
    inp[0].value = parseFloat(offer.sellingMode.price.amount).toFixed(2)
    inp[1].value = offer.stock.available
    inp[2].value = externalcodes.idnowe
    let offerbtn = offertemplate.querySelectorAll("button")
    offerbtn[6].innerHTML =
      offer.publication.status == "ACTIVE" ? "zakończ" : "wznów"
    if (offer.publication.status == "INACTIVE") {
      offerbtn[6].innerHTML = "szkic"
      offerbtn[6].setAttribute("disabled", "disabled")
    }

    container.appendChild(offertemplate)
    status_create = { status: "offer_create", code: item }
  }
  return status_create
}

function createProduct(item, products, type = "product") {
  let container = document.getElementById(item)
  let template = document.getElementById("product-data")
  let ul = document.createElement("ul")
  ul.setAttribute("class", "products")
  products.forEach((product) => {
    let producttemplate = template.content.cloneNode(true)
    let span = producttemplate.querySelectorAll("span")
    span[0].innerText = type != "dostawa" ? product.kodn : ""
    span[1].innerText = type != "dostawa" ? product.nazwa : type
    span[2].innerText = type != "dostawa" ? product.ilosc : product.stock
    span[3].innerText =
      type != "dostawa"
        ? parseFloat(product.cena).toFixed(2)
        : parseFloat(product.price).toFixed(2)
    ul.appendChild(producttemplate)
  })
  container.appendChild(ul)
}

function information(elem, massage, uuid = "", type = "") {
  let info = elem.getElementsByClassName("info")[0]

  let infochanged = info.getElementsByClassName("infochanged")
  let forCount = infochanged.length - 4
  if (forCount > 0) {
    for (w = 0; w < forCount; w++) {
      infochanged[0].remove()
    }
  }
  let li = document.createElement("li")
  li.id = uuid
  li.classList.add("infochanged")
  li.classList.add(type)
  li.innerText = massage
  info.appendChild(li)
}

function informationv2(massage, uuid = "", type = "") {
  let info = document.getElementById(uuid)
  info.innerText = massage
  info.classList.add(type)
}

function alerts(item, offer, fpp) {
  let container = document.getElementById(item)
  let offershow = container.getElementsByClassName(offer.id)[0]
  let priceoffer = offershow.getElementsByClassName("price")[0]
  let stockoffer = offershow.getElementsByClassName("stock")[0]

  let stockalert = false
  let pricealert = false
  for (let i = 0; i < fpp.length; i++) {
    if (fpp[i].ilosc < offer.stock.available) {
      stockalert = true
    }
    if (parseFloat(fpp[i].cena) > parseFloat(offer.sellingMode.price.amount)) {
      pricealert = true
    }
  }
  if (offer.publication.status == "ACTIVE") {
    if (stockalert) {
      stockoffer.classList.add("alert")
    }
    if (pricealert) {
      priceoffer.classList.add("alert")
    }
  }
}

function alertproducts(item, offer) {
  let container = document.getElementById(item)
  if (container != null) {
    let pricefpp = container.getElementsByClassName("fpp-price")[0].innerText
    let stockfpp = container.getElementsByClassName("fpp-stock")[0].innerText
    let offershow = container.getElementsByClassName(offer.id)[0]
    let priceoffer = offershow.getElementsByClassName("price")[0]
    let stockoffer = offershow.getElementsByClassName("stock")[0]

    let stockalert = false
    let pricealert = false

    if (parseFloat(stockfpp) < parseFloat(stockoffer.value)) {
      stockalert = true
    }
    if (parseFloat(pricefpp) > parseFloat(priceoffer.value)) {
      pricealert = true
    }
    if (offer.publication.status == "ACTIVE") {
      if (stockalert) {
        stockoffer.classList.add("alert")
      }
      if (pricealert) {
        priceoffer.classList.add("alert")
      }
    }
  }
}

function externalids(name, external) {
  let id,
    idnowe,
    idd = ""
  let idList = []
  if (external != null) {
    id = external.id
    idnowe = external.id
    if (id.search(" ") > 0) {
      idList = id.split(" ")
    }
  } else {
    id = name.substr(-5)
    idnowe = id.replace(/[^\d]/g, "")
    id = "z nazwy" + id.replace(/[^\d]/g, "")
  }
  if (idList.length == 0) {
    idd = idnowe
    if (id.search("x") != -1) {
      idd = id.split("x")[0]
    }
    idList.push(idd)
  } else {
    for (let i = 0; i < idList.length; i++) {
      if (idList[i].search("x") > 0) {
        idList[i] = idList[i].split("x")[0]
      }
    }
  }
  return { id: id, idlist: idList, idnowe: idnowe }
}

function compare(a, b) {
  if (parseInt(a.cena) < parseInt(b.cena)) {
    return -1
  }
  if (parseInt(a.cena) > parseInt(b.cena)) {
    return 1
  }
  return 0
}

function showparameters(offer, parameters) {
  var offerparameters = offer.parameters
  var parameters = parameters.parameters
  var div = document.getElementById("parameterserror")
  div.innerHTML = ""
  var template = document.getElementById("parametersdata")
  var templatedict = document.getElementById("parametersdict")

  for (var i = 0; i < parameters.length; i++) {
    var ch = false
    for (var k = 0; k < offerparameters.length; k++) {
      if (offerparameters[k].id == parameters[i].id) {
        ch = true
      }
    }
    if (ch == false) {
      if (parameters[i].type != "dictionary") {
        var parameterstemplate = template.content.cloneNode(true)
        var paramname = parameterstemplate.querySelectorAll("span")
        var paramvalue = parameterstemplate.querySelectorAll("input")
        paramname[0].innerText = parameters[i].name
        paramvalue[0].name = parameters[i].id
        paramvalue[0].placeholder = parameters[i].type
        if (parameters[i].required !== false) {
          paramvalue[0].classList.add("requiredparam")
        }
        if (parameters[i].required !== false) {
          div.appendChild(parameterstemplate)
        }
      } else {
        var parameterstemplate = templatedict.content.cloneNode(true)
        var paramname = parameterstemplate.querySelectorAll("span")
        paramname[0].innerText = parameters[i].name
        var select = parameterstemplate.querySelectorAll("select")
        select[0].name = parameters[i].id
        if (parameters[i].required !== false) {
          select[0].classList.add("requiredparam")
        }
        for (let d = 0; d < parameters[i].dictionary.length; d++) {
          let opt = document.createElement("option")
          opt.value = parameters[i].dictionary[d].id
          opt.innerText = parameters[i].dictionary[d].value
          select[0].appendChild(opt)
        }
        if (parameters[i].required !== false) {
          div.appendChild(parameterstemplate)
        }
      }
    }
  }
  let input = document.createElement("input")
  input.type = "button"
  input.name = "set"
  input.value = "Zapisz dla " + offer.id
  input.addEventListener("click", function () {
    setParamsOffer(this, offer)
  })
  div.appendChild(input)
  div.style.display = "block"
}

function setParamsOffer(element, offer) {
  let setparams = element.parentNode.getElementsByClassName("param-string")
  let div = document.getElementById("parameterserror")
  for (let i = 0; i < setparams.length; i++) {
    par = setparams[i].children
    if (par[1].tagName == "SELECT") {
      if (par[1].options[par[1].selectedIndex].value != "") {
        p = {
          id: par[1].name,
          valuesIds: [par[1].options[par[1].selectedIndex].value]
        }
      }
    } else {
      if (par[1].value != "") {
        p = { id: par[1].name, values: [par[1].value] }
      }
    }
    offer.parameters.push(p)
  }
  //console.log(offer);
  if (window.XMLHttpRequest) {
    xmlhttp = new XMLHttpRequest()
  } else {
    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP")
  }
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      //console.log(xmlhttp.responseText);
      let odpowiedz = JSON.parse(xmlhttp.responseText)
      div.style.display = "none"
    }
  }
  let url = "&offerdata=" + JSON.stringify(offer)
  xmlhttp.open("POST", "./php/setparameters.php", true)
  xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
  xmlhttp.send(url)
}
