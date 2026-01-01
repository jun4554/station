var map;
var sidebar;
var prefectureArray = [];
var prefixArray = [];
var prefectureIdArray = [];
var lineIdArray = [];
var isSearch = false;
var devFlg = false;
var crossMarker;
var showNum = 200;

const SMART_PHONE_MAX_WIDTH = 768;

// アイコンサイズの設定など
var LeafIcon = L.Icon.extend({
    options: {
        iconSize: [41, 41],
        iconAnchor: [10, 41],
        popupAnchor: [3, -40]
    }
});

// 利用するアイコンの種類とURL
var purpleIcon = new LeafIcon({iconUrl: 'img/marker/Purple.png'});
var redIcon = new LeafIcon({iconUrl: 'img/marker/Red.png'});
var brownIcon = new LeafIcon({iconUrl: 'img/marker/Brown.png'});
var yellowIcon = new LeafIcon({iconUrl: 'img/marker/Yellow.png'});
var greenIcon = new LeafIcon({iconUrl: 'img/marker/Green.png'});
var defaultIcon = new LeafIcon({iconUrl: 'img/marker/Default.png'});
var aliceBlueIcon = new LeafIcon({iconUrl: 'img/marker/AliceBlue.png'});
var silverIcon = new LeafIcon({iconUrl: 'img/marker/Silver.png'});

var markerLayer;

window.onload = async function(){

    (adsbygoogle = window.adsbygoogle || []).push({});

    var isShowZoomSlider = true;
    var isShowZoomControl = false;
    if ($(window).width() < SMART_PHONE_MAX_WIDTH) {
        isShowZoomSlider = false;
        isShowZoomControl  = true;
    }

    var lat = Cookies.get('lat');
    var lng = Cookies.get('lng');
    var zoom = Cookies.get('zoom');

    if (lat == undefined || lng == undefined || zoom == undefined) {
        lat = 35.68106522586127;
        lng = 139.7671222686768;
        zoom = 15;
    }

    var aryLatLng = [];
    aryLatLng.push(lat);
    aryLatLng.push(lng);

    // 東京駅を中心に地図を描画
    map = L.map('mapid', {
        center: aryLatLng,
        zoom: zoom,
        minZoom: 7,
        zoomsliderControl: isShowZoomSlider,
        zoomControl: isShowZoomControl,
        messagebox: true
    });
    // OpenStreetMap から地図画像を読み込む
    var tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution: '? <a href="http://osm.org/copyright">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',});
    tileLayer.addTo(map);

    // サイドバー表示
    sidebar = L.control.sidebar('sidebar', {position: 'right'}).addTo(map);
    
    $('#searchText').keypress(function (e) {
        if (e.which == 13) {
            search();
	}
    });

    $('#passengerTextFrom').keypress(function (e) {
        if (e.which == 13) {
            passengerTextChange();
	}
    });
    $('#passengerTextTo').keypress(function (e) {
        if (e.which == 13) {
            passengerTextChange();
	}
    });

    // 都道府県を取得
    await getPrefecture();

    // 路線一覧取得
    await getLine();

    // 都道府県チェックボタン処理
    $(document).on('change', 'input[name^="prefectureCheckBox"]', function() {
        afterPrefectureCheck();
    });

    // 各路線全チェックボタン処理
    $(document).on('change', 'input[name^="allCheckBox"]', function() {
        if($(this).prop('checked')){
            $('[name*="' + $(this).val() + '"]').prop('checked', true);
        } else {
            $('[name*="' + $(this).val() + '"]').prop('checked', false);
        }
        afterLineCheck();
    });

    // 路線チェックボタン処理
    $(document).on('change', 'input[name^="lineCheckBox"]', function() {
        
        var checkBoxName = $(this).attr('name');
        checkBoxName = checkBoxName.replace('lineCheckBox', 'allCheckBox');

        if($(this).prop('checked')){
            // 子要素全てにチェックが付いたら親チェックも付ける
            var classLength = $('[name="' + $(this).attr("name") + '"]').length;
            var classCheckLength = $('[name="' + $(this).attr("name") + '"]:checked').length;
            if (classLength == classCheckLength) {
                $('[name="' + checkBoxName + '"]').prop('checked', true);
            }
        } else {
            // 子要素のチェックが1つでも外れたら親チェックも外す
            $('[name="' + checkBoxName + '"]').prop('checked', false);
        }
        afterLineCheck();
    });

    // 乗降客数スライドバー
    $('#range-slider').jRange({
        from: 0,
        to: 600000,
        step: 1000,
        scale: [0,150000,300000,450000,600000],
        format: '%s 人',
        width: 300,
        theme: 'theme-blue',
        showLabels: true,
        isRange : true,
        ondragend: function(){
            sliderChange();
        },
        onbarclicked: function(){
            sliderChange();
        }
    });
    $('#passengerTextFrom').val('0');
    $('#passengerTextTo').val('600000');

    // 非公表の駅も表示するチェック時処理
    $('[name="nonPublicCheckBox"]').change(function(){
        setMarker();
    });

    // ウィンドウリサイズ時に選択中の都道府県表示を更新
    var resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            updateSelectedPrefectureDisplay();
        }, 500);
    });
    
    // 現在地ボタン表示
    L.Control.CurrentMark = L.Control.extend({
        onAdd: function(map) {
            var img = L.DomUtil.create('img');
            img.src = 'img/current.png';
            img.style ="border:solid 1px #d3d3d3; cursor: pointer;";
            img.addEventListener('click', ()=> {
                this.fire('currentPos');
            });
            this.img
            return img;
        }
    });
    L.extend(L.Control.CurrentMark.prototype, L.Evented.prototype);
    var mark = new L.Control.CurrentMark({position: 'bottomleft'}).addTo(map);
    mark.on('currentPos', function() {
        getgeo();
    })
    
    // 移動後に表示地図内のマーカーをセット
    map.on('moveend', function() {
        setMarker();

        Cookies.set('lat', map.getCenter().lat, {expires: 30});
        Cookies.set('lng', map.getCenter().lng, {expires: 30});
        Cookies.set('zoom', map.getZoom(), {expires: 30});
        
        if (devFlg) {
            if(navigator.clipboard){
                navigator.clipboard.writeText("" + map.getCenter().lat + map.getCenter().lng);
            }
        }
    });

    await getLinePrefix();
    await setMarker();

    map.on('move', function(e) {
        if (devFlg) {
            crossMarker.setLatLng(map.getCenter());
        }
    });
    
    $(window).keydown(function(e){
        if(event.ctrlKey){
            if(e.keyCode === 118){
                // 十字マークを地図中央に表示
                var crossIcon = L.icon({
                    iconUrl: 'https://maps.gsi.go.jp/image/map/crosshairs.png',
                    iconSize: [32, 32], 
                    iconAnchor: [16, 16] 
                });

                crossMarker = L.marker(map.getCenter(),{
                    icon:crossIcon,  
                    zIndexOffset:1000, 
                    interactive:false 
                }).addTo(map);

                devFlg = true;
                return false;
            }
        }
    });

    // 表示駅数の変更処理
    $('input[name="showNum"]').change(function () {
        showNum = $('input:radio[name="showNum"]:checked').val();
        setMarker();
    });
}

