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
readCount($conn);

function readCount($conn){
	$readData = "SELECT * FROM Data WHERE DATE = '2018-05-14' and FROMSYMBOL = 'BTC' and TOSYMBOL = 'USD'" ;

	$getResults= sqlsrv_query($conn, $readData);

	if ($getResults == FALSE)
		  die(FormatErrors(sqlsrv_errors()));
	else
		echo "Success<br />";
		while( $row = sqlsrv_fetch_array( $getResults, SQLSRV_FETCH_ASSOC) ) {
            echo $row['ID'].", ".$row['DATE'].", ".$row['FROMSYMBOL'].", ".$row['TOSYMBOL'].", ".$row['PRICE'].", ".
            $row['LASTUPDATE'].", ".$row['LASTVOLUME'].", ".$row['LASTVOLUMETO'].", ".$row['VOLUMEDAY'].", ".
            $row['VOLUMEDAYTO'].", ".$row['VOLUME24HOUR'].", ".$row['VOLUME24HOURTO'].", ".$row['OPENDAY'].", ".
            $row['HIGHDAY'].", ".$row['LOWDAY'].", ".$row['OPEN24HOUR'].", ".$row['HIGH24HOUR'].", ".
            $row['LOW24HOUR']."<br />";
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

