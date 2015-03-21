<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>canvasで図形を描く</title>

<link rel="stylesheet" href="/css/style.css">
<!-- script type="text/javascript" src="/js/jquery-2.1.3.min.js"></script -->
<script type="text/javascript" src="/js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="/js/jcanvas-2015.02.07.min.js"></script>
<script type="text/javascript" src="/js/sprintf.js"></script>
<script type="text/javascript">
<!--

//------------------------------------------------------
// define
//------------------------------------------------------
ROOT3 = 1.73205;
PANEL = { /* パネルの種類 */
    type: 0,              /* int : パネルのタイプ */
    num: 0,               /* int : ダイス目       */
    z: false,             /* int : 描画する順番   */
    fillColor: "white",   /* text: 塗りつぶしの色 */
    strokeColor: "black", /* text: 線の色         */
    strokeWidth: 1,       /* int : 線の太さ       */
};
PANEL_SEA    = {type: 0, num: 0, z: 1, fillColor: "blue",    strokeColor: "navy",  strokeWidth: 1}; // 海
PANEL_WOOD   = {type: 1, num: 0, z: 2, fillColor: "#008800", strokeColor: "white", strokeWidth: 8}; // 木
PANEL_MUD    = {type: 2, num: 0, z: 2, fillColor: "#88270A", strokeColor: "white", strokeWidth: 8}; // 土
PANEL_IRON   = {type: 3, num: 0, z: 2, fillColor: "gray",    strokeColor: "white", strokeWidth: 8}; // 鉄
PANEL_WHEAT  = {type: 4, num: 0, z: 2, fillColor: "orange",  strokeColor: "white", strokeWidth: 8}; // 麦
PANEL_SHEEP  = {type: 5, num: 0, z: 2, fillColor: "#33ff00", strokeColor: "white", strokeWidth: 8}; // 羊
PANEL_DESERT = {type: 6, num: 0, z: 2, fillColor: "#DBC266", strokeColor: "white", strokeWidth: 8}; // 砂漠
//PANEL_SEA = PANEL_WHEAT = PANEL_IRON = PANEL_SHEEP = PANEL_MUD = PANEL_WOOD = PANEL_DESERT = PANEL;

POINT = {
    x: 0,
    y: 0,
    panel1: {},
    panel2: {},
    panel3: {},
};

//------------------------------------------------------
// global
//------------------------------------------------------
var _stat = {init: false};

var gBoardSize = 0;
var gLine = {};
var gHexagon = {};
var gStrokeWidth = 1;
var gRadius = 1;
var gPolygonNum = 10;

//------------------------------------------------------
// event
//------------------------------------------------------
/**
 * 起動時の処理
 */
$(function(){
    init_status();
    set_panel();
    first_draw();
});


/**
 * ウィンドウリサイズ時の処理：再描画
 */
var timer = false;
$(window).resize(function() {
    if (timer !== false) {
        clearTimeout(timer);
    }
    timer = setTimeout(function() {
        redraw();
    }, 100);
});

//------------------------------------------------------
// function
//------------------------------------------------------
/**
 * 
 */
function copy_panel(l, x, y, num, panel) {
    gHexagon[l][x][y].type        = panel.type;
    gHexagon[l][x][y].num         = num;
    gHexagon[l][x][y].z           = panel.z;
    gHexagon[l][x][y].fillColor   = panel.fillColor;
    gHexagon[l][x][y].strokeColor = panel.strokeColor;
    gHexagon[l][x][y].strokeWidth = panel.strokeWidth;

    if (l == 0) {
        set_point(1, 2*x-1, y-1, gHexagon[l][x][y]);
        set_point(1, 2*x-0, y-1, gHexagon[l][x][y]);
        set_point(1, 2*x-1, y-0, gHexagon[l][x][y]);
        set_point(1, 2*x-0, y-0, gHexagon[l][x][y]);
        set_point(0, 2*x-1, y-0, gHexagon[l][x][y]);
        set_point(0, 2*x-0, y-0, gHexagon[l][x][y]);
    }
    else {
        set_point(0, 2*x+0, y+0, gHexagon[l][x][y]);
        set_point(0, 2*x+1, y+0, gHexagon[l][x][y]);
        set_point(0, 2*x+0, y+1, gHexagon[l][x][y]);
        set_point(0, 2*x+1, y+1, gHexagon[l][x][y]);
        set_point(1, 2*x+0, y+0, gHexagon[l][x][y]);
        set_point(1, 2*x+1, y+0, gHexagon[l][x][y]);
    }
}

/**
 * 
 */
