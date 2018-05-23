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

//truncateTable("Data", $conn);
readData($conn);

function readData($conn){
	$readData = "SELECT * FROM Data" ;

	$getResults= sqlsrv_query($conn, $readData);

	if ($getResults == FALSE)
		  die(FormatErrors(sqlsrv_errors()));
	else
		echo "Success<br />";
		while( $row = sqlsrv_fetch_array( $getResults, SQLSRV_FETCH_ASSOC) ) {
            echo $row['ID'].", ".$row['Date'].", ".$row['FromSymbol'].", ".$row['ToSymbol'].", ".$row['_Open'].", ".
            $row['High'].", ".$row['Low'].", ".$row['_Close'].", ".$row['VolumeFrom'].", ".
            $row['VolumeTo']."<br />";
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

