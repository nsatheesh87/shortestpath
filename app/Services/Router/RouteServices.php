<?php
namespace App\Services\Router;
use App\Services\Router\RouterServiceInterface;

/**
 * Class RouteServices
 * @package App\Services\Router
 */
class RouteServices implements RouteServiceInterface
{
    /**
     * @var string
     */
    private $gmapUrl = 'https://maps.googleapis.com/maps/api/directions/json?';

    /**
     * @param array $routeArray
     * @return array
     */
    public function isValid($routeArray = [])
     {
         $result = ['success' => false];
         if(is_array($routeArray) && !empty($routeArray))
         {
             if(sizeof($routeArray) > 1){
                 $newRoutes = array_map(function($item){
                     if(sizeof($item) == 2){
                         $latlng = array_values($item);
                         if(is_numeric($latlng[0]) && (((float) $latlng[0]) == $latlng[0])
                             && is_numeric($latlng[1]) && (((float) $latlng[1]) == $latlng[1])){
                             return 1;
                         }
                     }
                     return 0;
                 }, $routeArray);
                 if(array_sum($newRoutes) == sizeof($newRoutes)){
                     $result['success'] = true;
                     $result['data'] = $routeArray;
                 }else{
                     $result['error'] = 'INVALID_INPUT_PARAMETERS';
                 }
             }else{
                 $result['error'] = 'MISSING_DESTINATION';
             }
         } else {
             $result['error'] = 'INVALID_JSON';
         }

         return $result;

     }

    /**
     * @param $routes
     * @return array
     */
     public function processRoute($routes)
     {
         $result = [
             'total_distance' => 0,
             'total_time' => 0,
             'error' => ''
         ];

         $origins = '';
         $destinations = $waypoints = [];

         foreach ($routes as $key => $route) {
             if ($key === 0) {
                 $origins = ((float)$route[0]) . ',' . ((float)$route[1]);
             } else {
                 $destinations[] = ((float)$route[0]) . ',' . ((float)$route[1]);
                 $waypoints[] = $this->buildWaypoint($key, $routes);
             }
         }
         $distance = $duration = false;
         $routeResults = [];
         // Todo - Improve this method by using CURL Library
         foreach ($destinations as $key => $dest) {
             $query = http_build_query([
                 'units' => 'metric',
                 'origin' => $origins,
                 'destination' => $dest,
                 'waypoints' => isset($waypoints[$key]) ? ('optimize:true|' . $waypoints[$key]) : '',
                 'key' => env('GMAP_KEY')
             ]);
             $resource = file_get_contents($this->gmapUrl . $query);

             if ($resource === false) {
                 $routeResults['error'][$key] = 'CONNECTION_ERROR';
             } else {
                 $response = json_decode($resource, true);

                 $legs = $response['routes'][0]['legs'];
                 $routeResults[$key]['distance'] = $this->processResponse($legs, 'distance');
                 $routeResults[$key]['duration'] = $this->processResponse($legs, 'duration');
             }

         }

         foreach($routeResults as $key => $routeResult){
             if(isset($routeResult['distance'])){
                 if($distance === false || $distance > $routeResult['distance']){
                     $distance = $routeResult['distance'];
                     $duration = $routeResult['duration'];
                 }
             }
         }

         if(isset($distance) && isset($duration)){
             $result['total_distance'] = $distance;
             $result['total_time'] = $duration;
             $result['path'] = $this->distanceHaversine($routes);
         }else{
             $errors = array_unique(array_map(function($item){
                 return (int) $item['error'];
             }, $routeResults));
             $result['errors'] = implode(' ', $errors);
         }
         return $result;
     }

    /**
     * @param $index
     * @param $routes
     * @return string
     */
    private function buildWaypoint($index, $routes){
        $wayPoints = '';
        foreach($routes as $key => $route){
            if($key == 0 || $key == $index){
                continue;
            }
            if($wayPoints != ''){
                $wayPoints .= '|';
            }
            $wayPoints .= ((float) $route[0]) . ',' . ((float) $route[1]);
        }
        return $wayPoints;
    }

    /**
     * @param $legs
     * @param $key
     * @return float|int
     */
     private function processResponse($legs, $key){
         if($key == 'duration'){
             return array_sum(array_map(function($item){
                 return (int) $item['duration']['value'];
             }, $legs));
         }else if($key == 'distance'){
             return array_sum(array_map(function($item){
                 return (int) $item['distance']['value'];
             }, $legs));
         }

    }

    /**
     * @param array $routesArray
     * @param array $path
     * @return array
     */
    private function distanceHaversine($routesArray = [], $path = [])
    {
        $earthRadius = 6371000;
        $origin = array_shift($routesArray);
        array_push($path, $origin);
        $distance = [];
        foreach($routesArray as $key => $route) {
             $latFrom = deg2rad($origin[1]);
             $lonFrom = deg2rad($origin[0]);
             $latTo = deg2rad($route[1]);
             $lonTo = deg2rad($route[0]);

             $latDelta = $latTo - $latFrom;
             $lonDelta = $lonTo - $lonFrom;

             $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                     cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
             $distance[$key] = $angle * $earthRadius.'\n';

         }
        uasort($distance, array($this, 'compareDistance'));

        $routesArrayTemp = $routesArray;
        $routesArray = array_replace(array_flip(array_keys($distance)), $routesArrayTemp);
        $routesArray = array_values($routesArray);

        if(count($routesArray) > 1){
            //Call the function again. until find the distance for all the destinations.
            return $this->distanceHaversine($routesArray, $path);
        }
        array_push($path, $routesArray[0]);
        return $path;
    }

    /**
     * @param $a
     * @param $b
     * @return int
     */
    private function compareDistance($a, $b) {
        if($a > $b) return 1;
        elseif($a < $b) return -1;
        else return 0;
    }

}