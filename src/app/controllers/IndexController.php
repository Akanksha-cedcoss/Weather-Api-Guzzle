<?php
session_start();

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Manager;
use GuzzleHttp\Client;

class IndexController extends Controller
{
    public function indexAction()
    {
        if ($_POST) {
            $location = urlencode($this->request->getPost('search_box'));
            $client = new GuzzleHttp\Client(['base_uri' => 'http://api.weatherapi.com/v1/search.json']);
            $result =  $client->request('GET', '?key=0bab7dd1bacc418689b143833220304&q=' . $location . '')->getBody();
            $this->view->locations = json_decode($result, true);
        }
    }
    public function singleCityAction($lat, $lon)
    {
        $client = new GuzzleHttp\Client(['base_uri' => 'http://api.weatherapi.com/v1/']);
        $url = '?key=0bab7dd1bacc418689b143833220304&q=' . $lat . ',' . $lon . '';
        $result =  $client->request('GET', 'current.json' . $url)->getBody();
        $result = json_decode($result, true);
        $this->view->city = $result;
        if (isset($_POST["mybutton"])) {
            $search = $_POST["mybutton"];
            switch ($search) {
                case 'Current weather':
                    $str = array(
                        '<img style="width: 70px; height:50px;" src="' . $result['current']['condition']['icon'] . '">Current Weather is ' . $result['current']['condition']['text'],
                        'temperature today in centigrade : ' . $result['current']['temp_c']
                    );
                    $this->view->result = $str;
                    break;

                case 'Forecast':
                    $now = date("Y-m-d", strtotime("+1 days"));
                    $result =  $client->request('GET', 'history.json' . $url . '&dt=' . $now)->getBody();
                    $result = json_decode($result, true);
                    $str = array(
                        '<img style="width: 70px; height:50px;" src="' . $result['forecast']['forecastday'][0]['day']['condition']['icon'] . '">Tomorrow Weather forecast is ' . $result['forecast']['forecastday'][0]['day']['condition']['text'],
                        'Maximum temperature in C : ' . $result['forecast']['forecastday'][0]['day']['maxtemp_c'],
                        'Maximum temperature in C : ' . $result['forecast']['forecastday'][0]['day']['mintemp_c']
                    );
                    $this->view->result = $str;
                    break;

                case 'Astronomy':
                    $result =  $client->request('GET', 'astronomy.json?key=0bab7dd1bacc418689b143833220304&q=' . $lat . ',' . $lon . '')->getBody();
                    $result = json_decode($result, true);
                    $str = array(
                        'Astro Sunrise : ' . $result['astronomy']['astro']['sunrise'],
                        'Astro Sunset : ' . $result['astronomy']['astro']['sunset'],
                        'Astro Moonrise: ' . $result['astronomy']['astro']['moonrise'],
                        'Astro Moonset: ' . $result['astronomy']['astro']['moonset']
                    );
                    $this->view->result = $str;
                    break;
                case 'History':
                    $now = date("Y-m-d", strtotime("-1 days"));
                    $result =  $client->request('GET', 'history.json' . $url . '&dt=' . $now)->getBody();
                    $result = json_decode($result, true);
                    $str = array(
                        '<img style="width: 70px; height:50px;" src="' . $result['forecast']['forecastday'][0]['day']['condition']['icon'] . '">Yesterday Weather forecast is ' . $result['forecast']['forecastday'][0]['day']['condition']['text'],
                        'Maximum temperature in C was : ' . $result['forecast']['forecastday'][0]['day']['maxtemp_c'],
                        'Maximum temperature in C was : ' . $result['forecast']['forecastday'][0]['day']['mintemp_c']
                    );
                    $this->view->result = $str;
                    break;
                case 'Time Zone':
                    $result =  $client->request('GET', 'timezone.json' . $url)->getBody();
                    $result = json_decode($result, true);
                    $str = array(
                        'Time Zone : ' . $result['location']['tz_id'],
                        'Local Time is : ' . $result['location']['localtime'],
                        'Latitude : ' . $result['location']['lat'],
                        'Longitude : ' . $result['location']['lon']
                    );
                    $this->view->result = $str;
                    break;
                case 'Sports':
                    $result =  $client->request('GET', 'sports.json' . $url)->getBody();
                    $result = json_decode($result, true);
                    $str = array();
                    foreach ($result as $k => $v) {
                        foreach ($v as $p) {
                            array_push($str, $k . ' Match between ' . $p['match'] . ' is at stadium ' . $p['stadium'] . ',' . $p['country'] . ' today.');
                        }
                    }
                    $this->view->result = $str;
                    break;
                case 'Weather Alerts':
                    $result =  $client->request('GET', 'forecast.json' . $url . '&days=1&aqi=no&alerts=yes')->getBody();
                    $result = json_decode($result, true)['alerts'];
                    $str = array();
                    foreach ($result as $k => $v) {
                        foreach ($v as $p) {
                            array_push($str, 'Alert : ' . $p['headline'] . ' <hr>Description : ' . $p['desc']);
                        }
                    }
                    $this->view->result = $str;
                    break;
                case 'Air Quality':
                    $result =  $client->request('GET', 'forecast.json' . $url . '&days=1&aqi=yes')->getBody();
                    $result = json_decode($result, true)['current']['air_quality'];
                    // echo '<pre>';
                    // print_r($result);
                    // die;
                    $str = array(
                        'Level of co : ' . $result['co'],
                        'Level of no2 : ' . $result['no2'],
                        'Level of o3 : ' . $result['o3'],
                        'Level of so2 : ' . $result['so2'],
                        'pm2_5 : ' . $result['pm2_5'],
                    );
                    $this->view->result = $str;
                    break;
                    
                    $this->view->result = $str;
                    break;
            }
        }
    }
}
