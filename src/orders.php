<?
//error_reporting(0);

// for output response
function out_res($res_code, $data) {
	http_response_code($res_code);
    echo json_encode($data);
}

// calculate distance between two points by Distance API
function cal_dist($ori_lat, $ori_lon, $dest_lat, $dest_lon) {
	$dist_api_url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$ori_lat.",".$ori_lon."&destinations=".$dest_lat.",".$dest_lon."&key=".getenv("DISTANCE_API_KEY");

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $dist_api_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	if(curl_exec($ch) === false) {
		out_res(500, "Cannot reach distance API - ".curl_error($ch));
		exit;
	} else {
	    $result = json_decode(curl_exec($ch));
	    $row = $result->{"rows"}[0];
	    $element = $row->{"elements"}[0];
	    $distance = $element->{"distance"};

	    return $distance->{"value"};
	}

}

// build and secure mysql connection
$dblink = mysqli_connect("mysql_db", "miuser", "mipassword", "temp_db");

if (!$dblink) {
	$error_data = array('error' => mysqli_connect_error());
	out_res(500, $error_data);
	exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
     // place order request
	$req_json = file_get_contents('php://input');
	$req_obj = json_decode($req_json);

	$origin = $req_obj->{"origin"};
	$destination = $req_obj->{"destination"};

	if(is_array($origin) && is_array($destination)) {
		if(count($origin) == 2 && count($destination) == 2) {
			if( 
				($origin[0] >= -90 && $origin[0] <= 90) && 
				($destination[0] >= -90 && $destination[0] <= 90)  &&
				($origin[1] >= -180 && $origin[1] <= 180) && 
				($destination[1] >= -180 && $destination[1] <= 180) 
			) {
				$distance = cal_dist($origin[0], $origin[1], $destination[0], $destination[1]);
				$uuid = uniqid();

				$insert_sql = "INSERT INTO orders (uuid, start_lat, start_long, end_lat, end_long, distance, create_date) VALUES ('".$uuid."', '".$origin[0]."','".$origin[1]."','".$destination[0]."','".$destination[1]."',".$distance.", NOW()) ";

				if (mysqli_query($dblink, $insert_sql) === TRUE) {
					$succ_data = array('id' => $uuid, 'distance' => $distance,  'status' => 'UNASSIGNED');
					out_res(200, $succ_data);
				} else {
					$error_data = array('error' => "Cannot insert into database");
					out_res(400, $error_data);
				    exit;
				}

			} else {
				$error_data = array('error' => "Invalid range for latitude or longitude");
				out_res(400, $error_data);
			    exit;
			}

		} else {
			$error_data = array('error' => "Invalid format of input");
			out_res(400, $error_data);
		    exit;
		}

	} else{
		$error_data = array('error' => "Invalid format of input");
		out_res(400, $error_data);
	    exit;
	}
	
} else if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
	$req_json = file_get_contents('php://input');
	$req_obj = json_decode($req_json);

	if($req_obj->{"status"} === "TAKEN") {
		$config_sql = "START TRANSACTION";
		mysqli_query($dblink, $config_sql);

		$select_sql = "SELECT * FROM orders WHERE uuid='".htmlspecialchars($_GET["uuid"])."' and status=0 and deleted=0 FOR UPDATE";
		if ($result = mysqli_query($dblink, $select_sql)) {
			$update_sql = "UPDATE orders SET status=1, assign_date=NOW() where uuid='".htmlspecialchars($_GET["uuid"])."' and deleted=0 and status=0 ";
			mysqli_query($dblink, $update_sql);

			$config_sql = "COMMIT";
			mysqli_query($dblink, $config_sql);

			$succ_data = array('status' => 'SUCCESS');
			out_res(200, $succ_data);
		} else {
			$config_sql = "COMMIT";
			mysqli_query($dblink, $config_sql);

			$error_data = array('error' => "Order has been taken");
			out_res(400, $error_data);
		    exit;
		}
	}

} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if(is_numeric($_GET["page"]) && is_numeric($_GET["limit"])) {
		$page = intval($_GET["page"]) - 1;
		if($page < 0) {
			$page = 0;
		}
		$limit = intval($_GET["limit"]);
		if($limit <= 0) {
			$limit = 10;
		}

		$select_sql = "SELECT uuid, distance, status FROM orders WHERE deleted=0 limit $page, $limit";
		$succ_data = array();
		if ($result = mysqli_query($dblink, $select_sql)) {
		    while ($row = mysqli_fetch_assoc($result)) {

		    	$status = $row["status"] == 0 ? "UNASSIGNED" : "TAKEN";
		    	$row_data = array('id' => $row["uuid"], 'distance' => $row["distance"], 'status' => $status);
		    	array_push($succ_data, $row_data);
		    }

			mysqli_free_result($result);

			out_res(200, $succ_data);
		}
	} else {
		$error_data = array('error' => "Parameter page and limit must in integer");
		out_res(400, $error_data);
	}

}


// close mysql connection
mysqli_close($dblink);

?>