<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use phpDocumentor\Reflection\Types\Integer;
use stdClass;
//use GuzzleHttp\Middleware;
//use Psr\Http\Message\RequestInterface;
//use Psr\Http\Message\ResponseInterface;




class PhotoController extends Controller
{
    //url mapped /test
    function saveJson(Request $request)
    {
        Log::info("saveJson called START");
        $data = json_decode($request->getContent());  //object stdClass dopo averlo convertito dal json in input
        Log::info("DATA");
        Log::info($request->getContent()); //logga il json secco a stringa
        //Log::info($data); //logga l'array decodificato
        Log::info("saveJson called END");
        //echo "saved";
        echo json_encode(array('success'=>'true'));
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $photosValues = Cache::get('globals');
        if (isset($photosValues)) {
            echo "Display data From REDIS server<br>";
            $photos = json_decode($photosValues);
            //dump($photos);
            Log::info('Display data From REDIS server');
            Log::info($photos);
            echo "PHOTOS N:". count($photos);
        }
        else {
            echo "Display data From REST API server<br>"; //https://jsonplaceholder.typicode.com/photos
            $response = Http::withOptions(["verify" => false])->get('https://jsonplaceholder.typicode.com/photos');
            //$response = Http::withOptions(["verify" => false])->withHeaders(["Cache-Control: no-cache",])->get('https://jsonplaceholder.typicode.com/photos');

            Log::info('Display data From REST API server');
            Log::info($response);
            Log::info($response->body());
            //var_dump($response->body());



            $photos = json_decode($response->body());

            //mostra a video l'output dei 5000 elementi
            //dump($photos);

            Cache::forever('globals', json_encode($photos));


            //Http::post($url,$fields)::withHeaders(["Authorization: Bearer $paystack_key","Cache-Control: no-cache",])::withOptions(["verify"=>false]);

            /*Cache::forever('key', 'value');
            Given that, I would change your code to something like the following:
            cache()->forever('globals', json_encode(['foo' => 'bar']));*/

            echo "STATUS:" . strval($response->status()) . "<br>";
            echo "OK:" . strval($response->ok()) . "<br>";
            echo "SUCCESSFUL:" . strval($response->successful()) . "<br>";
            echo "PHOTOS N:". count($photos);
        }
    }

    public function indexLoadXML()
    {
        $locListValues = Cache::get('LocList');
        if (isset($locListValues)) {
            echo "Display data From REDIS server<br>";
            $locListArr =  json_decode($locListValues,true);
            //dump($photos);
            Log::info('Display data From REDIS server');
            //Log::info(print_r($locListArr));
            echo "LOCLIST N:" . count($locListArr["Loc"]); // $locListArr["Loc"]
            dump($locListArr["Loc"][0]);
            dump($locListArr["Loc"][1]);
        } else {
            echo "Display data reading xml file<br>";

            //$objXmlDocument = simplexml_load_file("LocList.xml");
            $objXmlDocument = simplexml_load_file("LocList.xml", "SimpleXMLElement", LIBXML_NOCDATA);

            if ($objXmlDocument === FALSE) {
                echo "There were errors parsing the XML file.\n";
                foreach (libxml_get_errors() as $error) {
                    echo $error->message;
                }
                exit;
            }

            //Convert the SimpleXMLElement Object Into Its JSON Representation
            $objJsonDocument = json_encode($objXmlDocument);
            //Decode the JSON String Into an Array
            $arrOutput = json_decode($objJsonDocument, TRUE);

            echo "XMLdata count N:" . count($arrOutput["Loc"]);

            dump($arrOutput["Loc"][0]);
            dump($arrOutput["Loc"][1]);
            //dump($arrOutput["Loc"]);// ci mette un po'

            Cache::forever('LocList', $objJsonDocument);
            echo "STATUS:CACHED";

//        $filteredLocality = array_filter($arrOutput["Loc"], 'filter');
//        $elementArray = array();
//
//        //LOOP ATTRAVERSO LA CHIAVE PER INDICE DI ARRAY ASSOCIATIVO
//        foreach ($filteredLocality as $key => $value) {
//            //echo $key;
//            $convertCoordinates = [];
//            array_push($convertCoordinates, (float)$value["lon_wgs84"], (float)$value["lat_wgs84"]); //aggiunge 2 elementi all'array
//            //$coordinates = json_encode($convertCoordinates); //questa istruzione converte in stringa non andava bene.
//
//
//            $element = new stdClass();
//            $element->name = $value["nazione"];
//            $element->description = $value["desloc_cfti"];
//            $element->coordinates = $convertCoordinates;//$coordinates;
//            $element->url = "http://www.google.it";
//            //$element->key = $key; //SE SI VUOLE AGGIUNGERE LA CHIAVE
//            //aggiungo elemento nell'array
//            $elementArray[] = $element;
//            //DEBUG INFORMAZIONI
//            //print_r($elementArray);
//        }
        }
    }


