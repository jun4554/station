window.onload = function(){

  var request = new XMLHttpRequest();
    var url = 'https://ji-develop.com/station/old/php/API/getStation.php?station_name=東京&pattern_match=1';
    request.open('GET', url, true);
    request.send();
 
    var ret;
    request.onreadystatechange = function(){
        if (request.readyState == 4 && request.status == 200) {

            ret = JSON.parse(request.responseText);
            var aaaa = ret["stationData"].length;
            for (var i = 0; i < ret["stationData"].length; i++) {
                var aaa = ret["stationData"][i]["name"];
            }
        }
    }
}