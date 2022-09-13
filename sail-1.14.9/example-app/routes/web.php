<?php

use App\Http\Controllers\PhotoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/cfti5', function () {
    return view('indexCFTI5');
});



//Route::get('/test','PhotoController@saveJson');
Route::post('/test','PhotoController@saveJson');


Route::get('/indexQuakesXML','PhotoController@indexQuakesXML');



Route::get('/photo','PhotoController@index');

Route::get('/photoLoadXML','PhotoController@indexLoadXML');


//PRIMA CHIAMATA DATI CACHATI
Route::get('/photoLoadXML2','PhotoController@indexLoadXML2');


Route::get('/indexV3LocFull3', function () {
    $result = (new PhotoController())->indexLocalityLoad();

    $arrOutput = json_decode($result->content(), TRUE);  //decodifica il json della risposta

    // Log::info( $arrOutput[0]["Loc"] ); //dato presente e verificato sui LOG
    Log::info( "Route@indexV3LocFull3 TOTALE ELEMENTI RECUPERATI DAL CONTROLLER:" . count($arrOutput[0]) ); //dato presente e verificato sui LOG
    Log::info("Route@indexV3LocFull3 ELEMENT ARRAY CARICATO MAPPATURA VIEW LOADING...");
    return view('indexV3LocFull', ['alldata' => $arrOutput[0]]);
});



Route::get('/indexV3LocFull2', function () {
    $result = (new PhotoController())->indexLoadXML2();

    //RECUPERA IL CONTENT DEL JSON DI RISPOSTA e deve essere ASSOCIATIVO per prendere il LOC
    $arrOutput = json_decode($result->content(), TRUE);  //decodifica il json della risposta

    // Log::info( $arrOutput[0]["Loc"] ); //dato presente e verificato sui LOG


    function filter($item): bool
    {
        return ($item['maxint'] >= 0 );
    }

    //essendo una risposta JSON la risposta e composta cosi quindi 0 per entrare dentro l'oggetto Loc per referenziare l'array con tutti i risultati
    //  array (
    //  0 =>
    //  (object) array(
    //     'Loc' =>
    $filteredLocality = array_filter($arrOutput[0]["Loc"], 'filter');
    $elementArray = array();
    //LOOP ATTRAVERSO LA CHIAVE PER INDICE DI ARRAY ASSOCIATIVO
    foreach ($filteredLocality as $key => $value) {
        $convertCoordinates = [];
        //crea una classe std di output che rappresenterebbe il model
        $element = new stdClass();
        $element->ris = $value["risentimenti"];
        $element->EEnum = (int)$value["ee"];
        $element->maxint = (float)$value["maxint"];
        $element->name = $value["nazione"];
        $element->description = $value["desloc_cfti"];
        array_push($convertCoordinates, (float)$value["lon_wgs84"], (float)$value["lat_wgs84"]); //aggiunge 2 elementi all'array
        $element->coordinates = $convertCoordinates;//$coordinates;
        $element->url = "http://www.google.it";
        //gestione del campo maxintROM e
        if ($element->maxint == 11) {
            $element->maxintROM = "XI";
        } else if ($element->maxint == 10.5) {
            $element->maxintROM = "XI-X";
        } else if ($element->maxint == 10) {
            $element->maxintROM = "X";
        } else if ($element->maxint == 9.5) {
            $element->maxintROM = "IX-X";
        } else if ($element->maxint == 9.1) {
            $element->maxint = 9;
            $element->maxintROM = "IX";
        } else if ($element->maxint == 9) {
            $element->maxintROM = "IX";
        } else if ($element->maxint == 8.5) {
            $element->maxintROM = "VIII-IX";
        } else if ($element->maxint == 8.2) {
            $element->maxint = 8;
            $element->maxintROM = "VIII";
        } else if ($element->maxint == 8.1) {
            $element->maxint = 8;
            $element->maxintROM = "VIII";
        } else if ($element->maxint == 8) {
            $element->maxintROM = "VIII";
        } else if ($element->maxint == 7.5) {
            $element->maxintROM = "VII-VIII";
        } else if ($element->maxint == 7) {
            $element->maxintROM = "VII";
        } else if ($element->maxint == 6.5) {
            $element->maxintROM = "VI-VII";
        } else if ($element->maxint == 6.1) {
            $element->maxint = 6;
            $element->maxintROM = "VI";
        } else if ($element->maxint == 6.6) {
            $element->maxint = 6.5;
            $element->maxintROM = "VI-VII";
        } else if ($element->maxint == 6) {
            $element->maxintROM = "VI";
        } else if ($element->maxint == 5.5) {
            $element->maxintROM = "V-VI";
        } else if ($element->maxint == 5.1) {
            $element->maxint = 5;
            $element->maxintROM = "V";
        } else if ($element->maxint == 5) {
            $element->maxintROM = "V";
        } else if ($element->maxint == 4.6) {
            $element->maxint = 4.5;
            $element->maxintROM = "IV-V";
        } else if ($element->maxint == 4.5) {
            $element->maxintROM = "IV-V";
        } else if ($element->maxint == 4) {
            $element->maxintROM = "IV";
        } else if ($element->maxint == 3.5) {
            $element->maxintROM = "III-IV";
        } else if ($element->maxint == 3) {
            $element->maxintROM = "III";
        } else if ($element->maxint == 2.5) {
            $element->maxintROM = "II-III";
        } else if ($element->maxint == 2) {
            $element->maxintROM = "II";
        } else if ($element->maxint == 1) {
            $element->maxintROM = "I";
        } else if ($element->maxint == 0.2) {
            $element->maxintROM = "G";
        } else if ($element->maxint == 0) {
            $element->maxintROM = "NF";
        } else if ($element->maxint == 0.1) {
            $element->maxintROM = "N";
        } else if ($element->maxint == -1) {
            $element->maxintROM = "NC";
        } else if ($element->maxint == -2) {
            $element->maxintROM = "-";
        }

        //$element->key = $key; //SE SI VUOLE AGGIUNGERE LA CHIAVE
        //aggiungo elemento nell'array
        $elementArray[] = $element;
    }

    //['varibileNellaPagina' => variabileQuiLocale]
    //CHIAMA LA PAGINA indexV3LocFull.blade.php passando nella varibile alldata il contenuto di $elementArray sopra valorizzato. Ovviamente per il PHP variabile letta con $alldata

    //Log::info($elementArray); valorizzazione dei log su disco
    Log::info("ELEMENT ARRAY CARICATO MAPPATURA VIEW LOADING...");
    return view('indexV3LocFull', ['alldata' => $elementArray]);
});

