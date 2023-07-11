<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="author" content="Maciej Krupiński">
</head>
<body id="body">
<input id="procent" onkeyup="oferty()" type="text" placeholder="procent">
<input id="licznik">
<ol id="lista">
</ol>
</body>
</html>

<script type="text/javascript">
function oferty(){
    if (window.XMLHttpRequest) {xmlhttp=new XMLHttpRequest();} else {xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var odpowiedz = JSON.parse(xmlhttp.responseText);
            //console.log(odpowiedz);
            sprawdz(odpowiedz,0);
        }
    }
    var url = "&procent=0";
    xmlhttp.open("POST","oferty.php",true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send(url);
}

function sprawdz(offers,index){
	var licznik = document.getElementById("licznik");
    licznik.value = index;
    if(offers.length > index){
        if (window.XMLHttpRequest) {xmlhttp=new XMLHttpRequest();} else {xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
        xmlhttp.onreadystatechange=function() {
            if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                var odpowiedz = JSON.parse(xmlhttp.responseText);
                //console.log(odpowiedz);
                var i = 0;
                while (i < odpowiedz.quotes.length){
                    if (odpowiedz.quotes[i].type == "INEFFECTIVE_LISTING_FEE"){
                        console.log(odpowiedz.quotes[i].offer.id);
                        console.log(odpowiedz.quotes[i].nextDate);
                    }
                    //console.log(odpowiedz.quotes[i]);
                    i++;
                }
                index++;
                sprawdz(offers,index);
            }
        }
        var url = "&offerid="+offers[index].id;
        xmlhttp.open("POST","sprawdz.php",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(url);
    }
}

</script>