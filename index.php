<?php

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
} catch(\LINE\LINEBot\Exception\UnknownEventTypeException $e) {
  error_log('parseEventRequest failed. UnknownEventTypeException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownMessageTypeException $e) {
  error_log('parseEventRequest failed. UnknownMessageTypeException => '.var_export($e, true));
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

  

//   // LocationMessageクラスのインスタンスの場合
//   else if ($event instanceof \LINE\LINEBot\Event\MessageEvent\LocationMessage) {
//     // Google APIにアクセスし緯度経度から住所を取得
//     $jsonString = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?language=ja&latlng=' . $event->getLatitude() . ',' . $event->getLongitude());
//     // 文字列を連想配列に変換
//     $json = json_decode($jsonString, true);
//     // 住所情報のみを取り出し
//     $addressComponentArray = $json['results'][0]['address_components'];
//     // 要素をループで処理
//     foreach($addressComponentArray as $addressComponent) {
//       // 県名を取得
//       if(in_array('administrative_area_level_1', $addressComponent['types'])) {
//         $prefName = $addressComponent['long_name'];
//         break;
//       }
//     }
//     // 東京と大阪の場合他県と内容が違うので特別な処理
//     if($prefName == '東京都') {
//       $location = '東京';
//     } else if($prefName == '大阪府') {
//       $location = '大阪';
//     // それ以外なら
//     } else {
//       // 要素をループで処理
//       foreach($addressComponentArray as $addressComponent) {
//         // 市名を取得
//         if(in_array('locality', $addressComponent['types']) && !in_array('ward', $addressComponent['types'])) {
//           $location = $addressComponent['long_name'];
//           break;
//         }
//       }
//     }
//   }

//   // 住所ID用変数
//   $locationId;
//   // XMLファイルをパースするクラス
//   $client = new Goutte\Client();
//   // XMLファイルを取得
//   $crawler = $client->request('GET', 'http://weather.livedoor.com/forecast/rss/primary_area.xml');
//   // 市名のみを抽出しユーザーが入力した市名と比較
//   foreach ($crawler->filter('channel ldWeather|source pref city') as $city) {
//     // 一致すれば住所IDを取得し処理を抜ける
//     if($city->getAttribute('title') == $location || $city->getAttribute('title') . "市" == $location) {
//       $locationId = $city->getAttribute('id');
//       break;
//     }
//   }
//   // 一致するものが無ければ
//   if(empty($locationId)) {
//     // 位置情報が送られた時は県名を取得済みなのでそれを代入
//     if ($event instanceof \LINE\LINEBot\Event\MessageEvent\LocationMessage) {
//       $location = $prefName;
//     }
//     // 候補の配列
//     $suggestArray = array();
//     // 県名を抽出しユーザーが入力した県名と比較
//     foreach ($crawler->filter('channel ldWeather|source pref') as $pref) {
//       // 一致すれば
//       if(strpos($pref->getAttribute('title'), $location) !== false) {
//         // その県に属する市を配列に追加
//         foreach($pref->childNodes as $child) {
//           if($child instanceof DOMElement && $child->nodeName == 'city') {
//             array_push($suggestArray, $child->getAttribute('title'));
//           }
//         }
//         break;
//       }
//     }
//     // 候補が存在する場合
//     if(count($suggestArray) > 0) {
//       // アクションの配列
//       $actionArray = array();
//       //候補を全てアクションにして追加
//       foreach($suggestArray as $city) {
//         array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder ($city, $city));
//       }
//       // Buttonsテンプレートを返信
//       $builder = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder(
//         '見つかりませんでした。',
//         new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder ('見つかりませんでした。', 'もしかして？', null, $actionArray));
//         $bot->replyMessage($event->getReplyToken(), $builder
//       );
//     }
//     // 候補が存在しない場合
//     else {
//       // 正しい入力方法を返信
//       replyTextMessage($bot, $event->getReplyToken(), '入力された地名が見つかりませんでした。市を入力してください。');
//     }
//     // 以降の処理はスキップ
//     continue;
//   }

//   // 住所IDが取得できた場合、その住所の天気情報を取得
//   $jsonString = file_get_contents('http://weather.livedoor.com/forecast/webservice/json/v1?city=' . $locationId);
//   // 文字列を連想配列に変換
//   $json = json_decode($jsonString, true);

//   // 形式を指定して天気の更新時刻をパース
//   $date = date_parse_from_format('Y-m-d\TH:i:sP', $json['description']['publicTime']);

//   // 予報が晴れの場合
//   if($json['forecasts'][0]['telop'] == '晴れ') {
//     // 天気情報、更新時刻、晴れのスタンプをまとめて送信
//     replyMultiMessage($bot, $event->getReplyToken(),
//       new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($json['description']['text'] . PHP_EOL . PHP_EOL .
//         '最終更新：' . sprintf('%s月%s日%s時%s分', $date['month'], $date['day'], $date['hour'], $date['minute'])),
//       new \LINE\LINEBot\MessageBuilder\StickerMessageBuilder(2, 513)
//     );
//   // 雨の場合
//   } else if($json['forecasts'][0]['telop'] == '雨') {
//     replyMultiMessage($bot, $event->getReplyToken(),
//       // 天気情報、更新時刻、雨のスタンプをまとめて送信
//       new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($json['description']['text'] . PHP_EOL . PHP_EOL .
//         '最終更新：' . sprintf('%s月%s日%s時%s分', $date['month'], $date['day'], $date['hour'], $date['minute'])),
//       new \LINE\LINEBot\MessageBuilder\StickerMessageBuilder(2, 507)
//     );
//   // 他
//   } else {
//     // 天気情報と更新時刻をまとめて返信
//     replyTextMessage($bot, $event->getReplyToken(), $json['description']['text'] . PHP_EOL . PHP_EOL .
//       '最終更新：' . sprintf('%s月%s日%s時%s分', $date['month'], $date['day'], $date['hour'], $date['minute']));
//   }
// }

// // テキストを返信。引数はLINEBot、返信先、テキスト
// function replyTextMessage($bot, $replyToken, $text) {
//   // 返信を行いレスポンスを取得
//   // TextMessageBuilderの引数はテキスト
//   $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text));
//   // レスポンスが異常な場合
//   if (!$response->isSucceeded()) {
//     // エラー内容を出力
//     error_log('Failed! '. $response->getHTTPStatus . ' ' . $response->getRawBody());
//   }
// }

// // 画像を返信。引数はLINEBot、返信先、画像URL、サムネイルURL
// function replyImageMessage($bot, $replyToken, $originalImageUrl, $previewImageUrl) {
//   // ImageMessageBuilderの引数は画像URL、サムネイルURL
//   $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder($originalImageUrl, $previewImageUrl));
//   if (!$response->isSucceeded()) {
//     error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
//   }
// }

// // 位置情報を返信。引数はLINEBot、返信先、タイトル、住所、
// // 緯度、経度
// function replyLocationMessage($bot, $replyToken, $title, $address, $lat, $lon) {
//   // LocationMessageBuilderの引数はダイアログのタイトル、住所、緯度、経度
//   $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder($title, $address, $lat, $lon));
//   if (!$response->isSucceeded()) {
//     error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
//   }
// }

// // スタンプを返信。引数はLINEBot、返信先、
// // スタンプのパッケージID、スタンプID
// function replyStickerMessage($bot, $replyToken, $packageId, $stickerId) {
//   // StickerMessageBuilderの引数はスタンプのパッケージID、スタンプID
//   $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\StickerMessageBuilder($packageId, $stickerId));
//   if (!$response->isSucceeded()) {
//     error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
//   }
// }

// // 動画を返信。引数はLINEBot、返信先、動画URL、サムネイルURL
// function replyVideoMessage($bot, $replyToken, $originalContentUrl, $previewImageUrl) {
//   // VideoMessageBuilderの引数は動画URL、サムネイルURL
//   $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\VideoMessageBuilder($originalContentUrl, $previewImageUrl));
//   if (!$response->isSucceeded()) {
//     error_log('Failed! '. $response->getHTTPStatus . ' ' . $response->getRawBody());
//   }
// }

// // オーディオファイルを返信。引数はLINEBot、返信先、
// // ファイルのURL、ファイルの再生時間
// function replyAudioMessage($bot, $replyToken, $originalContentUrl, $audioLength) {
//   // AudioMessageBuilderの引数はファイルのURL、ファイルの再生時間
//   $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\AudioMessageBuilder($originalContentUrl, $audioLength));
//   if (!$response->isSucceeded()) {
//     error_log('Failed! '. $response->getHTTPStatus . ' ' . $response->getRawBody());
//   }
// }

// // 複数のメッセージをまとめて返信。引数はLINEBot、
// // 返信先、メッセージ(可変長引数)
// function replyMultiMessage($bot, $replyToken, ...$msgs) {
//   // MultiMessageBuilderをインスタンス化
//   $builder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
//   // ビルダーにメッセージを全て追加
//   foreach($msgs as $value) {
//     $builder->add($value);
//   }
//   $response = $bot->replyMessage($replyToken, $builder);
//   if (!$response->isSucceeded()) {
//     error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
//   }
// }

// // Buttonsテンプレートを返信。引数はLINEBot、返信先、代替テキスト、
// // 画像URL、タイトル、本文、アクション(可変長引数)
// function replyButtonsTemplate($bot, $replyToken, $alternativeText, $imageUrl, $title, $text, ...$actions) {
//   // アクションを格納する配列
//   $actionArray = array();
//   // アクションを全て追加
//   foreach($actions as $value) {
//     array_push($actionArray, $value);
//   }
//   // TemplateMessageBuilderの引数は代替テキスト、ButtonTemplateBuilder
//   $builder = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder(
//     $alternativeText,
//     // ButtonTemplateBuilderの引数はタイトル、本文、
//     // 画像URL、アクションの配列
//     new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder ($title, $text, $imageUrl, $actionArray)
//   );
//   $response = $bot->replyMessage($replyToken, $builder);
//   if (!$response->isSucceeded()) {
//     error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
//   }
// }

// // Confirmテンプレートを返信。引数はLINEBot、返信先、代替テキスト、
// // 本文、アクション(可変長引数)
// function replyConfirmTemplate($bot, $replyToken, $alternativeText, $text, ...$actions) {
//   $actionArray = array();
//   foreach($actions as $value) {
//     array_push($actionArray, $value);
//   }
//   $builder = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder(
//     $alternativeText,
//     // Confirmテンプレートの引数はテキスト、アクションの配列
//     new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder ($text, $actionArray)
//   );
//   $response = $bot->replyMessage($replyToken, $builder);
//   if (!$response->isSucceeded()) {
//     error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
//   }
// }

// // Carouselテンプレートを返信。引数はLINEBot、返信先、代替テキスト、
// // ダイアログの配列
// function replyCarouselTemplate($bot, $replyToken, $alternativeText, $columnArray) {
//   $builder = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder(
//   $alternativeText,
//   // Carouselテンプレートの引数はダイアログの配列
//   new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder (
//    $columnArray)
//   );
//   $response = $bot->replyMessage($replyToken, $builder);
//   if (!$response->isSucceeded()) {
//     error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
//   }
    // エリアリスト
$areas = array(
    1850144 => '東京都',
    6940394 => '埼玉県（さいたま市）',
    2130404 => '北海道（江別市）',
    1856035 => '沖縄県（那覇市）',
    1853909 => '大阪府（大阪市）'
);

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

// アイコン取得
function getIcon($arg)
{
    switch ($arg) {
        case 'clear sky':
            return 'sun';
            break;
        case 'few clouds':
            return 'few_sun';
            break;
        case 'overcast clouds':
            return 'clouds';
            break;
        case 'broken clouds':
        case 'scattered clouds':
            return 'few_clouds';
            break;
        case 'light rain':
        case 'light intensity shower rain':
            return 'light_rain';
            break;
        case 'moderate rain':
        case 'shower rain':
            return 'moderate_rain';
            break;
        case 'heavy intensity rain':
        case 'very heavy rain':
        case 'heavy intensity shower rain':
            return 'heavy_rain';
            break;
        case 'thunderstorm':
            return 'thunderstorm';
            break;
        case 'snow':
            return 'snow';
            break;
        case 'mist':
            return '靄';
            break;
        case 'tornado':
            return 'tornado';
            break;
        default:
            return $arg;
    }
}

function getWeather($type, $area_id)
{
    $api_base = 'https://api.openweathermap.org/data/2.5/';
    $api_parm = '?id=' . $area_id . '&units=metric&appid=79ff4330900ac4740fbb13d69d959a1d';
    $api_url = $api_base . $type . $api_parm;

    return json_decode(file_get_contents($api_url), true);
}

// メイン処理
try {

    if (isset($location)) {
        if (!array_key_exists($location, $areas)) {
            throw new Exception('不正なパラメーターです。 セレクトボックスから選択してください。');
        }
    }

     error_log($location);
    // ID
    $area_id = $location ? $location : array_shift(array_keys($areas));

    // 5日間天気
    $response = getWeather('forecast', $area_id);

    $weather_list = $response['list']; // list配下
    $cnt = 0;

    $city_id = $response['city']['id'];
    $city = $areas[$city_id];

    // 現在の天気
    $response_now = getWeather('weather', $area_id);

    $now_des = getTranslation($response_now['weather'][0]['description']); // 現在の天気説明
    $now_icon = getIcon($response_now['weather'][0]['description']); // 現在の天気アイコン（自分用）
    // $now_icon = $response_now['weather'][0]['icon']; // 現在の天気アイコン（公式のアイコンを使用）
    $now_temp = $response_now['main']['temp']; // 現在の気温
    $now_humidity = $response_now['main']['humidity']; // 現在の湿度

    replyMultiMessage($bot, $event->getReplyToken(),
    new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("都市名：$city"),
    new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("現在の天気：\n$now_des\n温度：$now_temp ℃\n湿度：$now_humidity ％"),
  );


foreach( $weather_list as $items ):
    $temp = $items['main']['temp']; // 気温
    $temp_max = $items['main']['temp_max']; // 最高気温
    $temp_min = $items['main']['temp_min']; // 最低気温
    $humidity = $items['main']['humidity']; // 湿度
    $weather = $items['weather'][0]['main']; // 天気
    $weather_des = getTranslation($items['weather'][0]['description']); // 天気説明
    $weather_icon = getIcon($items['weather'][0]['description']); // 天気アイコン（自分用）
    // $weather_icon = $items['weather'][0]['icon']; // 天気アイコン（公式のアイコンを使用）
    $datetime = new DateTime();
    $datetime->setTimestamp( $items['dt'] )->setTimeZone(new DateTimeZone('Asia/Tokyo')); // 日時 - 協定世界時 (UTC)を日本標準時 (JST)に変換
    $date =  $datetime->format('Y年m月d日'); //　日付
    $time = $datetime->format('H:i'); // 時間
    $cnt++; 
    //$bot->replyText($event->getReplyToken(), "$date $time ：$weather_des $temp ,湿度：$humidity");
endforeach;

} catch (Exception $e) {
    echo '<p class="m-normal-txt">' . $e->getMessage() . '</p>';
}
}
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

?>
