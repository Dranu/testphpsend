<?php
    //header("Location: https://test-version-heater-dranu.c9users.io/");
    require_once "Price/priceparsing.php";
    require_once "Weather/weatherAPI.php";
    require_once "Map/mapAPI.php";
    $elspot = new Elspot;
    $weather = new WeatherAPI;
    $map = new MapAPI;
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $time = 0;
        $date = 0;
        $water = 0;
        $temp = 0;
        $address = "";
        $errorString="<div style='float:left'>";
        if (empty($_POST["address"])) {
            $errorString=$errorString . "<br>No address was given, using Lappeenranta Kirkkokatu<br>";
            $address = "Kirkkokatu, 53100";
        } else {
            $address = $_POST["address"];
        }
        if (empty($_POST["date"])) {
            $errorString=$errorString . "No date was given, using this day <br>";
            //$date = date(d.m.Y);
            $date = "26.4.2017";
        } else {
            $date = $_POST["date"];
        }
        if (empty($_POST["time"])) {
            $errorString=$errorString . "No time was give, using default value <br>";
            $time = "12";
        } else {
            $time = $_POST["time"];
        }
            if (empty($_POST["waterAmount"])) {
            $errorString=$errorString . "No water mount was give, using default value of 100 litres<br>";
            $water = 100;
        } else {
            $water = $_POST["waterAmount"];
        }
            if (empty($_POST["waterTemp"])) {
            $errorString=$errorString . "No temperature was give, using default value of 38 celsius<br>";
            $temp = 38;
        } else {
            $temp = $_POST["waterTemp"];
        }
        $elspot->splitDate($date);
        $elspot->splitTime($time);
       /* echo '<br>';
        echo $date;
        echo '<br>';
        echo $time;*/
        $map-> findLatLng($address);
        $weather->fetchCurrentTemp($map->getLat(), $map->getLng());
        $elspot->setTemp($temp);
        $elspot->setTotalWater($water);

        //echo "<br>Current temperature: " . $weather->getCurrentTemp();
        $elspot->calculateWaterTempInPipes($weather->getCurrentTemp());
        $elspot->fetchHourlyPrice();
        $elspot->executeCalculations();
        $errorString=$errorString . "Current address: " . $map->getAddress();
        $errorString=$errorString . "</div>";
        
        if($water < 135){
            $string = "<div style='background-color:#1fef37; float:right; width:350px; margin-right:50px'>";
            $string = $string . "<strong>Wow! If this is your daily usage, you're using less than an average human, good job!</strong>";
        }
        else if ($water < 155){
            $string = "<div style='background-color:yellow; float:right; width:350px; margin-right:50px'>";
            $string = $string . "<strong>Hmm, if this is your daily usage then you're using roughly the average amount of water.</strong>";
        }
        else {
            $string = "<div style='background-color:red; float:right; width:350px; margin-right:50px'>";
            $string = $string . "<strong>You're using more water than an average human. I suggest you use less water by taking shorter showers or not letting the water flow without a purpose</strong>";
        }
        list($day, $month, $year) = split('[/.-]', $date);
        $newDate = $year . "-" . $month . "-" . $day;
        $elspot-> setOutTempWhenUsing($weather->fetchTempAtSpecificTime($newDate,$elspot->getTime()));
        $elspot-> setOutTempWhenHeating($weather->fetchTempAtSpecificTime($newDate,$elspot->getTime()));
        $weather->fetchTempAtSpecificTime("2017-04-27",$elspot->getTime());
        $string = $string . "<br>Here is how much warm water is needed: ";
        $string = $string . "<strong>" . $elspot->getWarmWater() . " litres</strong>";
        $string = $string . "<br>The best time to heat the water is at ";
        $string = $string . "<strong>" . $elspot->getBestTime() . " o'clock</strong> as it is cheapest then";
        $string = $string . "<br>Here is how much cold water is needed: ";
        $string = $string . "<strong>" . $elspot->getColdWater() . " litres</strong>";
        $string = $string . "<br>Here is how much total water is needed: ";
        $string = $string . "<strong>" . $elspot->getTotalWater() . " litres</strong>";
        $string = $string . "<br>Here is how much time is needed to heat it: ";
        $string = $string . "<strong>" . $elspot->getDuration() . " minutes</strong>";
        $string = $string . "<br>Here is how much electricity is consumed: ";
        $string = $string . "<strong>" . round($elspot->getElectricity() / 3600, 2) . " kWh</strong>";
        $string = $string . "<br>Here is how much heating it will cost you: ";
        $string = $string . "<strong>" . $elspot->getPrice() . " sents</strong>";
        $price = $elspot->getPrice();
        $string = $string . "<br>Here is how much the used water will cost you: ";
        $string = $string . "<strong>" . round($water * 0.448,2) . " sents</strong>";
        $price += $water * 0.448;
        $string = $string . "<br><strong>In total this will cost you: " . round($price, 2) . " sents</strong>";
        $string = $string . "</div>";
        echo $errorString;
        echo $string;
        
        
        
        
    }    



?>