    public function indexLoadXML2()
    {
        Log::info('@@Attempt display data From REDIS server START');
          $listXML = Cache::rememberForever('LocListForever', function () {
              Log::info('@@Display XMLFile  STARTED...........');
            $objXmlDocument = simplexml_load_file("LocList.xml", "SimpleXMLElement", LIBXML_NOCDATA);
            if ($objXmlDocument === FALSE) {
                echo "There were errors parsing the XML file.\n";
                foreach (libxml_get_errors() as $error) {
                    echo $error->message;
                }
                exit;
            }
            //Convert the SimpleXMLElement Object Into Its JSON Representation
            $objJsonDocument = json_encode($objXmlDocument);
            //Decode the JSON String Into an Array
            $arrOutput = json_decode($objJsonDocument, TRUE);

            Log::info("@@Display XMLFile  FILE count N:" . count($arrOutput["Loc"]) );

            //Cache::forever('LocList', $objJsonDocument);
              Log::info('@@Display XMLFile ENDED...........');
            return $arrOutput;
        });
        Log::info('@@Attempt display data From REDIS server END ');
        Log::info("@@Data readed from XMLFILE/cache count N:" . count($listXML["Loc"]));
        return response()->json([$listXML]);
    }

    /*
     * 1) ritorna xml puro
     * 2) cachare xml su rediis
     * 3) ritorna oggetto cachato
     * 4)   IN GENERALE PRIMO STEP TUTTO QUELLO CHE ERA LEGGI DA JS e prendi XML diventa prendi da un controller CACHE
     * 4.1) OTTIMIZZAZIONE ULTERIORE tutte le trasformazioni fatte sul JS dopo la lettura XML vengono quindi effettuate da PHP
     * 5) Si potrebbe prevedere un save in cache di un array preelaborato e fare un check su rediis se già preesistente non serve rifare l'elaborazione dell'array ma basta utilizzare
     * quanto gia' presente e cachato.
     */
    public function indexQuakesXML(){

        //TODO: chiamata esterna di esempio //

        //        $response = Http::withMiddleware(
//            Middleware::mapResponse(function (ResponseInterface $response) {
//                $header = $response->getHeader('Content-Type: application/xml');
//
//                // ...
//
//                return $response;
//            })
//        )->get('https://api.namecheap.com/xml.response?ApiUser=(username)&ApiKey=(apikey)&UserName(username)&ClientIp=(ip)');
//        Log::info($response->status());
//        Log::info($response->body());
//        //TODO:sostituisci il body con il file richiesto
//        $xml = simplexml_load_string($response->body(),'SimpleXMLElement',LIBXML_NOCDATA);
//        header('Content-Type: application/xml'); //dichiarata anche nel mapResponse qui serve se accedi direttamente al file
//        Log::info($xml->asXML());
//        echo $xml->asXML();

        Log::info('indexQuakesXML@@Attempt display data From REDIS server START');
        $listXML = Cache::rememberForever('QuakesXMLForever', function () {
            Log::info('indexQuakesXML@@LOADING XMLFile FROM DISK STARTED...........');
            $objXmlDocument = simplexml_load_file("QuakeList.xml", "SimpleXMLElement", LIBXML_NOCDATA);
            if ($objXmlDocument === FALSE) {
                echo "There were errors parsing the XML file.\n";
                foreach (libxml_get_errors() as $error) {
                    echo $error->message;
                }
                exit;
            }
            header('Content-Type: application/xml'); //dichiarata anche nel mapResponse qui serve se accedi direttamente al file
            //Log::info($objXmlDocument->asXML()); logging
            return $objXmlDocument->asXML();
        });
        Log::info('indexQuakesXML@@Attempt display data From REDIS server END returning data');
        header('Content-Type: application/xml');
        // Log::info($listXML); recupero XML
        //echo $xml->asXML();
        return $listXML;
        //return $xml->asXML();
    }


    public function indexLocalityLoad()
    {
        Log::info('Controller@@IndexLocalityLoad Attempt display data From REDIS server START');
        $listXML = Cache::rememberForever('IndexLocalityLoad', function () {
            Log::info('@@IndexLocalityLoad Display XMLFile  STARTED...........');
            $objXmlDocument = simplexml_load_file("LocList.xml", "SimpleXMLElement", LIBXML_NOCDATA);
            if ($objXmlDocument === FALSE) {
                echo "There were errors parsing the XML file.\n";
                foreach (libxml_get_errors() as $error) {
                    echo $error->message;
                }
                exit;
            }
            //Convert the SimpleXMLElement Object Into Its JSON Representation
            $objJsonDocument = json_encode($objXmlDocument);
            //Decode the JSON String Into an Array
            $arrOutput = json_decode($objJsonDocument, TRUE);

            Log::info("Controller@@IndexLocalityLoad Loading XMLFile  FILE count N:" . count($arrOutput["Loc"]) );
            Log::info('Controller@@IndexLocalityLoad Loading XMLFile ENDED...........');
            Log::info("Controller@@IndexLocalityLoad START business method on IndexLocalityLoad");

            $filteredLocality = array_filter($arrOutput["Loc"],  function($item) { return $item['maxint'] >= 0; });
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
            Log::info("Controller@@IndexLocalityLoad END business method on IndexLocalityLoad");
            Log::info("Controller@@IndexLocalityLoad ELEMENT ARRAY CARICATO .......");
            return $elementArray;
        });  //end  $listXML = Cache::rememberForever('IndexLocalityLoad', function () {
        Log::info('Controller@@IndexLocalityLoad Attempt display data From REDIS server END ');
        //Log::info("@@IndexLocalityLoad Data readed from XMLFILE/cache count N:" . count($listXML["Loc"]));
        Log::info("Controller@@IndexLocalityLoad Data readed from XMLFILE/cache count N:" . count($listXML));
        return response()->json([$listXML]);
    }

//    public function filter($item): bool
//    {
//        return ($item['maxint'] >= 0 );
//    }




    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