// 都道府県を取得
function getPrefecture() {
    var request = new XMLHttpRequest();
    var url = 'https://ji-develop.com/station/old/php/getPrefecture.php';
    request.open('GET', url, true);
    request.send();
 
    var ret;
    request.onreadystatechange = function(){
        if (request.readyState == 4 && request.status == 200) {

            prefectureArray = JSON.parse(request.responseText);

            var preRegionName = "";
            for (var i = 0; i < prefectureArray.length; i++) {
                var prefectureId = prefectureArray[i].prefecture_id;
                var prefectureName = prefectureArray[i].name;
                var regionName = prefectureArray[i].region_name;
                if (preRegionName != regionName) {
                    $('#prefectureList').append('<p><strong>' + regionName + '</strong></p>');
                    preRegionName = regionName;
                }
                var elements = '<input type="checkbox" name="prefectureCheckBox" value="' + prefectureId + '">' + prefectureName;
                $('#prefectureList').append($('<label class="prefectureListName" />').append(elements));
            }
        }
    }
}

// 絞込み用の路線を取得
function getLine() {
    var request = new XMLHttpRequest();
    var url = 'https://ji-develop.com/station/old/php/getLine.php';
    request.open('GET', url, true);
    request.send();
 
    var ret;
    request.onreadystatechange = function(){
        if (request.readyState == 4 && request.status == 200) {
 
            var genericTermArray = [];

            ret = JSON.parse(request.responseText);

            var otherPrefectureId;
            for (var i = 0; i < ret.length; i++) {
                var lineId = ret[i].id;
                var lineName = ret[i].name;
                var genericTermId = ret[i].genericTermId;
                var genericTermName = ret[i].genericTermName;
                var prefix = ret[i].prefix;
                if (!genericTermArray.includes(genericTermId)) {
                    if (i != 0) $('#lineList').append('<div class="term-divider"></div>');
                    $('#lineList').append('<p class="all-checkbox-wrapper"><label class="all-checkbox-label"><input type="checkbox" name="allCheckBox' + genericTermId + '" value="' + genericTermId + '"><strong>' + genericTermName + '</strong></label></p>' );
                    $('#lineList').append('<div class="line-break"></div>');
                    genericTermArray.push(genericTermId);
                }
                var elements = '<input type="checkbox" name="lineCheckBox' + genericTermId + '" value="' + lineId + '">' + lineName.replace(prefix, "");
                if (genericTermId == '90000') {
                    if (otherPrefectureId != lineId.substr(1, 2)){
                        otherPrefectureId = lineId.substr(1, 2);
                        var prefecture = prefectureArray.filter(e => e.prefecture_id === parseInt(otherPrefectureId, 10))[0];
                        $('#lineList').append('<p><strong>' + prefecture.name + '</strong></p>');
                    }
                }
                $('#lineList').append($('<label class="lineListName" />').append(elements));
            }
        }
    }
}

