<?php
class mwmod_mw_geo_helper extends mw_apsubbaseobj{
    private $geoHash;
    public $defaultGeohashLength=5;

    function __construct(){
    
    }
    function validateCoordinates($latitude, $longitude) {
        return $this->validateLatitude($latitude) && $this->validateLongitude($longitude);
    }
    function validateLatitude($latitude) {
        return is_numeric($latitude) && $latitude >= -90 && $latitude <= 90;
    }
    function validateLongitude($longitude) {
        return is_numeric($longitude) && $longitude >= -180 && $longitude <= 180;
    }
    final function __get_priv_geoHash(){
        if(!isset($this->geoHash)){
            $this->geoHash=new mwmod_mw_geo_geohash();
            $this->geoHash->defaultGeohashLength=$this->defaultGeohashLength;
        }
        return $this->geoHash;
    }
}
?>