function set_point(l, x, y, obj) {
    if ($.isEmptyObject(gLine[l][x][y].panel1)) {
        gLine[l][x][y].panel1 = obj;
    }
    else if ($.isEmptyObject(gLine[l][x][y].panel2)) {
        gLine[l][x][y].panel2 = obj;
    }
    else if ($.isEmptyObject(gLine[l][x][y].panel3)) {
        gLine[l][x][y].panel3 = obj;
    }
    else {
        console.log({l:l, x:x, y:y});
        console.log(gLine[l][x][y]);
    }
}

/**
 * パネルと出目の配置（暫定）→Ajaxで取得する
 */
function set_panel() {
    var panel = shuffle([
            PANEL_DESERT, 
            PANEL_WOOD , PANEL_WOOD , PANEL_WOOD , PANEL_WOOD , 
            PANEL_MUD  , PANEL_MUD  , PANEL_MUD  , 
            PANEL_IRON , PANEL_IRON , PANEL_IRON , 
            PANEL_WHEAT, PANEL_WHEAT, PANEL_WHEAT, PANEL_WHEAT, 
            PANEL_SHEEP, PANEL_SHEEP, PANEL_SHEEP, PANEL_SHEEP, 
        ]);
    var num = shuffle([2,3,3,4,4,5,5,6,6,8,8,9,9,10,10,11,11,12]);
    num.push(0);
    var pos = [
            [1, 1, 3],
            [1, 1, 4],
            [1, 1, 5],
            [0, 2, 3],
            [0, 2, 4],
            [0, 2, 5],
            [0, 2, 6],
            [1, 2, 2],
            [1, 2, 3],
            [1, 2, 4],
            [1, 2, 5],
            [1, 2, 6],
            [0, 3, 3],
            [0, 3, 4],
            [0, 3, 5],
            [0, 3, 6],
            [1, 3, 3],
            [1, 3, 4],
            [1, 3, 5],
        ];
    
    for (i=0; i<19; i++) {
        if (panel[i].type == 6 || panel[i].type == 0) {
            num[18] = num[i];
            copy_panel(pos[i][0], pos[i][1], pos[i][2],      0, panel[i]);
        } else {
            copy_panel(pos[i][0], pos[i][1], pos[i][2], num[i], panel[i]);
        }
    }
    console.log(num);
}

/**
 * 
 */
function init_status() {
    var w = $(window).width(),
        h = $(window).height();
    gBoardSize = (w > h ? h : w) * 0.9;
    var height = gBoardSize / (gPolygonNum) / 2;

    gRadius = height / ROOT3 * 2;
    gStrokeWidth = gRadius / 5;

    gHexagon[0] = {};
    gHexagon[1] = {};
    for (i=0; i<gPolygonNum; i++) {
        gHexagon[0][i] = {};
        gHexagon[1][i] = {};
        for (j=0; j<gPolygonNum; j++) {
            gHexagon[0][i][j] = $.extend(true, {}, PANEL_SEA);
            gHexagon[1][i][j] = $.extend(true, {}, PANEL_SEA);
        }
    }
    gLine[0] = {};
    gLine[1] = {};
    for (i=0; i<gPolygonNum*2; i++) {
        gLine[0][i] = {};
        gLine[1][i] = {};
        for (j=0; j<gPolygonNum; j++) {
            gLine[0][i][j] = $.extend(true, {}, POINT);
            gLine[1][i][j] = $.extend(true, {}, POINT);
        }
    }

    var shift_x = -gRadius/2;
    var shift_y = -height;
    for (i=0; i<gPolygonNum; i++) {
        for (j=0; j<gPolygonNum; j++) {
            gHexagon[0][i][j].x = gRadius * (i*3    );
            gHexagon[1][i][j].x = gRadius * (i*3+1.5);

            gHexagon[0][i][j].y = height * (j*2  );
            gHexagon[1][i][j].y = height * (j*2+1);

            $("body").append("<div>( " + gHexagon[0][i][j].x + " , " + gHexagon[0][i][j].y + " )</div>");
            $("body").append("<div>( " + gHexagon[1][i][j].x + " , " + gHexagon[1][i][j].y + " )</div>");
        }
    }

    for (j=0; j<gPolygonNum; j++) {
        for (i=0; i<gPolygonNum*2; i+=2) {
            gLine[0][i][j].x = gRadius * (i*1.5+1.0);
            gLine[1][i][j].x = gRadius * (i*1.5+0.5);
            gLine[0][i][j].y = height * (j*2  );
            gLine[1][i][j].y = height * (j*2+1);
        }
        for (i=1; i<gPolygonNum*2; i+=2) {
            gLine[0][i][j].x = gRadius * (i*1.5+0.5);
            gLine[1][i][j].x = gRadius * (i*1.5+1.0);
            gLine[0][i][j].y = height * (j*2  );
            gLine[1][i][j].y = height * (j*2+1);
        }
    }
}


/**
 * 最初の描画処理
 */
