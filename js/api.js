$(function() {
  //  クリックイベントにajax処理を登録する

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

  $('body').on('click', 'button[data-btn-type=ajax]', function(e) {
    console.log('click btn');
    //  リクエストの下準備
    //  リクエスト時に一緒に送るデータの作成
    var send_data;
    send_data = {
      //  テキストボックスの値を設定
      prefecture_no : $('input').val()
    };
    console.log(send_data);
    //  WebAPIを叩く
    $.ajax({
      //  リクエストの内容
      url: 'php/API/getPrefectureName.php',
      dataType: "json",
      data: send_data,
      //  レスポンス成功時の処理
      success: function(responce) {
        if (responce.result === "OK") {
          console.log(responce);
          $('div[data-result=""]').html(JSON.stringify(responce));
        } else {
          console.log(responce);
          $('div[data-result=""]').html(JSON.stringify(responce));
        }
        return false;
      },
      //  レスポンス失敗時の処理
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        console.log(XMLHttpRequest);
        console.log(textStatus);
        console.log(errorThrown);
        $('div[data-result=""]').html(JSON.stringify("データ取得中にエラーが発生しました。"));
        return false;
      }
    });
    //  フォーカスをテキストボックスに合わせる
    $('input').focus();

    return false;
  });
});