<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="author" content="Maciej Krupiński">
</head>
<body id="body">
<input id="kod" onkeyup="szukaj()" type="text" placeholder="kod"><input placeholder="czego szukasz" type="text" id="czegoszukasz" disabled="disabled">
<input id="check" onclick="checkall()" type="button" value="Zaznacz wszystkie">
<ol id="lista">
</ol>
</body>
</html>

<script type="text/javascript">
function checkall() {
    var checks = document.getElementsByClassName('checks');
    var checkbutton = document.getElementById('check');
    if (checkbutton.value == "Zaznacz wszystkie"){
        for (i=0;i<checks.length;i++){
            checks[i].checked=true;
        }
        checkbutton.value = "Odznacz wszystkie";
    }else{
        for (i=0;i<checks.length;i++){
            checks[i].checked=false;
        }
        checkbutton.value = "Zaznacz wszystkie";
    }
}
function szukaj(){
	var lista = document.getElementById("lista");
    var kod = document.getElementById("kod");
    var czegoszukasz = document.getElementById("czegoszukasz");
    if (kod.value.length > 3){
        lista.innerHTML = '';
        if (window.XMLHttpRequest) {xmlhttp=new XMLHttpRequest();} else {xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
        xmlhttp.onreadystatechange=function() {
            if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                var odpowiedz = JSON.parse(xmlhttp.responseText);
                console.log(odpowiedz);
                if (odpowiedz.name != null){
                    czegoszukasz.value=odpowiedz.name;
                    var li = document.createElement("li");
                    if (odpowiedz.availability_id != 1){
                        var namcode = '(sklep) '+odpowiedz.name+' ['+odpowiedz.code+']';
                        var tyt = document.createTextNode(namcode);
                        var checkbox = document.createElement('input');
						checkbox.type = "checkbox";
						checkbox.className = "checks";
						checkbox.value = odpowiedz.code;
						checkbox.id = odpowiedz.code;
                        li.appendChild(checkbox);
                        li.appendChild(tyt);
                        lista.appendChild(li);
                    }
                }
                isatszukaj();
            }
        }
        var url = "&kod="+kod.value;
        xmlhttp.open("POST","sklep.php",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(url);
    }
}
function isatszukaj(){
	var lista = document.getElementById("lista");
    var kod = document.getElementById("kod");
    if (kod.value.length > 3){
        if (window.XMLHttpRequest) {xmlhttp=new XMLHttpRequest();} else {xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
        xmlhttp.onreadystatechange=function() {
            if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                var odpowiedz = JSON.parse(xmlhttp.responseText);
                console.log(odpowiedz);
                if (odpowiedz.name != null){
                    var li = document.createElement("li");
                    if (odpowiedz.availability_id != 1){
                        var namcode = '(isat) '+odpowiedz.name+' ['+odpowiedz.code+']';
                        var tyt = document.createTextNode(namcode);
                        var checkbox = document.createElement('input');
						checkbox.type = "checkbox";
						checkbox.className = "checks";
						checkbox.value = odpowiedz.code;
						checkbox.id = odpowiedz.code;
                        li.appendChild(checkbox);
                        li.appendChild(tyt);
                        lista.appendChild(li);
                    }
                }
                alleszukaj();
            }
        }
        var url = "&kod="+kod.value;
        xmlhttp.open("POST","sklep.php",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(url);
    }
}
function alleszukaj(){
	var lista = document.getElementById("lista");
    var kod = document.getElementById("kod");
    if (kod.value.length > 3){
        if (window.XMLHttpRequest) {xmlhttp=new XMLHttpRequest();} else {xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
        xmlhttp.onreadystatechange=function() {
            if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                var odpowiedz = JSON.parse(xmlhttp.responseText);
                console.log(odpowiedz);
                if(odpowiedz.length>0){
                    for(i=0;i<odpowiedz.length;i++){
                        var li = document.createElement("li");
                        var tytext = "("+odpowiedz[i].id+") "+odpowiedz[i].name;
                        if (odpowiedz[i].external != null){
                            tytext = tytext+' ['+odpowiedz[i].external.id+']';
                        }
                        var tytulaukcji = document.createTextNode(tytext);
                        var checkbox = document.createElement('input');
						checkbox.type = "checkbox";
						checkbox.className = "checks";
						checkbox.value = odpowiedz[i].id;
						checkbox.id = odpowiedz[i].id;
                        li.appendChild(checkbox);
                        li.appendChild(tytulaukcji);
                        lista.appendChild(li);
                    }
                }
            }
        }
        var url = "&kod="+kod.value;
        xmlhttp.open("POST","allegro.php",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(url);
    }
}
</script>