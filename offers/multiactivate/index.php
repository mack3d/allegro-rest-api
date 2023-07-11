<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Allegro - zakończ oferty</title>
	<meta name="author" content="Maciej Krupiński">
</head>
<body id="body">
<textarea onchange="getofferlist()" id="codes" name="codes" rows="1" cols="50"></textarea>

<ol id="lista">
</ol>

<script type="text/javascript">

function zakoncz(offerid){
    var button = document.getElementById("z"+offerid);
    if (window.XMLHttpRequest) {xmlhttp=new XMLHttpRequest();} else {xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var odpowiedz = JSON.parse(xmlhttp.responseText);
            if(typeof(odpowiedz.errors) == "undefined"){
                button.setAttribute("disabled","disabled");
            }else{
                console.log(odpowiedz);
            }
        }
    }
    var url = "&offerid="+offerid;
    xmlhttp.open("POST","zakoncz.php",true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send(url);
}

function wyswietl(offers,codes){
    var lista = document.getElementById("lista");
    lista.innerText = "";
    code = codes.value.split(" ");
    if (offers.length > 0){
        for(var c = 0; c < code.length; c++){
            szukaj = code[c];
            for(o = 0; o < offers.length; o++){
                offer = offers[o];
                if (offer.external != null){
                    tytuloferty = offer.name+" ["+offer.external.id+"]";
                }else{
                    tytuloferty = offer.name;
                }
                if (tytuloferty.includes(szukaj)){
                    var li = document.createElement("li");

                    var span = document.createElement("span");
                    var textspan = document.createTextNode(tytuloferty);
                    span.appendChild(textspan);
                    span.style.width = "620px";
                    span.style.display = "inline-block";

                    var buttonz = document.createElement("button");
                    var textbuttonz = document.createTextNode("Zakończ");
                    buttonz.appendChild(textbuttonz);
                    buttonz.setAttribute("id", "z"+offer.id);
                    buttonz.setAttribute("onClick", "zakoncz("+offer.id+")");
                    
                    li.appendChild(span);
                    li.appendChild(buttonz);
                    li.style.marginBottom = "5px";
                    li.setAttribute("id", offer.id);
                    lista.appendChild(li);
                }
            }
            if (lista.childElementCount > 0){
                lista.lastChild.style.marginBottom = "20px";
            }
        }
    }else{
        alert("Brak pasujących ofert");
    }
}

function getofferlist(){
    var codes = document.getElementById("codes");
        code = codes.value.split(" ");
        if (window.XMLHttpRequest) {xmlhttp=new XMLHttpRequest();} else {xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
        xmlhttp.onreadystatechange=function() {
            if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                var odpowiedz = JSON.parse(xmlhttp.responseText);
                var re = [];
                var i = 0;
                while (i < odpowiedz.length){
                    offer = odpowiedz[i];
                    if (offer.external != null){
                        kod = offer.external.id;
                    }else{
                        kod = offer.name;
                    }
                    if(code.some(substring => kod.includes(substring))){
                        re.push(offer)
                    }
                    i += 1;
                }
                wyswietl(re,codes);
            }
        }
        var url = "";
        xmlhttp.open("POST","getofferslist.php",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(url);
}

</script>