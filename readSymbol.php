<?php
$serverName = "cryptolive.database.windows.net";
$connectionOptions = array(
    "Database" => "cryptopivot",
    "Uid" => "kierancrypto",
    "PWD" => "2Sugarsplease"
);

$conn = sqlsrv_connect($serverName, $connectionOptions);
if( $conn === false ) {
    die( FormatErrors( sqlsrv_errors()));
}

//truncateTable("Symbol", $conn);
readSymbol($conn);

function readSymbol($conn){
	$readData = "SELECT * FROM Symbol" ;

	$getResults= sqlsrv_query($conn, $readData);

	if ($getResults == FALSE)
		  die(FormatErrors(sqlsrv_errors()));
	else
		echo "Success<br/>";
		while( $row = sqlsrv_fetch_array( $getResults, SQLSRV_FETCH_ASSOC) ) {
		    echo $row['ID'].", ".$row['Symbol'].", ".$row['CoinName']."<br />";
		}

	sqlsrv_free_stmt($getResults);
}

function truncateTable($tableName, $conn){
    $truncateTable = "TRUNCATE TABLE $tableName";
    $getResults= sqlsrv_query($conn, $truncateTable);
    if ($getResults == FALSE)
        die(FormatErrors(sqlsrv_errors()));
    else
    	echo "Table $tableName is truncated successfully!<br/>";
		sqlsrv_free_stmt($getResults);
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

