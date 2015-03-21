<?php
require_once("../lib/mysql.php");

$mysql = $__connection;



//------------------------------------------------------
// define
//------------------------------------------------------
define('PANEL_SEA'   , 0);
define('PANEL_WOOD'  , 1);
define('PANEL_MUD'   , 2);
define('PANEL_IRON'  , 3);
define('PANEL_WHEAT' , 4);
define('PANEL_SHEEP' , 5);
define('PANEL_DESERT', 6);

define('CARD_KNIGHT'  , 0);
define('CARD_POINT'   , 1);
define('CARD_LOAD'    , 2);
define('CARD_DRAW'    , 3);
define('CARD_MONOPOLY', 4);

//------------------------------------------------------
// exec
//------------------------------------------------------
$panel = array(
            PANEL_DESERT, 
            PANEL_WOOD , PANEL_WOOD , PANEL_WOOD , PANEL_WOOD ,
            PANEL_MUD  , PANEL_MUD  , PANEL_MUD  ,
            PANEL_IRON , PANEL_IRON , PANEL_IRON ,
            PANEL_WHEAT, PANEL_WHEAT, PANEL_WHEAT, PANEL_WHEAT,
            PANEL_SHEEP, PANEL_SHEEP, PANEL_SHEEP, PANEL_SHEEP,
        );
shuffle($panel);
$num = array(2,3,3,4,4,5,5,6,6,8,8,9,9,10,10,11,11,12);
shuffle($num);
$num[] = 0;

$panel_set = array();
for ($i=0; $i<19; $i++) {
    if ($panel[$i] == 6) {
        $num[18] = $num[$i];
        $num[$i] = 0;
    }
    $panel_set[] = array('type'=> $panel[$i], 'num'=> $num[$i]);
}


$cards = array(
            CARD_KNIGHT, CARD_KNIGHT, CARD_KNIGHT, CARD_KNIGHT, CARD_KNIGHT,
            CARD_KNIGHT, CARD_KNIGHT, CARD_KNIGHT, CARD_KNIGHT, CARD_KNIGHT,
            CARD_KNIGHT, CARD_KNIGHT, CARD_KNIGHT, CARD_KNIGHT,
            CARD_POINT, CARD_POINT, CARD_POINT, CARD_POINT, CARD_POINT,
            CARD_LOAD, CARD_LOAD,
            CARD_DRAW, CARD_DRAW,
            CARD_MONOPOLY, CARD_MONOPOLY
        );
shuffle($cards);


// DB‚É“o˜^
$mysql->query(sprintf('INSERT INTO games(`map`, `card`, `created`) VALUES ("%s", "%s", now())',
                $mysql->escape(json_encode($panel_set)),
                $mysql->escape(json_encode($cards))));
$mysql->getErrors($e); var_dump($e);

// JSON‚Åo—Í
header( 'Content-Type: text/javascript; charset=utf-8' );
echo json_encode(array(
        'panel_set'=> $panel_set,
        'cards' => $cards
    ));

