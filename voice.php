<?php

const FORMAT_PCM = "lpcm";
const FORMAT_OPUS = "oggopus";

$token = "t1.9euelZqTl8-axpeKyJzIl87Im82Yju3rnpWam8ySzIyaz5qSnJKeypWPic3l9Pcve2R6-e9kNx2i3fT3bylievnvZDcdog.q4PKZ8PVR0LZSeUiY9o0lCeEFxsaGc63pUNBc8mK2303sg8CgU67Vt9AjXueW4VMq2xkSDUqRLYzGP4ZKw3kCw"; # IAM-токен
print($token);
$folderId = "b1gv0oute3vr7pdei4cv"; # Идентификатор каталога
$url = "https://tts.api.cloud.yandex.net/speech/v1/tts:synthesize";
$files = scandir($argv[1]);
foreach($files as $key => $value){
	if($value == '.' || $value == '..' || $value == 'sound'){
		continue;
	}

	$text = file_get_contents($argv[1]."/".$value);
	$post = "text=" . urlencode($text) . "&lang=ru-RU&folderId=${folderId}&format=" . FORMAT_PCM . "&voice=ermil&emotion=good&speed=1.2&sampleRateHertz=16000";
	$headers = ['Authorization: Bearer ' . $token];
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, false);
	if ($post !== false) {
    		curl_setopt($ch, CURLOPT_POST, 1);
    		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	}
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


	$response = curl_exec($ch);
	if (curl_errno($ch)) {
    		print "Error: " . curl_error($ch);
	}
	if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
    		$decodedResponse = json_decode($response, true);
    		echo "Error code: " . $decodedResponse["error_code"] . "\r\n";
    		echo "Error message: " . $decodedResponse["error_message"] . "\r\n";
	} else {
		$file_name = preg_replace('~\.txt~', '', $value);

		if(!file_exists($argv[1]."/sound")){
			mkdir($argv[1]."/sound");
		}

		if(!file_exists($argv[1]."/sound/wav")){
                        mkdir($argv[1]."/sound/wav");
                }

    		file_put_contents($argv[1]."/sound/".$file_name.".raw", $response);
	}
	curl_close($ch);
	shell_exec("sox -r 16000 -b 16 -e signed-integer -c 1 ".$argv[1]."/sound/".$file_name.".raw ".$argv[1]."/sound/wav/".$file_name.".wav");
}
