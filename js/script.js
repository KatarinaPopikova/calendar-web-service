function checkValidDate(date){
    let listOfDays = [31,29,31,30,31,30,31,31,30,31,30,31];
    let arrayDate = date.split(".");
    try {

        if(listOfDays[parseInt(arrayDate[1])-1] >= parseInt(arrayDate[0])){
            if(arrayDate.length<3)
                date += ".";
            return date;
        }
        else
            return "false";

    }
    catch {
        return "false";

    }



}

function showOutput(array, element){

    $(element).empty();
    try {
        $(array).each(function( ) {
            $(element).append(this,document.createElement("br")) ;
        });
    }
    catch {
        $(element).text(array);
        $(element).append(document.createElement("br")) ;

    }
}

function getName(){
    let date = checkValidDate($("#fdate").val());
    if(date === "false"){
        $("#show-name" ).text("Nesprávny vstup!");
        return;
    }

    let request = new Request('https://wt144.fei.stuba.sk/strankaZ6/api/'+$("#fcountry").val()+'/date/'+date, {
        method: 'GET',
    });
    fetch(request)
        .then(response => response.json())
        .then(data => {
            if(data.name.length <1)
                $("#show-name" ).text("V tento deň nik neoslavuje meniny");
            else
                showOutput(($(data.name).toArray()), "#show-name");
        });
}

function getDate(){
    let request = new Request('https://wt144.fei.stuba.sk/strankaZ6/api/'+$("#country").val()+'/name/'+$("#fname").val(), {
        method: 'GET',
    });
    fetch(request)
        .then(response => response.json())
        .then(data => {
            if(data.date.length <1)
                $("#show-date" ).text("Toto meno sa nenachádza v kalendári!");
            else
                showOutput(data.date, "#show-date");
        });
}

function getSpeslDays(){
    let request = new Request('https://wt144.fei.stuba.sk/strankaZ6/api/'+$("#spesl-days").val(), {
        method: 'GET',
    });
    fetch(request)
        .then(response => response.json())
        .then(data => {
            if(data.holidays)
                showOutputSpeslDays(data.holidays);
            else
                showOutputSpeslDays(data.memorials);

        });
}

function showOutputSpeslDays(array){
    $("#spesl-days-name").empty();
    $(array).each(function( ) {
        let tr = document.createElement("tr");
        $("#spesl-days-name").append(tr);
        let td1 = document.createElement("td");
        let td2 = document.createElement("td");
        $(td1).text(this.date);
        $(td2).text(this.name);
        $(tr).append(td1,td2);

    });
}


function addName(){
    let date = checkValidDate($("#add-date").val());
    if(date === "false"){
        $("#show-add-name" ).text("Nesprávny vstup!");
        return;
    }
    let udaje = {
        "name": $("#add-name").val(),
        "date": date
    }

    let request = new Request('https://wt144.fei.stuba.sk/strankaZ6/api/create/', {
        method: 'POST',
        body: JSON.stringify(udaje)
    });
    fetch(request)
        .then(response => response.json())
        .then(data => {
            if(data.error)
                $("#show-add-name" ).text(data.error);
            else
                $("#show-add-name" ).text("Meno bolo úspešne pridané :)");
        });

}