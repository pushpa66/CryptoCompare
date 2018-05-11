<?php
$serverName = "cryptolive.database.windows.net";
$connectionOptions = array(
    "Database" => "cryptopivot",
    "Uid" => "kierancrypto",
    "PWD" => "2Sugarsplease"
);

$symbols = getSymbols();
echo count($symbols)."<br/>";

/*
truncateTable("Symbol", $conn);
insertDataToSymbol($symbols, $conn);
*/

// truncateTable("Data", $conn);

// createTableSymbol($conn);
// createTableData($conn);


$data = getAllCoinSnapShots();
echo count($data)."<br/>";

//Establishes the connection
$conn = sqlsrv_connect($serverName, $connectionOptions);
if( $conn === false ) {
    die( FormatErrors( sqlsrv_errors()));
}
insertDataToData($data, $conn);

// sqlsrv_free_stmt($getResults);


function insertDataToData($data, $conn){

    $count = count($data);
    for ($i = 0; $i <= (int)($count / 1000); $i++){
        $string = "";
        for ($j = 0; $j < 1000; $j++){
            $index = $i * 1000 + $j;
            if($index != $count){
                $date = $data[$index]['DATE'];
                $fromSymbol = $data[$index]['FROMSYMBOL'];
                $toSymbol = $data[$index]['TOSYMBOL'];
                $price = $data[$index]['PRICE'];
                $lastUpdate = $data[$index]["LASTUPDATE"];
                $lastVolume = $data[$index]["LASTVOLUME"];
                $lastVolumeTo = $data[$index]["LASTVOLUMETO"];
                $volumeDay = $data[$index]["VOLUMEDAY"];
                $volumeDayTo = $data[$index]["VOLUMEDAYTO"];
                $volume24Hour = $data[$index]["VOLUME24HOUR"];
                $volume24HourTo = $data[$index]["VOLUME24HOURTO"];
                $openDay = $data[$index]["OPENDAY"];
                $highDay = $data[$index]["HIGHDAY"];
                $lowDay = $data[$index]["LOWDAY"];
                $open24Hour = $data[$index]["OPEN24HOUR"];
                $high24Hour = $data[$index]["HIGH24HOUR"];
                $low24Hour = $data[$index]["LOW24HOUR"];

                $string = $string."('$date','$fromSymbol','$toSymbol',$price, $lastUpdate, $lastVolume,";
                $string = $string."$lastVolumeTo,$volumeDay, $volumeDayTo, $volume24Hour, $volume24HourTo,";
                $string = $string."$openDay, $highDay, $lowDay, $open24Hour, $high24Hour, $low24Hour),";
            } else {
                break;
            }   
        }
        $string = $string."+";
        $string = str_replace(",+", "", $string);
        // var_dump($string);
    
        $insert = "INSERT INTO Data
        (DATE, FROMSYMBOL, TOSYMBOL, PRICE, LASTUPDATE, LASTVOLUME, LASTVOLUMETO, VOLUMEDAY, VOLUMEDAYTO,
        VOLUME24HOUR, VOLUME24HOURTO, OPENDAY, HIGHDAY, LOWDAY, OPEN24HOUR, HIGH24HOUR, LOW24HOUR)
        VALUES ".$string;
        // var_dump($insert);
        $getResults= sqlsrv_query($conn, $insert);
        if ($getResults == FALSE)
            die(FormatErrors(sqlsrv_errors()));
        else
            echo "Data are successfully inserted to Data table!<br/>";
    }
}

function insertDataToSymbol($symbols, $conn){

    $count = count($symbols);
    for ($i = 0; $i <= (int)($count / 1000); $i++){
        $string = "";
        for ($j = 0; $j < 1000; $j++){
            if(($i * 1000 + $j) != $count){
                $sym = $symbols[$i * 1000 + $j]['Symbol'];
                $name = $symbol[$i * 1000 + $j]['CoinName'];
                $string = $string."('$sym','$name'),";
            } else {
                break;
            }   
        }
        $string = $string."+";
        $string = str_replace(",+", "", $string);
        // var_dump($string);
    
        $insert = "INSERT INTO Symbol
        (Symbol, CoinName)
        VALUES ".$string;
        // var_dump($insert);
        $getResults= sqlsrv_query($conn, $insert);
        if ($getResults == FALSE)
            die(FormatErrors(sqlsrv_errors()));
        else
            echo "Data are successfully inserted to Symbol table!<br/>";
    }
}

function truncateTable($tableName, $conn){
    $truncateTable = "TRUNCATE TABLE $tableName";
    $getResults= sqlsrv_query($conn, $createTable);
    if ($getResults == FALSE)
        die(FormatErrors(sqlsrv_errors()));
    else
    	echo "Table $tableName is truncated successfully!<br/>";
}

function createTableData($conn){
    $createTable = "CREATE TABLE Data (
        ID int NOT NULL IDENTITY PRIMARY KEY,
        DATE varchar(20) NOT NULL,
        FROMSYMBOL varchar(10) NOT NULL,
        TOSYMBOL varchar(10) NOT NULL,
        PRICE float,
        LASTUPDATE int,
        LASTVOLUME float,
        LASTVOLUMETO float,
        VOLUMEDAY float,
        VOLUMEDAYTO float,
        VOLUME24HOUR float,
        VOLUME24HOURTO float,
        OPENDAY float,
        HIGHDAY float,
        LOWDAY float,
        OPEN24HOUR float,
        HIGH24HOUR float,
        LOW24HOUR float
    )" ;

    $getResults= sqlsrv_query($conn, $createTable);

    if ($getResults == FALSE)
        die(FormatErrors(sqlsrv_errors()));
    else
    	echo "Success";
}

