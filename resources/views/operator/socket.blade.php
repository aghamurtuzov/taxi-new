<?php

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);


$host = "85.132.4.86";
$port = 5038;
$ret = '';

// open a client connection
$fp = fsockopen($host, $port, $errno, $errstr);
fputs($fp, "Action: Login\r\n");
fputs($fp, "Username: admin\r\n");
fputs($fp, "Secret: asterisk001\r\n\r\n");

if (!$fp) {
	echo "Socketə Bağlanmadı";
} else {

// get the welcome message
	while ($ret = fgets($fp, 1024)) {
		echo $ret;
		if (substr($ret, 0, 6) == "Event:") {
			$e = explode(':', $ret);
			$event = trim($e[1]);
		}
		if (isset($event) && $event == "DialBegin") {
			$data = explode(':', $ret);

			if ($data[0] == "CallerIDNum") {
				$cidnum = trim($data[1]);
				echo 'Zeng eden ------ ' . $cidnum . '<br/>';
				exit;
			}

			if ($data[0] == "CallerIDName") {
				$cidname = trim($data[1]);
			}

			if ($data[0] == "DialString") {
				if (substr(trim($data[1]), 0, 3) == 'SIP' || is_numeric(trim($data[1]))) {
					if (is_numeric(trim($data[1]))) {
						$exten = trim($data[1]);
					} else {
						$e = explode('/', trim($data[1]));
						$exten = trim($e[1]);
					}

					echo 'Zengi qebul eden operator ------ ' . $exten . '<br/>';
					echo 'Bitdi';

				} else {
					$e = explode('@', trim($data[1]));
					$dialed = trim($e[0]);
					$e = explode('/', trim($dialed));
					$exten = trim($e[0]);

					echo 'Zengi qebul eden operator ------ ' . $exten . '<br/>';
					echo 'Bitdi';
					exit;
					// header("Location: http://cc.smarttaxi.cloud/taxi");
				}
			}
		}
	}
}
echo 'Bitdi';
fclose($fp);

?>
