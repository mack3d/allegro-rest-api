function zmiennazwe() {
  let name = document.getElementById("names")
  name.value = document.getElementById("nameallegro").value
}

async function getproduct(ean_code) {
  const req = await fetch(
    "./allegrogetproductdata.php?" +
      new URLSearchParams({
        ean: ean_code
      }),
    {
      method: "GET"
    }
  )
  const data = await req.json()

  console.log(data)
}

async function getProductFromSote() {
  const code = document.getElementById("code").value
  const names = document.getElementById("names")
  const nameallegro = document.getElementById("nameallegro")
  const short_description = document.getElementById("short_description")
  const description = document.getElementById("description")
  const price = document.getElementById("price")
  const man_code = document.getElementById("man_code")
  const id = document.getElementById("id")
  const odp = document.getElementById("numbera")
  const stock = document.getElementById("stock")
  const allegrocategory = document.getElementById("allegrocategory")

  if (code.length > 3) {
    var grupa = code.substr(0, 2)
    if (grupa == "37") {
      allegrocategory.value = "305589"
    } else if (grupa == "54") {
      allegrocategory.value = "122350"
    } else if (grupa == "44") {
      allegrocategory.value = "304609"
    } else if (grupa == "56") {
      allegrocategory.value = "305293"
    } else if (grupa == "40") {
      allegrocategory.value = "67306"
    } else if (grupa == "07") {
      allegrocategory.value = "111855"
    } else if (grupa == "10") {
      allegrocategory.value = "67306"
    } else if (grupa == "32") {
      allegrocategory.value = "67306"
    } else if (grupa == "15") {
      allegrocategory.value = "49145"
    } else if (grupa == "18") {
      allegrocategory.value = "49146"
    } else if (grupa == "19") {
      allegrocategory.value = "49146"
    } else if (grupa == "87") {
      allegrocategory.value = "63490"
    } else if (grupa == "88") {
      allegrocategory.value = "63638"
    } else if (grupa == "89") {
      allegrocategory.value = "63638"
    } else if (grupa == "20") {
      allegrocategory.value = "111855"
    } else if (grupa == "26") {
      allegrocategory.value = "67378"
    } else if (grupa == "70") {
      allegrocategory.value = "67379"
    } else if (grupa == "13" || grupa == "17") {
      allegrocategory.value = "76362"
    } else if (grupa == "14") {
      allegrocategory.value = "127361"
    } else if (grupa == "21") {
      allegrocategory.value = "15962"
    } else if (grupa == "69") {
      allegrocategory.value = "122403"
    } else if (grupa == "60") {
      allegrocategory.value = "122403"
    } else if (grupa == "45") {
      allegrocategory.value = "67211"
    } else if (grupa == "72") {
      allegrocategory.value = "67355"
    } else if (grupa == "03") {
      allegrocategory.value = "67211"
    } else if (grupa == "75") {
      allegrocategory.value = "67342"
    } else if (grupa == "25") {
      allegrocategory.value = "67288"
    } else if (grupa == "09") {
      allegrocategory.value = "67304"
    } else if (grupa == "34") {
      allegrocategory.value = "68368"
    } else {
      allegrocategory.value = "67346"
    }

    odp.innerText = ""

    const rfetch = await fetch(
      "./getproductdata.php?" +
        new URLSearchParams({
          code: code
        }),
      {
        method: "GET"
      }
    )
    const resp = await rfetch.json()

    soteData = resp.sote
    fpp = resp.fpp[0]
    names.value = soteData.name
    short_description.value =
      "<h2>" +
      replaceTytul(soteData.name) +
      "</h2>" +
      replaceHtml(soteData.short_description)
    description.value = replaceHtml(soteData.description)
    price.value = soteData.price_brutto
    man_code.value = soteData.man_code
    id.value = soteData.id
    stock.value = parseInt(fpp.ilosc)
    if (resp.allegro[0].lastname != "") {
      nameallegro.value = resp.allegro[0].lastname
    }

    liczbaznakow()
    podglad()
    //getproduct(soteData.man_code)
    getallegrocategory()
  }
}

function liczbaznakow() {
  const names = document.getElementById("names")
  const liczbaznakow = document.getElementById("liczbaznakow")
  if (names.value.length > 0) {
    liczbaznakow.value = names.value.length
    if (names.value.length > 75) {
      liczbaznakow.style.color = "red"
    } else {
      liczbaznakow.style.color = "green"
    }
  }
}