function first_draw() {
    $.jCanvas.defaults.layer = true;
    $.jCanvas.defaults.draggable = true;
    $.jCanvas.defaults.groups = ['board'];
    $.jCanvas.defaults.dragGroups = ['board'];
    
    setWHToElement(gBoardSize);

    //$("#board").clearCanvas();

    // 背面
    $.each( gHexagon, function() {
        $.each( this, function() {
            $.each( this, function(i,v) {
                if (v.z === 1)
                $('#board').drawPolygon({
                    fillStyle: v.fillColor,
                    strokeStyle: v.strokeColor,
                    strokeWidth: v.strokeWidth,
                    x: v.x, y: v.y,
                    radius: gRadius,
                    sides: 6,
                });
            });
        });
    });

    // 前面
    $.each( gHexagon, function() {
        $.each( this, function() {
            $.each( this, function(i,v) {
                if (v.z === 2) {
                    $('#board').drawPolygon({
                        fillStyle: v.fillColor,
                        strokeStyle: v.strokeColor,
                        strokeWidth: v.strokeWidth,
                        x: v.x, y: v.y,
                        radius: gRadius,
                        sides: 6,
                    });
                    if (v.num != 0) {
                        $('#board').drawArc({
                            fillStyle: "#fff",
                            x: v.x, y: v.y,
                            radius: gRadius/2
                        }).drawText({
                            fillStyle: "#FF8800",
                            strokeStyle: "#AA0000",
                            strokeWidth: 2,
                            x: v.x, y: v.y,
                            fontSize: 36,
                            fontFamily: "Verdana",
                            text: "" + v.num
                        });
                    }
                }
            });
        });
    });
    
    situetion_init_point();
    
    setWHToStyle(gBoardSize);
    
    set_user_area(1);
}

/**
 * 何もできない状態にする
 */
function situetion_none() {
    $('#board').removeLayerGroup('points');
}

/**
 * ポイントを選択できる状態にする
 */
function situetion_init_point() {
    l = 0;
    $.each( gLine, function() {
        i = 0;
        $.each( this, function() {
            j = 0;
            $.each( this, function(i,v) {
                if (!$.isEmptyObject(v.panel1)) {
                    name = "x" + i + ",y" + j + ",z" + l;
                    $('#board').drawArc({
                        groups: ['board', 'points'],
                        fillStyle: "#36c",
                        x: v.x, y: v.y,
                        radius: gStrokeWidth*2,
                        opacity: 0.3,
                        data: {pz: l, px: i, py: j},
                        mouseover: function(layer) {
                            layer.opacity = 0.7;
                        },
                        mouseout: function(layer) {
                            layer.opacity = 0.3;
                        },
                        click: function(layer) {
                            d = layer.data;
                            alert(d.px + ", " + d.py + ", " + d.pz);
                            layer.mouseover = null;
                            layer.mouseout = null;
                        },
                    });
                }
                j++;
            });
            i++;
        });
        l++;
    });
}

/**
 * 再描画
 */
function redraw() {
    var w = $(window).width(),
    h = $(window).height();
    gBoardSize = (w > h ? h : w) * 0.9;

    setWHToStyle(gBoardSize);
}

/**
 * 小窓配置
 */
function set_user_area(num) {
    area1 = $("#u_area" + ((num+0)%5));
    area2 = $("#u_area" + ((num+1)%5));
    area3 = $("#u_area" + ((num+2)%5));
    area4 = $("#u_area" + ((num+3)%5));
    offset = $("#board").offset();
    height = $("#board").height();
    width  = $("#board").width();
    
    area_height = height/5;
    area_width  = width/3;

    area1.css({ top   : offset.top, 
                left  : offset.left,
                height: area_height,
                width : area_width});
    area2.css({ top: offset.top, 
                left: offset.left + width - area_width,
                height: area_height,
                width : area_width});
    area3.css({ top   : offset.top + height - area_height,
                left  : offset.left,
                height: area_height,
                width : area_width});
    area4.css({ top   : offset.top + height - area_height,
                left  : offset.left + width - area_width,
                height: area_height,
                width : area_width});
}

/**
 * element: width, height
 */
function setWHToElement(size) {
    //size *= 2;
    $("#board").attr({width: size + "px", height: size + "px"});
}

/**
 * style: width, height
 */
function setWHToStyle(size) {
    $("#board").css({width: size + "px", height: size + "px"});
}

/**
 * 配列のランダム並び替え
 */
function shuffle(array) {
    var n = array.length, t, i;
    
    while (n) {
        i = Math.floor(Math.random() * n--);
        t = array[n];
        array[n] = array[i];
        array[i] = t;
    }
    
    return array;
}


//-->
</script>

</head>
<body>

<canvas id="board">
描画非対応
</canvas>

<div id="u_area1" class="u_area"></div>
<div id="u_area2" class="u_area"></div>
<div id="u_area3" class="u_area"></div>
<div id="u_area4" class="u_area"></div>

</body>
</html>

