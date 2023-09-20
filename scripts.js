function fod(numer) {
  window.location = "orders/order.php?fod=" + numer
}

function czekamyallegro(numer) {
  window.open(
    "https://allegro.pl/moje-allegro/sprzedaz/zamowienia/?query=" + numer,
    "_blank"
  )
}

function szukaj(event) {
  var event
  var text = document.getElementById("search").value
  if (event.keyCode == 13) {
    if (text.length < 3) {
      alert("wprowadz minimum 3 znaki")
    } else {
      window.location.href = "index.php?search=" + text
    }
  }
}

async function przesylki() {
  var body = document.getElementById("body")
  var wys = body.clientHeight
  var sze = body.clientWidth
  document.getElementById("blokuj").style.height = wys + "px"
  document.getElementById("blokuj").style.width = sze + "px"
  document.getElementById("blokuj").style.display = "block"

  const res = await fetch("./przesylki.php", {
    method: "POST",
    body: JSON.stringify({ inpost: 1 })
  })
  const data = await res.json()
  document.getElementById("blokuj").style.display = "none"
  alert(data.msg)
  window.location.href = "index.php"
}

function blokuj() {
  document.getElementById("blokuj").style.display = "inline"
}

function dpd() {
  var dpd = document.getElementById("dpd")
  var wys = body.clientHeight
  var sze = body.clientWidth
  document.getElementById("dpd").style.height = wys + "px"
  document.getElementById("dpd").style.width = sze + "px"
  document.getElementById("dpd").style.display = "block"
}

function dpdcsv() {
  var dpd = document.getElementById("dpd")
  document.getElementById("dpd").style.display = "none"
}
