<?php 

class MapAPI {
    var $mLat = 0;
    var $mLng = 0;
    var $mAddress = "";
    
    public function findLatLng($addressLong){
        list ($address, $postal) = split(',', $addressLong);
        $address = preg_replace("/ /","+",$address);
        $postal = str_replace(' ', '', $postal);
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?&address=' . $address . '%2C%20' . $postal . '&key=AIzaSyDTwHey91TakLJO4XFB-ljSVMFF4KjIpAU';
        $results = json_decode(file_get_contents($url), true);
        if($results["status"] == "ZERO_RESULTS"){
	        echo "<p>No results found</p>";
        } else {
            $result = $results["results"][0];
            $this ->mLat = $result['geometry']['location']['lat'];
            $this ->mLng = $result['geometry']['location']['lng'];
            $this ->mAddress = $result['formatted_address'];
            //echo "<br>" . $this ->mAddress;
        }
    }
    public function getLat(){return $this->mLat;}
    public function getLng(){return $this->mLng;}
    public function getAddress(){return $this->mAddress;}
}



?>