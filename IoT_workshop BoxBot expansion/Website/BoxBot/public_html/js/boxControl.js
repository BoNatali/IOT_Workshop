//http://stackoverflow.com/questions/8886248/add-image-to-page-onclick

var recordedDirections = "";
var speed = 25; //25 out of 255
var time = 500; //in ms
var devider = ';';  //looks like "forward;25;500;left;25;500;"
var formattedDirection = "";    //direction after formatting
var num = 0; //is used for the commands array, gives the images a unique id for removal
var recordedDirections = "";
var directionArray = new Array();


function addDirection(direction) {
    console.log(direction);
    formattedDirection = direction + devider + speed + devider + time + devider; // so it will look like "forward+25+500;left+25+500;" or "direction+speed+time;"
    directionArray[num] = formattedDirection;
    console.log(directionArray);

    //num + 1 after everything is done
    num = num + 1;

    //add image
    var img = document.createElement("img");

    switch (direction) {
        case 'forward':
            img.src = "resources/directions/up.png";
            break;
        case 'left':
            img.src = "resources/directions/left.png";
            break;
        case 'right':
            img.src = "resources/directions/right.png";
            break;
            //bot can't go backwards at the moment
//        case 'backward':
//            img.src = "resources/directions/down.png";
//            break;
    }
    img.id = "directionImage" + num; //give the image an unique id for removal
    img.height = 50;
    img.width = 50;
    document.getElementById("directionsField").appendChild(img); //append image to div
}

function erase() {
    if (num > 0) {//prevents num from going negative, otherwise the array will be negative
        //command removal
        delete directionArray[num - 1]; //minus 1 becaus the array is incremented after filling an element

        //image removal
        $("#directionImage" + num).remove();// num causes the last added image to be removed instead of the first

        num = num - 1; //prepare num to delete the second last image

        console.log(directionArray);
    }
}

function go() {//gives directons to the bot 
    var recordedDirections = "";
    for (var i = 0; i <= num; i++) {//add all the array entries together into one string to be sent
        if (directionArray[i] != null) {//prevents adding empty array entries
            recordedDirections = recordedDirections + directionArray[i];
        }
    }
    console.log(recordedDirections);

    if (directionArray[0] != null) {//if the first enry is not empty

        //post recordedDirections to wilco's api and then to the nodeMCU, FIRST! Set device configuration (api url with t=sdc)
        urlSDC = "http://thingscon16.futuretechnologies.nl/api.php?t=sdc&d=FF6E&td=FF6E&c=&m=" + recordedDirections;
        successSDC = console.log("go SDC post success");
        ajaxPost(urlSDC, successSDC);//ajaxPost, see ajaxPost function 

        //SECONDLY the Set query item (api url with t=sqi)
        urlSQI = "http://thingscon16.futuretechnologies.nl/api.php?t=sqi&d=FF6E";
        successSQI = console.log("go SQI post success");
        ajaxPost(urlSQI, successSQI);//ajaxPost, see ajaxPost function 

        //change button to stop button
        document.getElementById("goButton").className = 'btn btn-danger controllerButton'; //change class to change color
        document.getElementById("goButton").setAttribute("onClick", "stop();"); //change onclick event to stop to run stop at the next click
        document.getElementById("goButton").id = 'stopButton'; //change id to use js on the stop button instead of the go button
        document.getElementById("goButtonGlyphicon").className = 'glyphicon glyphicon-stop'; //change glypicon to stop
    }
}

function stop() {//to stop the bot

    //post stop to wilco's api and then to the nodeMCU, FIRST! Set device configuration (api url with t=sdc)
    urlSDC = "http://thingscon16.futuretechnologies.nl/api.php?t=sdc&d=FF6E&td=FF6E&c=&m=stop";
    successSDC = console.log("stop SDC post success");
    ajaxPost(urlSDC, successSDC);//ajaxPost, see ajaxPost function 

    //SECONDLY the Set query item (api url with t=sqi)
    urlSQI = "http://thingscon16.futuretechnologies.nl/api.php?t=sqi&d=FF6E";
    successSQI = console.log("stop SQI post success");
    ajaxPost(urlSQI, successSQI);//ajaxPost, see ajaxPost function 

    //change button to goButton
    document.getElementById("stopButton").className = 'btn btn-success controllerButton'; //change class to change color
    document.getElementById("stopButton").setAttribute("onClick", "go();"); //change onclick event to go to run go at the next click
    document.getElementById("stopButton").id = 'goButton'; //change id to use js on the go button instead of the stop button
    document.getElementById("goButtonGlyphicon").className = 'glyphicon glyphicon-play-circle'; //change glypicon to go
}

function goCode() { //gives directons to the bot using code
    var code;
    var val = document.getElementById("codeArea").value; //retrieves string,
    if (val != "") { //if textarea is not empty
        val = val.replace(/\s+/g, '');//remove spaces http://stackoverflow.com/questions/5963182/how-to-remove-spaces-from-a-string-using-javascript
        code = val;

        console.log(code); //logs the code

        document.getElementById("goButton").className = 'btn btn-danger controllerButton'; //change class to change color
        document.getElementById("goButton").setAttribute("onClick", "stopCode();"); //change onclick event to stop to run stop at the next click
        document.getElementById("goButton").id = 'stopButton'; //change id to use js on the stop button instead of the go button
        document.getElementById("goButtonGlyphicon").className = 'glyphicon glyphicon-stop'; //change glypicon to stop

        //post recordedDirections to wilco's api and then to the nodeMCU, FIRST! Set device configuration (api url with t=sdc)
        urlSDC = "http://thingscon16.futuretechnologies.nl/api.php?t=sdc&d=FF6E&td=FF6E&c=&m=" + code;
        successSDC = console.log("go SDC post success");
        ajaxPost(urlSDC, successSDC);//ajaxPost, see ajaxPost function 

        //SECONDLY the Set query item (api url with t=sqi)
        urlSQI = "http://thingscon16.futuretechnologies.nl/api.php?t=sqi&d=FF6E";
        successSQI = console.log("go SQI post success");
        ajaxPost(urlSQI, successSQI);//ajaxPost, see ajaxPost function 
    }
}

function stopCode() {//to stop the bot using code

    //post stop to wilco's api and then to the nodeMCU, FIRST! Set device configuration (api url with t=sdc)
    urlSDC = "http://thingscon16.futuretechnologies.nl/api.php?t=sdc&d=FF6E&td=FF6E&c=&m=stop";
    successSDC = console.log("stop SDC post success");
    ajaxPost(urlSDC, successSDC);//ajaxPost, see ajaxPost function 

    //SECONDLY the Set query item (api url with t=sqi)
    urlSQI = "http://thingscon16.futuretechnologies.nl/api.php?t=sqi&d=FF6E";
    successSQI = console.log("stop SQI post success");
    ajaxPost(urlSQI, successSQI);//ajaxPost, see ajaxPost function 

    //change button to goButton
    document.getElementById("stopButton").className = 'btn btn-success controllerButton'; //change class to change color
    document.getElementById("stopButton").setAttribute("onClick", "goCode();"); //change onclick event to go to run go at the next click
    document.getElementById("stopButton").id = 'goButton'; //change id to use js on the go button instead of the stop button
    document.getElementById("goButtonGlyphicon").className = 'glyphicon glyphicon-play-circle'; //change glypicon to go
}

function ajaxPost(url, success) { //posts the data to an url with a success log
    $.ajax({
        type: "POST",
        url: url,
        success: success
    });
}