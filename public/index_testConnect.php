        <?php
		
	       echo phpinfo();
		
        //   $serverName = "biapi.acs.vn";
        //   $connectionOptions = array(  "Database" => "biapi_dev","Uid" => "bi_dev_read", "PWD" => "123456acs@");
        //   //Establishes the connection
        //    $conn = sqlsrv_connect($serverName, $connectionOptions);
        //  if($conn)
		// 	 echo "Connected!";
		//  else 
		// 	 echo "Not Connected!";
		 
		$servername = "biapi.acs.vn";
		$username = "bi_dev_read";
		$password = "123456acs@";
		$database = "biapi_dev";
		$port = "1433";
		try {
			$conn = new PDO("sqlsrv:server=$servername,$port;Database=$database;ConnectionPooling=0", $username, $password,
				array(
					PDO::ATTR_PERSISTENT => true,
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
				)
			);
			echo ("Connnected");

		} catch (PDOException $e) {
			echo ("Error connecting to SQL Server: " . $e->getMessage());
		}
		 
		// try {
        //     DB::connection()->getPdo();
        //     if(DB::connection()->getDatabaseName()){
        //         echo "Yes! Successfully connected to the DB: " . DB::connection()->getDatabaseName();
        //     }else{
        //         die("Could not find the database. Please check your configuration.");
        //     }
        // } catch (\Exception $e) {
        //     die("Could not open connection to database server.  Please check your configuration.");
        // }
		 
		 
         ?>
