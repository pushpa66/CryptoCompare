<?php
$serverName = "cryptolive.database.windows.net";
$connectionOptions = array(
    "Database" => "cryptopivot",
    "Uid" => "kierancrypto",
    "PWD" => "2Sugarsplease"
);

$symbols = getSymbols();
echo count($symbols)."<br/>";

$t1 = time();
$data = getAllCoinSnapShots();
$t2 = time();
$min = strval((int)(($t2 - $t1)/60));
$sec = strval(($t2 - $t1)%60);
echo "Execution time ".$min." minutes and ".$sec." seconds <br/>";
echo count($data)."<br/>";

// var_dump($data);

$conn = sqlsrv_connect($serverName, $connectionOptions);
if( $conn === false ) {
    die( FormatErrors( sqlsrv_errors()));
}

// dropTable("Symbol", $conn);
// dropTable("Data", $conn);
// createTableSymbol($conn);
// createTableData($conn);

insertDataToData($data, $conn);

truncateTable("Symbol", $conn);
insertDataToSymbol($symbols, $conn);


// truncateTable("Data", $conn);

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
            $result1 = getCoinData($fsym, "ETH");
            $result2 = getCoinData($fsym, "BTC");
        } else if ($fsym == "ETH"){
            $result1 = getCoinData($fsym, "USD");
            $result2 = getCoinData($fsym, "BTC");
        } else if ($fsym == "BTC"){
            $result1 = getCoinData($fsym, "USD");
            $result2 = getCoinData($fsym, "ETH");
        } else {
            $result1 = getCoinData($fsym, "USD");
            $result2 = getCoinData($fsym, "ETH");
            $result3 = getCoinData($fsym, "BTC");
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
function insertDataToData($data, $conn){

    $count = count($data);
    for ($i = 0; $i <= (int)($count / 1000); $i++){
        $string = "";
        for ($j = 0; $j < 1000; $j++){
            $index = $i * 1000 + $j;
            if($index != $count){
                $date = $data[$index]['Date'];
                $fromSymbol = $data[$index]['FromSymbol'];
                $toSymbol = $data[$index]['ToSymbol'];
                $time = $data[$index]['Time'];
                $open = $data[$index]['Open'];
                $high = $data[$index]['High'];
                $low = $data[$index]['Low'];
                $close = $data[$index]['Close'];
                $volumeFrom = $data[$index]['VolumeFrom'];
                $volumeTo = $data[$index]['VolumeTo'];

                $string =  $string."('$date','$fromSymbol','$toSymbol',$time, $open, $high, $low, $close, $volumeFrom, $volumeTo ),";
            } else {
                break;
            }   
        }

        $string = $string."+";
        $string = str_replace(",+", "", $string);
        var_dump($string);
    
        $insert = "INSERT INTO Data
        (Date, FromSymbol, ToSymbol, Time, _Open, High, Low, _Close, VolumeFrom, VolumeTo)
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
                $name = $symbols[$i * 1000 + $j]['CoinName'];
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

function dropTable($tableName, $conn){
    $dropTable = "DROP TABLE $tableName";
    $getResults= sqlsrv_query($conn, $dropTable);
    if ($getResults == FALSE)
        die(FormatErrors(sqlsrv_errors()));
    else
    	echo "Table $tableName is dropped successfully!<br/>";
}

function truncateTable($tableName, $conn){
    $truncateTable = "TRUNCATE TABLE $tableName";
    $getResults= sqlsrv_query($conn, $truncateTable);
    if ($getResults == FALSE)
        die(FormatErrors(sqlsrv_errors()));
    else
    	echo "Table $tableName is truncated successfully!<br/>";
}

function createTableData($conn){
    $createTable = "CREATE TABLE Data (
        ID int NOT NULL IDENTITY PRIMARY KEY,
        Date varchar(20) NOT NULL,
        FromSymbol varchar(30) NOT NULL,
        ToSymbol varchar(30) NOT NULL,
        Time float,
        _Open float,
        High float,
        Low float,
        _Close float,
        VolumeFrom float,
        VolumeTo float
    )";

    $getResults= sqlsrv_query($conn, $createTable);

    if ($getResults == FALSE){
        echo "Error! <br/>";
        die(FormatErrors(sqlsrv_errors()));
    } else
        echo "Table Data is created successfully!<br/>";
}

function createTableSymbol($conn){
    //Select Query
    $createTable = "CREATE TABLE Symbol (
        ID int NOT NULL IDENTITY PRIMARY KEY,
        Symbol varchar(30) NOT NULL,
        CoinName varchar(50)
    )" ;

    $getResults= sqlsrv_query($conn, $createTable);

    if ($getResults == FALSE)
        die(FormatErrors(sqlsrv_errors()));
    else
        echo "Table Symbol is created successfully!<br/>";
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
            $sym = array("Symbol" => $symbol['Symbol'], "CoinName" => $symbol['CoinName']);
            array_push($symbols, $sym);
        }
    }

    // var_dump($symbols);
    return $symbols;
}

function getCoinData($fsym, $tsym){

    $snapShot = array();
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://min-api.cryptocompare.com/data/histominute?fsym=$fsym&tsym=$tsym&limit=1",
        // CURLOPT_URL => "https://www.cryptocompare.com/api/data/coinsnapshot/?fsym=$fsym&tsym=$tsym",
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


        if($response['Response'] == "Success"){
            $data = $response['Data'][0];

            $t=time();
            $date = date("Y-m-d",$t);

            $fromSymbol = $fsym;
            $toSymbol = $tsym;
            $time = setData($data, 'time');
            $open = setData($data, 'open');
            $high = setData($data, 'high');
            $low = setData($data, 'low');
            $close = setData($data, 'close');
            $volumeFrom = setData($data, 'volumefrom');
            $volumeTo = setData($data, 'volumeto');
          
            $snap = array(
                'Date'=> $date,
                'FromSymbol' => $fromSymbol,
                'ToSymbol' => $toSymbol,
                'Time' => $time,
                'Open' => $open,
                'High' => $high,
                'Low' => $low,
                'Close' => $close,
                'VolumeFrom' => $volumeFrom,
                'VolumeTo' => $volumeTo
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
