<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

// 複数のメッセージをまとめて返信。引数はLINEBot、
// 返信先、メッセージ(可変長引数)
function replyMultiMessage($bot, $replyToken, ...$msgs) {
  // MultiMessageBuilderをインスタンス化
  $builder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
  // ビルダーにメッセージを全て追加
  foreach($msgs as $value) {
    $builder->add($value);
  }
  $response = $bot->replyMessage($replyToken, $builder);
  if (!$response->isSucceeded()) {
    error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
  }
}

// 日本語に変換
function getTranslation($arg)
{
    switch ($arg) {
        case 'overcast clouds':
            return 'どんよりした雲（雲85~100%）';
            break;
        case 'broken clouds':
            return '千切れ雲（雲51~84%）';
            break;
        case 'scattered clouds':
            return '散らばった雲（雲25~50%）';
            break;
        case 'few clouds':
            return '少ない雲（雲11~25%）';
            break;
        case 'light rain':
            return '小雨';
            break;
        case 'moderate rain':
            return '雨';
            break;
        case 'heavy intensity rain':
            return '大雨';
            break;
        case 'very heavy rain':
            return '激しい大雨';
            break;
        case 'clear sky':
            return '快晴';
            break;
        case 'shower rain':
            return 'にわか雨';
            break;
        case 'light intensity shower rain':
            return '小雨のにわか雨';
            break;
        case 'heavy intensity shower rain':
            return '大雨のにわか雨';
            break;
        case 'thunderstorm':
            return '雷雨';
            break;
        case 'snow':
            return '雪';
            break;
        case 'mist':
            return '靄';
            break;
        case 'tornado':
            return '強風';
            break;
        default:
            return $arg;
    }
}

function getWeather($type, $lat, $lon)
{
    $api_base = 'https://api.openweathermap.org/data/2.5/';
    $api_parm = '?lat=' . $lat . '&lon=' . $lon . '&units=metric&appid=79ff4330900ac4740fbb13d69d959a1d';
    $api_url = $api_base . $type . $api_parm;

    return json_decode(file_get_contents($api_url), true);
}

// Composerでインストールしたライブラリを一括読み込み
require_once __DIR__ . '/vendor/autoload.php';

// アクセストークンを使いCurlHTTPClientをインスタンス化
$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
// CurlHTTPClientとシークレットを使いLINEBotをインスタンス化
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);
// LINE Messaging APIがリクエストに付与した署名を取得
$signature = $_SERVER['HTTP_' . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];

// 署名が正当かチェック。正当であればリクエストをパースし配列へ
// 不正であれば例外の内容を出力
try {
  $events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);
} catch(\LINE\LINEBot\Exception\InvalidSignatureException $e) {
  error_log('parseEventRequest failed. InvalidSignatureException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\InvalidEventRequestException $e) {
  error_log('parseEventRequest failed. InvalidEventRequestException => '.var_export($e, true));
}

// 配列に格納された各イベントをループで処理
foreach ($events as $event) {
  // MessageEventクラスのインスタンスでなければ処理をスキップ
  if (!($event instanceof \LINE\LINEBot\Event\MessageEvent)) {
    error_log('Non message event has come');
    continue;
  }
  // TextMessageクラスのインスタンスの場合
  if ($event instanceof \LINE\LINEBot\Event\MessageEvent\TextMessage) {
    // 入力されたテキストを取得
    $location = $event->getText();
  }

    $http_client = new Client();
    $url = 'https://maps.googleapis.com/maps/api/geocode/json';
    $api_key = 'AIzaSyCIaJrhuadNLAhjGCpVhfDzvwzxEW_wSZs';

    /*
     Geocoding (latitude/longitude lookup)
     住所から緯度経度を取得
    */

    try {
          $response = $http_client->request('GET', $url, [
         'headers' => [
         'Accept' => 'application/json',
        ],
        'query' => [
            'key' => $api_key,
            'language' => 'ja',
            'address' => $location,
        ],
        'verify' => false,
       ]);
    } catch (ClientException $e) {
       throw $e;
    }

    $body = $response->getBody();
    $json = json_decode($body);

     if ($json->status == "ZERO_RESULTS") {
        $bot->replyText($event->getReplyToken(), "類似する地名が見つかりません");

        throw new Exception('類似する地名が見つかりません。');
    }

    $lat = $json->results[0]->geometry->location->lat;
    $lon = $json->results[0]->geometry->location->lng;
    $formatted_address = $json->results[0]->formatted_address;
   
    $city = $location;
    // 現在の天気
    $response_now = getWeather('weather', $lat, $lon);

    $now_des = getTranslation($response_now['weather'][0]['description']); // 現在の天気説明
    $now_temp = $response_now['main']['temp']; // 現在の気温
    $now_humidity = $response_now['main']['humidity']; // 現在の湿度

    //スタンプを返信
    if(preg_match('/cloud/',$response_now['weather'][0]['description'])){
        replyMultiMessage($bot, $event->getReplyToken(),
            new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("地名：$formatted_address"),
            new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("現在の天気：\n$now_des\n温度：$now_temp ℃\n湿度：$now_humidity ％"),
            new \LINE\LINEBot\MessageBuilder\StickerMessageBuilder(11539, 52114110)
        );
    }else if(preg_match('/rain/',$response_now['weather'][0]['description'])){
        replyMultiMessage($bot, $event->getReplyToken(),
            new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("地名：$formatted_address"),
            new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("現在の天気：\n$now_des\n温度：$now_temp ℃\n湿度：$now_humidity ％"),
            new \LINE\LINEBot\MessageBuilder\StickerMessageBuilder(11538, 51626522)
        );    
    }else {
        replyMultiMessage($bot, $event->getReplyToken(),
            new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("地名：$formatted_address"),
            new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("現在の天気：\n$now_des\n温度：$now_temp ℃\n湿度：$now_humidity ％"),
            new \LINE\LINEBot\MessageBuilder\StickerMessageBuilder(11537, 52002771)
        );   
    }

}

?>