<?php



class WeatherAPI {
    var $wLat = "60.6592076";//"61.04";
    var $wLon = "25.6415902";//"28.11";
    var $currentTemp = 0;    
    
    public function fetchCurrentTemp($iLat, $iLng){
        $this->wLat = $iLat;
        $this->wLng = $iLng;
        $json = file_get_contents('http://api.openweathermap.org/data/2.5/weather?lat='.$this->wLat.'&lon='.$this->wLon.'&units=metric&appid=17173eb1682a6e673983451f80290045'); 

        $data = json_decode($json,true);
        $this->currentTemp = $data['main']['temp'];
        
    }
    
    public function fetchTempAtSpecificTime($iDate, $iTime){
        
        //$today = date("Y-m-d");
        //if($iDate < $today)
          //  $url = 'http://history.openweathermap.org/data/2.5/history?lat='.$this->wLat.'&lon='.$this->wLon.'&units=metric&appid=17173eb1682a6e673983451f80290045';
       // else 
            $url = 'http://api.openweathermap.org/data/2.5/forecast?lat='.$this->wLat.'&lon='.$this->wLon.'&units=metric&appid=17173eb1682a6e673983451f80290045';
        $searchTime = $iTime;
        while(($searchTime % 3 )!= 0){
            $searchTime++;
        }
        $json = file_get_contents($url);
        $data = json_decode($json,true);
        $s=count($data['list']);
        for ($i=0;$i<$s;$i++) {
            list($date, $time) = split(" ",$data['list'][$i]['dt_txt']);
            $hour = split(":", $time)[0];
            //2017-04-27 15:00:00
            if(($date == $iDate) && ($hour == $searchTime)) {
                //$this->$currentTemp = $data['list'][$i]['main']['temp'];
                //echo "<br>Ennuste ajalle ",$data['list'][$i]['dt_txt']," on ",$data['list'][$i]['main']['temp']." astetta.<br>";
                break;
            }
            //echo "Ennuste ajalle ",$data['list'][$i]['dt_txt']," on ",$data['list'][$i]['main']['temp']." astetta.<br>";
        }
    }
    
    public function getCurrentTemp(){
        //$this->fetchCurrentTemp();
        return $this->currentTemp;
        
    }
    
    
}

?> 