// 路線名の削除Prefixを取得
function getLinePrefix() {
    var request = new XMLHttpRequest();
    var url = 'https://ji-develop.com/station/old/php/getLinePrefix.php';
    request.open('GET', url, true);
    request.send();
 
    request.onreadystatechange = function(){
        if (request.readyState == 4 && request.status == 200) {
            prefixArray = JSON.parse(request.responseText);
        }
    }
}

// 地図にマーカーをセット
function setMarker() {

    var tooltipFontSize = '100';
    if (map.getZoom() <= 12) {
        tooltipFontSize = '60';
    } else if (map.getZoom() == 13) {
        tooltipFontSize = '70';
    } else if (map.getZoom() == 14) {
        tooltipFontSize = '80';
    } else if (map.getZoom() == 15) {
        tooltipFontSize = '90';
    }

    var request = new XMLHttpRequest();
    var url = [
        'https://ji-develop.com/station/old/php/getStation.php',
        '?east=' + map.getBounds().getEast(),
        '&west=' + map.getBounds().getWest(),
        '&south=' + map.getBounds().getSouth(),
        '&north=' + map.getBounds().getNorth(),
        '&passengerFrom=' + $('#passengerTextFrom').val(),
        '&passengerTo=' + $('#passengerTextTo').val(),
        '&showNonPublic=' + $('#nonPublicCheckBox').prop('checked')
    ].join('');
    if (prefectureIdArray.length != 0) {
        url += '&prefectureIdArray=' + prefectureIdArray;
    }
    if (lineIdArray.length != 0) {
        url += '&lineIdArray=' + lineIdArray;
    }
    request.open('GET', url, true);
    request.send();

    var ret;
    request.onreadystatechange = function(){
        if (request.readyState == 4 && request.status == 200) {

            // 表示中のマーカーがあれば削除
            if (markerLayer != undefined) {
                map.removeLayer(markerLayer);
                $('#list').empty();
            }

            ret = JSON.parse(request.responseText);

            // showFlgが1のレコード数のみ抽出
            var filterRet = $.grep(ret,
                function(station) {
                    // showFlgプロパティの値でフィルター
                    return (station.showFlg == 1);
                }
            );
            
            // 表示マーカーの数が設定値を超える場合はメッセージを表示
            if (filterRet.length > showNum) {
                $('#dialog').fadeIn("normal", function () {
                    //コールバックで2秒後にフェードアウト	
                   $(this).delay(2000).fadeOut("normal");
                });
                return;
            }

            if (ret.length == 0) {
                $('#list').append('<p>データはありません</p>');
            }

            var markers = [];
            var lineNameArray =[];
            for (var i = 0; i < ret.length; i++) {
                var station = {
                    "lineId": ret[i].line_id,
                    "lineName": ret[i].lineName,
                    "lineName2": ret[i].lineName2,
                    "lineName3": ret[i].lineName3,
                    "lineName4": ret[i].lineName4,
                    "lineName5": ret[i].lineName5,
                    "lineName6": ret[i].lineName6,
                    "lineName7": ret[i].lineName7,
                    "lineName8": ret[i].lineName8,
                    "lineName9": ret[i].lineName9,
                    "Name": ret[i].stationName,
                    "KanaName": ret[i].stationKanaName,
                    "passenger": ret[i].passenger,
                    "remarks": ret[i].remarks,
                    "year": ret[i].year,
                    "lat": ret[i].lat,
                    "lng": ret[i].lng,
                    "sourceUrl": ret[i].sourceUrl,
                    "showFlg": ret[i].showFlg
                };
                
                if (station.showFlg == 1) {
                
                    // ポップアップのオプション設定
                    var popup = L.responsivePopup({
                        autoPan: false,
                        autoClose: false
                    });

                    // ポップアップの内容設定
                    var contents = "<span class='lineName'>";
                    contents += station.lineName;
                
                    if (station.lineName2 != null) {
                        $.each(prefixArray, function(index, prefix) {
                           station.lineName2 = station.lineName2.replace(prefix, "");
                        });
                        contents += "、" + station.lineName2;
                    }
                    if (station.lineName3 != null) {
                        $.each(prefixArray, function(index, prefix) {
                            station.lineName3 = station.lineName3.replace(prefix, "");
                        });
                        contents += "、" + station.lineName3;
                    }
                    if (station.lineName4 != null) {
                        $.each(prefixArray, function(index, prefix) {
                            station.lineName4 = station.lineName4.replace(prefix, "");
                        });
                        contents += "、" + station.lineName4;
                    }
                    if (station.lineName5 != null) {
                        $.each(prefixArray, function(index, prefix) {
                            station.lineName5 = station.lineName5.replace(prefix, "");
                        });
                        contents += "<br>" + station.lineName5;
                    }
                    if (station.lineName6 != null) {
                        $.each(prefixArray, function(index, prefix) {
                            station.lineName6 = station.lineName6.replace(prefix, "");
                        });
                        contents += "、" + station.lineName6;
                    }
                    if (station.lineName7 != null) {
                        $.each(prefixArray, function(index, prefix) {
                            station.lineName7 = station.lineName7.replace(prefix, "");
                        });
                        contents += "、" + station.lineName7;
                    }
                    if (station.lineName8 != null) {
                        $.each(prefixArray, function(index, prefix) {
                            station.lineName8 = station.lineName8.replace(prefix, "");
                        });
                        contents += "、" + station.lineName8;
                    }
                    if (station.lineName9 != null) {
                        $.each(prefixArray, function(index, prefix) {
                            station.lineName9 = station.lineName9.replace(prefix, "");
                        });
                        contents += "<br>" + station.lineName9;
                    }
                    contents += "</span>";
                    if (station.KanaName != null) {
                        contents += "<h3><ruby>" + station.Name + "<rp>(</rp><rt>" + station.KanaName + "</rt><rp>)</rp></ruby></h3>";
                    } else {
                        contents += "<h3>" + station.Name + "</h3>";
                    }
                    contents += "乗降客数:" + (station.passenger == -1 ? '-' : Number(station.passenger).toLocaleString()) + "人/日";
                    if (station.remarks != null) {
                        contents +=" " + station.remarks;
                    }
                    contents += "<br>";
                    if (station.year != "") contents += "統計年度:" + station.year + "年<br>";
                    if (station.sourceUrl == "") {
                        if (station.passenger != -1) {
                            contents += "<span class='remarks'>出典:<a href='https://ja.wikipedia.org/wiki/" + station.Name + "駅' target='_blank' rel='noopener noreferrer'>https://ja.wikipedia.org/wiki/" + station.Name + "駅</a></span>";
                        }
                    } else {
                        contents += "<span class='remarks'>出典:<a href='" + station.sourceUrl + "' target='_blank' rel='noopener noreferrer'>" + station.sourceUrl + "</a></span>";
                    }
                    popup.setContent(contents);

                    // アイコンの設定
                    var icon;
                    switch (true) {
                        case Number(station.passenger) >= 300000:
                            icon = purpleIcon;
                            break;
                        case Number(station.passenger) >= 100000:
                            icon = redIcon;
                            break;
                        case Number(station.passenger) >= 50000:
                            icon = brownIcon;
                            break;
                        case Number(station.passenger) >= 30000:
                            icon = yellowIcon;
                            break;
                        case Number(station.passenger) >= 10000:
                            icon = greenIcon;
                            break;
                        case Number(station.passenger) >= 5000:
                            icon = defaultIcon;
                            break;
                        case Number(station.passenger) >= 1000:
                            icon = aliceBlueIcon;
                            break;
                        default:
                            icon = silverIcon;
                    }

                    var marker = L.marker([station.lat,station.lng], {icon: icon/*, tags: tagArray*/}).bindPopup(popup);
                    if (filterRet.length > 1) {
                        marker.bindTooltip("<span style='font-size: " + tooltipFontSize + "%;'>" + station.Name + "</span>",{direction: 'center',permanent: true});
                    }
                    markers.push(marker);
                }
    
                // 一覧
                if (lineIdArray.length == 0 || lineIdArray.includes(station.lineId)) {
                    if (!lineNameArray.includes(station.lineName)) {
                        $('#list').append('<p class="line-header"><strong>' + station.lineName + '</strong></p>');
                        lineNameArray.push(station.lineName);
                    }
                    var elements;
                    if (station.KanaName != null) {
                        elements = '<p class="station-item"><ruby>' + station.Name + "<rp>(</rp><rt>" + station.KanaName + "</rt><rp>)</rp></ruby>";
                    } else {
                        elements = '<p class="station-item">' + station.Name;
                    }
                    if (station.passenger != -1) {
                        elements += '&nbsp;<span class="lineListName">' + Number(station.passenger).toLocaleString() + '人/日</span>';
                    } else {
                        elements += '&nbsp;<span class="lineListName">-人</span>';
                    }
                    //if (station.lineName2 != null) {
                    //    elements += '&nbsp;<span class="lineListName">［他路線有り］</span>';
                    //}
                    elements += '</p>';
                    $('#list').append(elements);
                }
            }
            // 再表示の際に一括削除出来るようレイヤーグループに設定
            markerLayer = L.layerGroup(markers);
            markerLayer.addTo(map);

            if (filterRet.length == 1) {
                // ポップアップをオープン状態にする
                markerLayer.eachLayer(function(marker){
                    marker.openPopup();
                });
            }
        }
    }
}