function createTableSymbol($conn){
    //Select Query
    $createTable = "CREATE TABLE Symbol (
        ID int NOT NULL IDENTITY PRIMARY KEY,
        Symbol varchar(10) NOT NULL,
        CoinName varchar(20)
    )" ;

    $getResults= sqlsrv_query($conn, $createTable);

    if ($getResults == FALSE)
        die(FormatErrors(sqlsrv_errors()));
    else
    	echo "Success";
}

function getAllCoinSnapShots(){
    $snapShots = array();
    $symbols = getSymbols();

    // var_dump($symbols);
    $index = 0;

    foreach ($symbols as $symbol){
        $fsym = $symbol['Symbol'];
        $result1 = array();
        $result2 = array();
        $result3 = array();
        // var_dump($fsym);
        if($fsym == "USD"){
            $result1 = getCoinSnapShot($fsym, "ETH");
            $result2 = getCoinSnapShot($fsym, "BTC");
        } else if ($fsym == "ETH"){
            $result1 = getCoinSnapShot($fsym, "USD");
            $result2 = getCoinSnapShot($fsym, "BTC");
        } else if ($fsym == "BTC"){
            $result1 = getCoinSnapShot($fsym, "USD");
            $result2 = getCoinSnapShot($fsym, "ETH");
        } else {
            $result1 = getCoinSnapShot($fsym, "USD");
            $result2 = getCoinSnapShot($fsym, "ETH");
            $result3 = getCoinSnapShot($fsym, "BTC");
            // var_dump($result1);
        }

        if (!empty($result1)){
            array_push($snapShots, $result1);
        }
        if (!empty($result2)){
            array_push($snapShots, $result2);
        }
        if (!empty($result3)){
            array_push($snapShots, $result3);
        }

        $index ++;

        // if ($index == 10){
        //     break;
        // }
    }
    return $snapShots;
}

function getSymbols(){
    $symbols = array();

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://www.cryptocompare.com/api/data/coinlist",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Cache-Control: no-cache",
            "Postman-Token: 1bda77c4-32b6-47ed-a84d-6f4f98415515"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        $response = json_decode($response, true);

        foreach($response['Data'] as $symbol) {
            $symbol = array("Symbol" => $symbol['Symbol'], "CoinName" => $symbol['CoinName']);
            array_push($symbols, $symbol);
        }
    }

    return $symbols;
}

function getCoinSnapShot($fsym, $tsym){

    $snapShot = array();
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://www.cryptocompare.com/api/data/coinsnapshot/?fsym=$fsym&tsym=$tsym",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Cache-Control: no-cache",
            "Postman-Token: 59851aa2-0678-450d-a457-5a9b9cbaaca3"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        $response = json_decode($response, true);


        if($response['Response'] !== "Error"){
            $data = $response['Data']['AggregatedData'];

            $t=time();
            $date = date("Y-m-d",$t);

            $fromSymbol = setData($data,'FROMSYMBOL');
            $toSymbol = setData($data,'TOSYMBOL');
            $price = setData($data,'PRICE');
            $lastUpdate = setData($data,"LASTUPDATE");
            $lastVolume = setData($data,"LASTVOLUME");
            $lastVolumeTo = setData($data,"LASTVOLUMETO");
            $volumeDay = setData($data,"VOLUMEDAY");
            $volumeDayTo = setData($data,"VOLUMEDAYTO");
            $volume24Hour = setData($data,"VOLUME24HOUR");
            $volume24HourTo = setData($data,"VOLUME24HOURTO");
            $openDay = setData($data,"OPENDAY");
            $highDay = setData($data,"HIGHDAY");
            $lowDay = setData($data,"LOWDAY");
            $open24Hour = setData($data,"OPEN24HOUR");
            $high24Hour = setData($data,"HIGH24HOUR");
            $low24Hour = setData($data,"LOW24HOUR");

            $snap = array(
                'DATE'=> $date,
                'FROMSYMBOL' => $fromSymbol,
                'TOSYMBOL' => $toSymbol,
                'PRICE' => $price,
                'LASTUPDATE' => $lastUpdate,
                'LASTVOLUME' => $lastVolume,
                'LASTVOLUMETO' => $lastVolumeTo,
                'VOLUMEDAY' => $volumeDay,
                'VOLUMEDAYTO' => $volumeDayTo,
                'VOLUME24HOUR' => $volume24Hour,
                'VOLUME24HOURTO' => $volume24HourTo,
                'OPENDAY' => $openDay,
                'HIGHDAY' => $highDay,
                'LOWDAY' => $lowDay,
                'OPEN24HOUR' => $open24Hour,
                'HIGH24HOUR' => $high24Hour,
                'LOW24HOUR' => $low24Hour
            );

            $snapShot = $snap;
        }

    }
    return $snapShot;
}

function setData($data, $key){
    if(array_key_exists($key, $data)){
        return $data[$key];
    } else {
        return 0;
    }
}

function FormatErrors( $errors ){
    /* Display errors. */
    echo "Error information: <br/>";
    foreach ( $errors as $error )
    {
        echo "SQLSTATE: ".$error['SQLSTATE']."<br/>";
        echo "Code: ".$error['code']."<br/>";
        echo "Message: ".$error['message']."<br/>";
    }
}
?>
