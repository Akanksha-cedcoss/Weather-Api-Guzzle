<?php
session_start();

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Manager;
use GuzzleHttp\Client;

class IndexController extends Controller
{
    /**
     * city search
     *
     * @return void
     */
    public function indexAction()
    {
        if ($_POST) {
            $location = urlencode($this->request->getPost('search_box'));
            $result =  $this->client->request('GET', '?key=0bab7dd1bacc418689b143833220304&q=' . $location . '')->getBody();
            $this->view->locations = json_decode($result, true);
        }
    }
    public function singleCityAction($lat, $lon)
    {
        /**
         * if location is choosen
         */
        $url = '?key=0bab7dd1bacc418689b143833220304&q=' . $lat . ',' . $lon . '';
        $result =  $this->client->request('GET', 'current.json' . $url)->getBody();
        $result = json_decode($result, true);
        $this->view->city = $result;
        /**
         * if any button is choosen
         */
        if (isset($_POST["mybutton"])) {
            $search = $_POST["mybutton"];
            switch ($search) {
                    /**
                 * current weather
                 */
                case 'Current weather':
                    $str = array(
                        '<img style="width: 70px; height:50px;" src="' . $result['current']['condition']['icon'] . '">Current Weather is ' . $result['current']['condition']['text'],
                        'temperature today in centigrade : ' . $result['current']['temp_c']
                    );
                    $this->view->result = $str;
                    break;
                    /**
                     * tomorrow's weather forecast
                     */
                case 'Forecast':
                    $now = date("Y-m-d", strtotime("+1 days"));
                    $result =  $this->client->request('GET', 'history.json' . $url . '&dt=' . $now)->getBody();
                    $result = json_decode($result, true);
                    $str = array(
                        '<img style="width: 70px; height:50px;" src="' . $result['forecast']['forecastday'][0]['day']['condition']['icon'] . '">Tomorrow Weather forecast is ' . $result['forecast']['forecastday'][0]['day']['condition']['text'],
                        'Maximum temperature in C : ' . $result['forecast']['forecastday'][0]['day']['maxtemp_c'],
                        'Maximum temperature in C : ' . $result['forecast']['forecastday'][0]['day']['mintemp_c']
                    );
                    $this->view->result = $str;
                    break;
                    /**
                     * weather astronomy
                     */
                case 'Astronomy':
                    $result =  $this->client->request('GET', 'astronomy.json?key=0bab7dd1bacc418689b143833220304&q=' . $lat . ',' . $lon . '')->getBody();
                    $result = json_decode($result, true);
                    $str = array(
                        'Astro Sunrise : ' . $result['astronomy']['astro']['sunrise'],
                        'Astro Sunset : ' . $result['astronomy']['astro']['sunset'],
                        'Astro Moonrise: ' . $result['astronomy']['astro']['moonrise'],
                        'Astro Moonset: ' . $result['astronomy']['astro']['moonset']
                    );
                    $this->view->result = $str;
                    break;
                    /**
                     * weather forecast of yesterday
                     */
                case 'History':
                    $now = date("Y-m-d", strtotime("-1 days"));
                    $result =  $this->client->request('GET', 'history.json' . $url . '&dt=' . $now)->getBody();
                    $result = json_decode($result, true);
                    $str = array(
                        '<img style="width: 70px; height:50px;" src="' . $result['forecast']['forecastday'][0]['day']['condition']['icon'] . '">Yesterday Weather forecast is ' . $result['forecast']['forecastday'][0]['day']['condition']['text'],
                        'Maximum temperature in C was : ' . $result['forecast']['forecastday'][0]['day']['maxtemp_c'],
                        'Maximum temperature in C was : ' . $result['forecast']['forecastday'][0]['day']['mintemp_c']
                    );
                    $this->view->result = $str;
                    break;
                    /**
                     * time zone of choosen city
                     */
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
                    /**
                     * sports match
                     */
                case 'Sports':
                    $result =  $this->client->request('GET', 'sports.json' . $url)->getBody();
                    $result = json_decode($result, true);
                    $str = array();
                    foreach ($result as $k => $v) {
                        foreach ($v as $p) {
                            array_push($str, $k . ' Match between ' . $p['match'] . ' is at stadium ' . $p['stadium'] . ',' . $p['country'] . ' today.');
                        }
                    }
                    $this->view->result = $str;
                    break;
                    /**
                     * weather alerts if any
                     */
                case 'Weather Alerts':
                    $result =  $this->client->request('GET', 'forecast.json' . $url . '&days=1&aqi=no&alerts=yes')->getBody();
                    $result = json_decode($result, true)['alerts'];
                    $str = array();
                    foreach ($result as $k => $v) {
                        foreach ($v as $p) {
                            array_push($str, 'Alert : ' . $p['headline'] . ' <hr>Description : ' . $p['desc']);
                        }
                    }
                    $this->view->result = $str;
                    break;
                    /**
                     * quality of air
                     */
                case 'Air Quality':
                    $result =  $this->client->request('GET', 'forecast.json' . $url . '&days=1&aqi=yes')->getBody();
                    $result = json_decode($result, true)['current']['air_quality'];
                    $str = array(
                        'Level of carbon monooxide : ' . $result['co'],
                        'Level of nitrogen dioxide : ' . $result['no2'],
                        'Level of ozone : ' . $result['o3'],
                        'Level of sulfer dioxide : ' . $result['so2'],
                        'pm2_5 : ' . $result['pm2_5'],
                    );
                    $this->view->result = $str;
                    break;
            }
        }
    }
}
