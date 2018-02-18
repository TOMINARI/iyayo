<?php
	require_once __DIR__.'/vendor/autoload.php';
	
	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
	
	$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);

	$signature = $_SERVER['HTTP_'.\LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];

	$events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);
	
	foreach($events as $event) 	{
		if($event instanceof \LINE\LINEBot\Event\MessageEvent\ImageMessage) {
			reciveImage($bot, $event);	
			//replyTextMessage($bot, $event->getReplyToken(), 'かっ、なんちゅう写真け');
		}
		elseif($event instanceof \LINE\LINEBot\Event\MessageEvent\TextMessage) {
			replyTextMessage($bot, $event->getReplyToken(), 'なに言うとるがけ');
		} else {
			replyTextMessage($bot, $event->getReplyToken(), 'ほげ？');
		}
	}
	
	function replyTextMessage($bot, $replyToken, $text)
	{
		$response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text));
		
		if(!$response->isSucceeded()) {
			error_log('Failed! '.$response->getHTTPStatus.' '.$response->getRawBody());
		}
	}

	function reciveImage($bot, $event)
	{
		$content = $bot->getMessageContent($event->getMessageId());
		
		$headers = $content->getHeaders();
		
		$directory_path = 'tmp';
		
		$filename = uniqid();
		
		$extension = explode('/', $headers['Content-Type'])[1];
		
		if(!file_exists($directory_path)) {
			if(mkdir($directory_path, 0777, true))
			{
				chmod($directory_path, 0777);
			}
		}
		
		$filepath = $directory_path.'/'.$filename.'.'.$extension;
		
		file_put_contents($filepath, $content->getRawBody());
		
		$url = 'https://westcentralus.api.cognitive.microsoft.com/vision/v1.0/analyze?visualFeatures=Description&language=en';

		$curl = curl_init();

		curl_setopt_array(
		    $curl,
		    array(
		        CURLOPT_URL => $url,
		        CURLOPT_HTTPHEADER => array(
		            'Content-Type: application/octet-stream',
		            'Ocp-Apim-Subscription-Key:fb27886c5d0a4c3c88dcf4c7f70f5f1b'
		        ),
		        CURLOPT_POST => true,
		        CURLOPT_POSTFIELDS  => $content->getRawBody(),
		        CURLOPT_RETURNTRANSFER => true,
		        CURLOPT_BINARYTRANSFER => true,
		    )
		);
		
		$response = curl_exec($curl);

		curl_close($curl);

		$imageResult = json_decode($response);

		error_log($imageResult->description->tags[0]);

//		replyTextMessage($bot, $event->getReplyToken(), $imageResult->tags);

		
	}

?>