function replaceTytul(desc) {
  desc = desc.replace(/</gi, "&lt;")
  desc = desc.replace(/>/gi, "$gt;")
  desc = desc.replace(/&/gi, "&amp;")
  desc = desc.replace(/'/gi, "&apos;")
  desc = desc.replace(/"/gi, "&quot;")
  return desc.trim()
}

function replaceHtml(desc) {
  desc = desc.replace(/(<(!--[^>]+)>)/gi, "")
  desc = desc.replace(/(<(div[^>]+)>)/gi, "")
  desc = desc.replace(/(<([^>]+)div>)/gi, "")
  desc = desc.replace(/(<(br[^>]+)>)/gi, "")
  desc = desc.replace(/(<(p[^>]+)>)/gi, "<p>")
  desc = desc.replace(/(<([^>]+)div>)/gi, "</p>")
  desc = desc.replace(/(<(span[^>]+)>)/gi, "")
  desc = desc.replace(/(<([^>]+)span>)/gi, "")
  desc = desc.replace(/(<(strong)>)/gi, "<b>")
  desc = desc.replace(/(<([^>]+)strong>)/gi, "</b>")
  desc = desc.replace(/(<(ul[^>]+)>)/gi, "<ul>")
  desc = desc.replace(/(<(li[^>]+)>)/gi, "<li>")
  desc = desc.replace(/(<a([^]+)\/a>)/gi, "")
  desc = desc.replace(/(<img([^]+)>)/gi, "")
  desc = desc.replace(/(<(section[^>]+)>)/gi, "")
  desc = desc.replace(/(<\/section>)/gi, "")
  desc = desc.replace(/(<(em)>)/gi, "")
  desc = desc.replace(/(<([^>]+)em>)/gi, "")
  desc = desc.replace(/^\s+$/gm, "")
  return desc.trim()
}

function podglad() {
  const short_description = document.getElementById("short_description")
  const description = document.getElementById("description")
  const lookshort = document.getElementById("lookshort")
  const lookdiv = document.getElementById("lookdiv")
  lookshort.innerHTML = short_description.value
  lookdiv.innerHTML = description.value
}

async function createDraft() {
  const code = document.getElementById("code").value
  const names = document.getElementById("names").value
  const short_description = document.getElementById("short_description")
  const description = document.getElementById("description")
  const price = document.getElementById("price")
  const man_code = document.getElementById("man_code").value
  const allegrocategory = document.getElementById("allegrocategory").value
  const id = document.getElementById("id").value
  const odp = document.getElementById("numbera")
  const stock = document.getElementById("stock").value

  if (man_code == "") {
    man_code = 0
  }
  const dataSend = {
    code: code,
    allegrocategory: allegrocategory,
    names: names,
    short_description: short_description.value,
    description: description.value,
    price: price.value,
    man_code: man_code,
    id: id,
    stock: stock
  }

  const rfetch = await fetch("./addnewdraft.php?", {
    method: "POST",
    body: JSON.stringify(dataSend)
  })
  const response = await rfetch.json()

  if (typeof response.errors != "undefined") {
    showErrors(response.errors)
  } else {
    odp.innerHTML =
      '<a href="https://allegro.pl/offer/' +
      response.id +
      '/restore">Aukcja ' +
      response.id +
      "</a>"
  }
}

function showErrors(errors) {
  const short_description = document.getElementById("short_description")
  const description = document.getElementById("description")
  const price = document.getElementById("price")
  for (e of errors) {
    if (e.path.includes("sections[0]"))
      short_description.style.border = "2px solid red"
    if (e.path.includes("sections[2]"))
      description.style.border = "2px solid red"
    if (e.path.includes("sellingMode.price"))
      price.style.border = "2px solid red"
  }
}

async function getallegrocategory() {
  const allegrocategory = document.getElementById("allegrocategory")
  const allegrocategorypath = document.getElementById("allegrocategoryname")

  const rfetch = await fetch(
    "./allegrocategory.php?" +
      new URLSearchParams({
        id: allegrocategory.value
      }),
    {
      method: "GET"
    }
  )
  const resp = await rfetch.json()

  let pathToCategory = ""
  for (let i = 0; i < resp.length; i++) {
    if (pathToCategory != "") {
      pathToCategory = " -> " + pathToCategory
    }
    pathToCategory = resp[i].name + pathToCategory
  }
  allegrocategorypath.value = pathToCategory
}
