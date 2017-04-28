<?php
    class Elspot {
        var $eDate = 0;
        var $eTimeHours = 0;
        var $BestTime = 0;
        var $eTimeMins = 0;
        var $ePriceTotal = 0;
        var $ePriceHour = 0;
        var $eElectricty = 0;
        var $eWarmWater = 0;
        var $eColdWater = 0;
        var $eTotalWater = 0;
        var $eDuration = 0;
        var $eTempOutWhenHeating = 0;
        var $eTempOutWhenUsing = 0;
        var $eTempWaterInPipes = 0;
        var $eTempWaterDesired = 0;
    
        public function splitTime($time){
            list($hours, $mins) = split('[.:]', $time);
           
           //correct row is hours + 2 
            $this->eTimeHours = intval($hours) + 2;
            $this->eTimeMins = $mins;
            // echo "Hours: $hours; Minutes: $mins";
            //return $hours;
        }
        public function splitDate($date){
            // Delimiters may be slash, dot, or hyphen
            list($day, $month, $year) = split('[/.-]', $date);
            
            //21-04 -> 28-04  : 8-1 columns
            if(($month == "04") &&($day < 29) &&($day > 20)){
                $this->eDate = 29- intval($day);
            }
            else{$this->eDate = 9;}
           // echo "Day: $day; Month: $month; Year: $year<br/>";
            
        }
        public function getTime(){return $this->eTimeHours-2;}
        public function getBestTime(){return $this->eBestTime;}
        public function getDate(){return $this->eDate;}
        public function getDuration(){return round($this->eDuration / 60,2);}
        public function getElectricity(){return round($this->eElectricty,2);}
        public function getWarmWater(){return round($this->eWarmWater, 2);}
        public function getColdWater(){return round($this->eColdWater,2);}
        public function getTotalWater(){return round($this->eTotalWater,2);}
        public function getPrice(){return round($this->ePriceTotal, 2);}
        
        public function setTemp($var) {$this->eTempWaterDesired = $var;}
        public function setTotalWater($var){ $this->eTotalWater = $var;}
        public function setPrice($var){$this->ePriceTotal = $var;}
        public function setOutTempWhenHeating($var){$this->eTempOutWhenHeating = $var;}
        public function setOutTempWhenUsing($var){$this->eTempOutWhenUsing = $var;}


        public function fetchHourlyPrice(){
            if($this->eDate < 9) {
                $file = file_get_contents("https://dranu.github.io/elspot/elspot.html", true);
                //$file = file_get_contents("PriceData/Elspot prices.html", true);
                $d = new DOMDocument;
                $mock = new DOMDocument;
                $d->loadHTML($file);//file_get_contents('/path/to/my.html'));
                $body = $d->getElementsByTagName('body')->item(0);
                $table = $body -> getElementsByTagName('table')->item(0);
                
                //get the correct time
                $row = $table->getElementsByTagName('tr')->item($this->eTimeHours);
                $mock ->appendChild($mock -> importNode($row,true));
                
                // get the correct date 
                $cell = $row->getElementsByTagName('td')->item($this->eDate);
                $mock ->appendChild($mock -> importNode($cell,true));
               
                $price = $cell->nodeValue;
                $newPrice = str_replace(",", ".", $price);
            }
            else{
                $newPrice = 35.87;
            }
             $newPrice = $newPrice / 10 + 0.30;
            $this->ePriceHour = $newPrice;
           // echo '<br> Price is: ' . $price . ' euros / MWh <br> It equals to ' 
        //    . $newPrice . " snt / kWh with approximate delivery price included (0.3 snt)";
        }
        
        
        public function getLowestPrice(){
            if($this->eDate < 9) {
                $file = file_get_contents("https://dranu.github.io/elspot/elspot.html", true);
                //$file = file_get_contents("PriceData/Elspot prices.html", true);
                $d = new DOMDocument;
                $mock = new DOMDocument;
                $d->loadHTML($file);//file_get_contents('/path/to/my.html'));
                $body = $d->getElementsByTagName('body')->item(0);
                $table = $body -> getElementsByTagName('table')->item(0);
                $i = 2;
                $j = $this->eTimeHours;
                $lowestPrice = 100;
                $heatTime = 0;
                for ($i; $i<=$j;$i++){
                    
                    $row = $table->getElementsByTagName('tr')->item($i);
                    $mock ->appendChild($mock -> importNode($row,true));
                    $cell = $row->getElementsByTagName('td')->item($this->eDate);
                    $mock ->appendChild($mock -> importNode($cell,true));
                    $newPrice = $cell->nodeValue;
                    //echo "<br>Found price: " . $newPrice . "<br>";
                    if ($lowestPrice > $newPrice){
    
                        $heatTime = $i - 2;
                        $lowestPrice = $newPrice;
                    }
                }
                if($heatTime < 0 )
                    $heatTime = 0;
                $this->eBestTime = $heatTime;
                $lowestPrice = str_replace(",", ".", $lowestPrice);
                //echo "<br>The cheapest price is at " . $heatTime . "<br>";
            }
            else {
               $lowestPrice = 35.87; 
            }
                
            return $lowestPrice / 10 + 0.3;
        }
        
        
        public function calculatePrice(){
            
            //Create a function to check lowest price between current time and desired time
            $this->ePriceHour = $this->getLowestPrice();
            //echo "<br>The lowest price is " . $this->ePriceHour . "<br>";
            
            //Check if hour doesn't change during heating duration
            if ($this->getDuration() < 60){
                //echo "In if! <br>";
               // $this->fetchHourlyPrice();
                $this->ePriceTotal = $this->eElectricty / 3600 * $this->ePriceHour;
                //echo "<br>Price is: " . $this->ePriceHour;
            }
            else{
                //echo "<br>Price is: " . $this->ePriceHour;
                $tempDuration = $this->eDuration;
                $this->ePriceTotal =  3 * $this->ePriceHour;
                $tempDuration -= 3600;
                $this->eTimeHours++;
                $this->fetchHourlyPrice();
                //echo "<br>Price is: " . $this->ePriceHour;
                $this->ePriceTotal += 3 * $tempDuration / 3600 * $this->ePriceHour;   
            }
            
        }
          /*
            ### Ainoa merkitys tulee jos hinta vaihtuu kesken kulutuksen
            kokonaishinta = vanhaHinta + uusiHinta
            vanhahinta = E1 / 3600 * p/h = W*s1 / 3600 * p/h   || s1 < s
            
            E2 = E - E1
            uusihinta = E2 / 3600 * p/h
            
            
            */
            
        public function executeCalculations(){
            $this->calculateWater();
            $this->calculateElectricity();
            $this->calculateTime();
            $this->calculatePrice();
        }

            
        public function calculateTime(){
            //Default watts 3 kW
            //Duration in seconds
            $this->eDuration = $this->eElectricty / 3;  
        }   
        
        public function calculateWaterTempInPipes($outsideTemp){
            //Assumption that at 0 celsius, water in pipes will be at 7 celsius 
            if(($outsideTemp > -20) && ($outsideTemp < 20)){
                $this->eTempWaterInPipes = 7 + $outsideTemp * 0.15;
            } else if ($outsideTemp < -20){
                $this->eTempWaterInPipes = 7 - 20 * 0.15;
            } else {$this->eTempWaterInPipes = 7 + 20 * 0.15;}
        }
        
        public function calculateElectricity(){
            $this->calculateWaterTempInPipes($this->eTempOutWhenHeating);
            $this->eElectricty = 4.19 * $this->eWarmWater * (90 - $this->eTempWaterInPipes);
        }
        
        public function calculateWater(){
            //m1 = m(t-t2) / (t1-t2)
            $this->calculateWaterTempInPipes($this->eTempOutWhenUsing);
            if($this->eTempWaterDesired < 90){
                
                $this->eWarmWater = $this->eTotalWater 
                                * ($this->eTempWaterDesired-$this->eTempWaterInPipes) 
                                    / (90-$this->eTempWaterInPipes);
            }
            else{$this->eWarmWater = $this->eTotalWater;}
            $this->eColdWater = $this->eTotalWater - $this->eWarmWater;
        }
    }




?>


