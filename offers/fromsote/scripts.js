function zmiennazwe(){
    let name = document.getElementById("names")
    name.value = document.getElementById("nameallegro").value;
}


async function getproduct(elem){
    const res = await fetch("./allegrogetproductdata.php", {
        method: "POST",
        body: JSON.stringify({
            ean: elem.value,
        })
    });
	const data = await res.json();
    console.log(data);
}

function getProductFromSote(){
    var code = document.getElementById("code").value;
    var names = document.getElementById("names");
    var nameallegro = document.getElementById("nameallegro");
    var short_description = document.getElementById("short_description");
    var description = document.getElementById("description");
    var price = document.getElementById("price");
    var man_code = document.getElementById("man_code");
    var id = document.getElementById("id");
    var odp = document.getElementById("numbera");
    var stock = document.getElementById("stock");
    var allegrocategory = document.getElementById("allegrocategory");
    
    if(code.length>3){
        var grupa = code.substr(0,2);
        if (grupa == "37"){
            allegrocategory.value = "305589";
        }else if(grupa == "54"){
            allegrocategory.value = "122350";
        }else if(grupa == "44"){
            allegrocategory.value = "304609";
        }else if(grupa == "56"){
            allegrocategory.value = "305293";
        }else if(grupa == "40"){
            allegrocategory.value = "67306";
        }else if(grupa == "07"){
            allegrocategory.value = "111855";
        }else if(grupa == "10"){
            allegrocategory.value = "67306";
        }else if(grupa == "32"){
            allegrocategory.value = "67306";
        }else if(grupa == "15"){
            allegrocategory.value = "49145";
        }else if(grupa == "18"){
            allegrocategory.value = "49146";
        }else if(grupa == "19"){
            allegrocategory.value = "49146";
        }else if(grupa == "87"){
            allegrocategory.value = "63490";
        }else if(grupa == "88"){
            allegrocategory.value = "63638";
        }else if(grupa == "89"){
            allegrocategory.value = "63638";
        }else if(grupa == "20"){
            allegrocategory.value = "111855";
        }else if(grupa == "26"){
            allegrocategory.value = "67378";
        }else if(grupa == "70"){
            allegrocategory.value = "67379";
        }else if(grupa == "13" || grupa == "17"){
            allegrocategory.value = "76362";
        }else if(grupa == "14"){
            allegrocategory.value = "127361";
        }else if(grupa == "21"){
            allegrocategory.value = "15962";
        }else if(grupa == "69"){
            allegrocategory.value = "122403";
        }else if(grupa == "60"){
            allegrocategory.value = "122403";
        }else if(grupa == "45"){
            allegrocategory.value = "67211";
        }else if(grupa == "72"){
            allegrocategory.value = "67355";
        }else if(grupa == "03"){
            allegrocategory.value = "67211";
        }else if(grupa == "75"){
            allegrocategory.value = "67342";
        }else if(grupa == "25"){
            allegrocategory.value = "67288";
        }else if(grupa == "09"){
            allegrocategory.value = "67304";
        }else if(grupa == "34"){
            allegrocategory.value = "68368";
        }else{
            allegrocategory.value = "67346";
        }
        
        odp.innerText = "";
        if (window.XMLHttpRequest) {xmlhttp=new XMLHttpRequest();} else {xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
        xmlhttp.onreadystatechange=function() {
            if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                var odpowiedz = xmlhttp.responseText;
                resp = JSON.parse(odpowiedz);
                console.log(resp);
                productdata = resp.sote;
                fpp = resp.fpp[0];
                names.value = productdata.name;
                short_description.value = '<h2>'+replaceTytul(productdata.name)+'</h2>'+replaceHtml(productdata.short_description);
                description.value = replaceHtml(productdata.description);
                price.value = productdata.price_brutto;
                man_code.value = productdata.man_code;
                id.value = productdata.id;
                stock.value = parseInt(fpp.ilosc);
                if (resp.allegro[0].lastname != ""){
                    nameallegro.value = resp.allegro[0].lastname;
                }

                liczbaznakow();
                podglad();
                allegrocategoryparametry();
            }
        }
        var url = "&code="+code;
        xmlhttp.open("POST","getproductdata.php",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(url);
    }
}

function liczbaznakow(){
    var names = document.getElementById("names");
    var liczbaznakow = document.getElementById("liczbaznakow");
    if (names.value.length > 0){
        liczbaznakow.value = names.value.length;
        if (names.value.length > 50){
            liczbaznakow.style.color = "red";
        }else{
            liczbaznakow.style.color = "green";
        }
    }
}

function replaceTytul(desc){
    desc = desc.replace(/</ig,"&lt;");
    desc = desc.replace(/>/ig,"$gt;");
    desc = desc.replace(/&/ig,"&amp;");
    desc = desc.replace(/'/ig,"&apos;");
    desc = desc.replace(/"/ig,"&quot;");
    return desc.trim();
}

function replaceHtml(desc){
    console.log(desc.split("<br />"));
    desc = desc.replace(/(<(!--[^>]+)>)/ig,"");
    desc = desc.replace(/(<(div[^>]+)>)/ig,"");
    desc = desc.replace(/(<([^>]+)div>)/ig,"");
    desc = desc.replace(/(<(br[^>]+)>)/ig,"");
    desc = desc.replace(/(<(p[^>]+)>)/ig,"<p>");
    desc = desc.replace(/(<([^>]+)div>)/ig,"</p>");
    desc = desc.replace(/(<(span[^>]+)>)/ig,"");
    desc = desc.replace(/(<([^>]+)span>)/ig,"");
    desc = desc.replace(/(<(strong)>)/ig,"<b>");
    desc = desc.replace(/(<([^>]+)strong>)/ig,"</b>");
    desc = desc.replace(/(<(ul[^>]+)>)/ig,"<ul>");
    desc = desc.replace(/(<(li[^>]+)>)/ig,"<li>");
    desc = desc.replace(/(<a([^]+)\/a>)/ig,"");
    desc = desc.replace(/(<img([^]+)>)/ig,"");
    desc = desc.replace(/(<(section[^>]+)>)/ig,"");
    desc = desc.replace(/(<\/section>)/ig,"");
    desc = desc.replace(/(<(em)>)/ig,"");
    desc = desc.replace(/(<([^>]+)em>)/ig,"");
    desc = desc.replace(/^\s+$/mg, "");
    return desc.trim();
}

function podglad(){
    var short_description = document.getElementById("short_description");
    var description = document.getElementById("description");
    var lookshort = document.getElementById("lookshort");
    var lookdiv = document.getElementById("lookdiv");
    lookshort.innerHTML = short_description.value;
    lookdiv.innerHTML = description.value;
}

function createDraft(){
    var code = document.getElementById("code").value;
    var names = document.getElementById("names").value;
    var short_description = document.getElementById("short_description");
    var description = document.getElementById("description");
    var price = document.getElementById("price").value;
    var man_code = document.getElementById("man_code").value;
    var allegrocategory = document.getElementById("allegrocategory").value;
    var id = document.getElementById("id").value;
    var odp = document.getElementById("numbera");
    var stock = document.getElementById("stock").value;

    if (man_code == ""){
        man_code = 0;
    }
    var dataSend = {
        "code": code,
        "allegrocategory": allegrocategory,
        "names": names,
        "short_description": short_description.value,
        "description": description.value,
        "price": price,
        "man_code": man_code,
        "id":id,
        "stock":stock,
    }

    if (window.XMLHttpRequest) {xmlhttp=new XMLHttpRequest();}else{xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            //console.log(xmlhttp.responseText);
            var odpowiedz = JSON.parse(xmlhttp.responseText);
            if (typeof(odpowiedz.errors) != "undefined"){
                for (i = 0; i < odpowiedz.errors.length; i++){
                    if (odpowiedz.errors[i].path.includes("sections[0]")){
                        short_description.style.border = "2px solid red";
                    }
                    else{
                        description.style.border = "2px solid red";
                    }
                    alert(odpowiedz.errors[i].userMessage);
                }
            }else{
                odp.innerHTML = '<a href="https://allegro.pl/offer/'+odpowiedz.id+'/restore">Aukcja '+odpowiedz.id+'</a>';
            }
        }
    }
    const jsonString = JSON.stringify(dataSend);
    xmlhttp.open("POST", "addnewdraft.php");
    xmlhttp.setRequestHeader("Content-Type", "application/json");
    xmlhttp.send(jsonString);
}

function getallegrocategory(){
    var allegrocategory = document.getElementById("allegrocategory");
    console.log(allegrocategory);
    var allegrocategoryname = document.getElementById("allegrocategoryname");
    if(allegrocategory.value.length>3){
        if (window.XMLHttpRequest) {xmlhttp=new XMLHttpRequest();} else {xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
        xmlhttp.onreadystatechange=function() {
            if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                var odpowiedz = xmlhttp.responseText;
                resp = JSON.parse(odpowiedz);
                var sciezkacat = '';
                for (var i=0; i < resp.length; i++){
                    if (sciezkacat != ''){
                        sciezkacat = " -> "+sciezkacat;
                    }
                    sciezkacat = resp[i].name+sciezkacat;
                }
                console.log(sciezkacat);
                allegrocategoryname.value = sciezkacat;
            }
        }
        var url = "&id="+allegrocategory.value;
        xmlhttp.open("POST","allegrocategory.php",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(url);
    }
}

function allegrocategoryparametry(){
    var allegrocategory = document.getElementById("allegrocategory");
    var allegroparametry = document.getElementById("allegroparametry");
    allegroparametry.innerHTML = '';
    if(allegrocategory.value.length>3){
        if (window.XMLHttpRequest) {xmlhttp=new XMLHttpRequest();} else {xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
        xmlhttp.onreadystatechange=function() {
            if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                var odpowiedz = xmlhttp.responseText;
                resp = JSON.parse(odpowiedz);
                parametry = resp.parameters;
                console.log(parametry);
                var inserthtmlparam = '';
                for (var i=0; i < parametry.length; i++){
                    typ = "input";
                    if (parametry[i].type == "dictonary"){
                        typ = "select";
                    }
                    var pch = document.createElement(typ);
                    pch.type = parametry[i].type;
                    pch.required = parametry[i].required;
                    pch.name = parametry[i].name;
                    pch.placeholder = parametry[i].name;
                    pch.id = parametry[i].id;
                    allegroparametry.appendChild(pch);
                }
                getallegrocategory();
            }
        }
        var url = "&id="+allegrocategory.value;
        xmlhttp.open("POST","allegrocategoryparametry.php",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(url);
    }
}