<?php
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
            return 'どんよりした雲<br class="nosp">（雲85~100%）';
            break;
        case 'broken clouds':
            return '千切れ雲<br class="nosp">（雲51~84%）';
            break;
        case 'scattered clouds':
            return '散らばった雲<br class="nosp">（雲25~50%）';
            break;
        case 'few clouds':
            return '少ない雲<br class="nosp">（雲11~25%）';
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
    if (isset($_GET['area'])) {
        if (!array_key_exists($_GET['area'], $areas)) {
            throw new Exception('不正なパラメーターです。 セレクトボックスから選択してください。');
        }
    }

    // ID
    $area_id = $_GET['area'] ? $_GET['area'] : array_shift(array_keys($areas));

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
    $cnt++; endforeach;
    error_log($now_des);

} catch (Exception $e) {
    echo '<p class="m-normal-txt">' . $e->getMessage() . '</p>';
}

?>