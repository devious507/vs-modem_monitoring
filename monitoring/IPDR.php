<?php
 
// Set time limit to indefinite execution
set_time_limit (0);
 
// Set the ip and port we will listen on
$address = '0.0.0.0';
$port = 5000;
 
// Array that will hold client information
$clients = Array();
 
// Create a TCP Stream socket
echo "Creating socket...\n";
$sock = socket_create(AF_INET, SOCK_STREAM, 0);
if ( socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1) === false )
{ 
    echo "Error:" . socket_strerror( socket_last_error( $sock ) );
    exit();
}
 
// Bind TCP Socket
echo "Binding socket...\n";
if( socket_bind($sock, $address, $port) === false )
{
    echo "Error:" . socket_strerror( socket_last_error( $sock ) );
    exit();
}
 
// Start listening
socket_listen( $sock );
while( true ) 
{
    $count=0;
    // Make an array of sockets to check if they have data
    $read = array( $sock );
    foreach( $clients as $client_index => $client )
    {        
        $read[] = $client[ 'sock' ];
    }
 
    // Check which sockets have data
    socket_select($read, $w = NULL, $e = NULL, 0);
 
    /* if a new connection is being made on the MAIN socket */
    if ( in_array( $sock, $read ) == true ) 
    {
        $client = array();
        $client[ 'sock' ] = socket_accept( $sock );
 
        socket_getpeername( $client[ 'sock' ], $address, $port );
 
        echo "* Connection from " . $address . ":" . $port . "\n";
 
        $client[ 'address' ] = $address;
        $client[ 'port' ] = $port;
        $client[ 'filename' ] = time() . ".txt";
        $clients[] = $client;
    } // end if in_array
 
    // Cycle each client
    foreach( $clients as $client_index => $client) // for each client
    {
        // If socket in sockets with data`
        if( in_array( $client[ 'sock' ], $read) === true )
        {
            $buffer = socket_read( $client[ 'sock' ], 2048);
            if( $buffer == null )
            {
                // Zero length string meaning disconnected
                echo "* Client disconnected\n";
                unset( $clients[ $client_index ] );
                continue;
            }
 
            $location = '/var/www/monitoring/IPDR_RECORDS/' . $client[ 'address' ] . "/";
            if( file_exists( $location ) === false )
            {
                //echo "* Creating folder " . $location . "\n";
                //mkdir( $location );
            }
 
            $location .= $client[ 'filename' ];
            echo "{$count} * Writing on " . $location . "\n";
	    $count++;
	    if($client['address'] == '192.168.251.10') {
		    file_put_contents( $location, $buffer, FILE_APPEND );
	    }
        }
    }
    // Sleep 10 micro seconds (just to avoid overloading the CPU)
    usleep( 10 );
}
 
// Close the master socket (Will never be called for now)
socket_close( $sock );