// 現在地表示
function getgeo() {
    map.on('locationerror', onLocationError);
    map.locate({
        setView: "true",
        zoom: map.getZoom()
    });
}

function onLocationError(e) {
    alert(e.message);
}

// 駅名検索処理
function search() {

    if (isSearch) return;

    $('#searchStationList').empty();
    $("#searchError").css('display', 'none');

    const regEx = new RegExp("駅$");
    var searchText;
    if (regEx.test($("#searchText").val())) {
        searchText = $("#searchText").val().replace("駅", "");
    } else {
        searchText = $("#searchText").val();
    }
    if (searchText == "") return;
    
    var request = new XMLHttpRequest();
    var url = 'https://ji-develop.com/station/old/php/getLatLng.php?searchText=' + searchText;
    request.open('GET', url, true);
    isSearch = true;
    request.send();
 
    var ret;
    request.onreadystatechange = function(){
        if (request.readyState == 4 && request.status == 200) {
 
            ret = JSON.parse(request.responseText);
            if(ret.length == 0) {
                $("#searchError").css('display', 'inline');
                isSearch = false;
                return;
            }
            for (var i = 0; i < ret.length; i++) {
                var stationName = ret[i].name;
                var stationKanaName = ret[i].kana_name;
                var prefectureName = ret[i].prefectureName;
                var lineName = ret[i].lineName;
                var lineName2 = ret[i].lineName2;
                if (lineName2 != null) {
                    lineName += '&nbsp;他';
                }
                var latlng = [ret[i].lat, ret[i].lng].join(',');
                var link;
                if (stationKanaName != null) {
                    link = '&emsp;<ruby>' + stationName + "<rp>(</rp><rt>" + stationKanaName + "</rt><rp>)</rp></ruby>";
                } else {
                    link = '&emsp;' + stationName;
                }
                link += '（' + prefectureName + '-' + lineName + '）';
                $('#searchStationList').append('<p><button class="link-style-btn" onclick="onMoveStation(\'' + latlng + '\');">' + link + '</button></p>');
            }
            
            // キーボードを閉じる
            $("#searchText").blur();

            isSearch = false;
        }
    }
}

// 選択した駅へ飛ぶ
function onMoveStation(latlng) {
    var aryLatLng = latlng.split(',');
    map.setView(aryLatLng, 17);
    // サイドバーを閉じる
    if ($(window).width() < SMART_PHONE_MAX_WIDTH) {
        sidebar.close();
    }
}

// 都道府県チェック後処理
function afterPrefectureCheck() {
    prefectureIdArray = [];
    $('[name^="prefectureCheckBox"]:checked').each(function(index, element){
        prefectureIdArray.push($(element).val());
    });
    updateSelectedPrefectureDisplay();
    setMarker();
}

// 選択中の都道府県表示を更新
function updateSelectedPrefectureDisplay() {
    var selectedPrefectures = [];
    $('[name^="prefectureCheckBox"]:checked').each(function(index, element){
        var prefectureId = $(element).val();
        var prefecture = prefectureArray.filter(e => e.prefecture_id == prefectureId)[0];
        if (prefecture) {
            selectedPrefectures.push(prefecture.name);
        }
    });
    
    var displayText = '';
    if (selectedPrefectures.length === 0) {
        displayText = '';
    } else {
        var $displayElement = $('#selectedPrefectureDisplay');
        if ($displayElement.length === 0) {
            return;
        }
        
        // 親要素（accordion_inner）の幅を取得
        var $parent = $displayElement.parent();
        var parentElement = $parent[0];
        if (!parentElement) {
            return;
        }
        // レイアウトを強制的に再計算させる
        parentElement.offsetWidth;
        var parentWidth = parentElement.offsetWidth;
        if (parentWidth === 0) {
            // 親要素の幅が取得できない場合（非表示など）は処理をスキップ
            return;
        }
        
        var displayElement = $displayElement[0];
        // 要素の実際の幅を強制的に再計算させる
        displayElement.offsetWidth;
        
        // 要素のclientWidthを使用（paddingを含むがborderを含まない）
        // scrollWidthはコンテンツの幅（padding/borderを含まない）なので、
        // clientWidthと比較することで正しく判定できる
        displayElement.offsetWidth; // レイアウトを強制的に再計算
        var containerWidth = displayElement.clientWidth;
        
        var separator = '、';
        var otherText = '、他';
        
        // すべて表示できるかチェック
        var allText = selectedPrefectures.join(separator);
        $displayElement.text(allText);
        displayElement.offsetWidth; // レイアウトを強制的に再計算
        var allTextWidth = displayElement.scrollWidth;
        
        if (allTextWidth <= containerWidth) {
            // すべて表示できる
            displayText = allText;
        } else {
            // 表示できる数を探す（「他」を含む状態で）
            var displayCount = 0;
            for (var i = 1; i <= selectedPrefectures.length; i++) {
                var testText = selectedPrefectures.slice(0, i).join(separator) + otherText;
                $displayElement.text(testText);
                // 要素の実際の幅を強制的に再計算させる
                displayElement.offsetWidth;
                var testWidth = displayElement.scrollWidth;
                if (testWidth > containerWidth) {
                    displayCount = i - 1;
                    break;
                }
                displayCount = i;
            }
            
            if (displayCount <= 0) {
                displayText = otherText.replace('、', '');
            } else if (displayCount >= selectedPrefectures.length) {
                // すべて表示できる場合は「他」を付けない
                displayText = allText;
            } else {
                displayText = selectedPrefectures.slice(0, displayCount).join(separator) + otherText;
            }
        }
    }
    
    $('#selectedPrefectureDisplay').text(displayText);
}

// 路線名チェック後処理
function afterLineCheck() {
    lineIdArray = [];
    $('[name^="lineCheckBox"]:checked').each(function(index, element){
        lineIdArray.push($(element).val());
    });
    setMarker();
}

// 乗降客数スライド処理
function sliderChange() {
    var rangeArray = $('#range-slider').val().split(',');
    $('#passengerTextFrom').val(rangeArray[0]);
    $('#passengerTextTo').val(rangeArray[1]);
    setMarker();
}

// 乗降客数テキスト変更処理
function passengerTextChange() {
    if (!$.isNumeric($('#passengerTextFrom').val()) || !$.isNumeric($('#passengerTextTo').val())) {
        return;
    }
    $('#range-slider').jRange('setValue', $('#passengerTextFrom').val() + "," + $('#passengerTextTo').val());
    setMarker();
}