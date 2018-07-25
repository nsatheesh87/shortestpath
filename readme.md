# Lumen PHP Framework - Find the shortest path using GMAP API

Step 1:
```
git clone https://github.com/nsatheesh87/shortestpath.git
```
Step 2:
```
cd shortestpath
```
step 3:
```
docker run --rm -v $(PWD):/app koutsoumpos89/composer-php7.1 install
```
step 4:
```
docker-compose up -d
```
step 5:
```
cp .env-example to .env and update API KEY GMAP_KEY=
```
step 6:
```
docker-compose exec app php artisan migrate

```

Step 7: (To run phpunit test)

````
docker run --rm -v /$(PWD):/app koutsoumpos89/composer-php7.1 vendor/bin/phpunit
````

 HTTP endpoints:

- [POST `/route`: Submit start point and drop-off locations](#submit-start-point-and-drop-off-locations)
- [GET `/route/<TOKEN>`: Get shortest driving route](#get-shortest-driving-route)

### Submit start point and drop-off locations

Method:  
 - `POST`

URL path:  
 - `/route`

Input body:  

```json
[
	["ROUTE_START_LATITUDE", "ROUTE_START_LONGITUDE"],
	["DROPOFF_LATITUDE_#1", "DROPOFF_LONGITUDE_#1"],
	...
]
```

Response body:  
 - `HTTP code 200`  

```json
{ "token": "TOKEN" }
```

or

```json
{ "error": "ERROR_DESCRIPTION" }
```

---

Input body example:

```json
[
	["22.372081", "114.107877"],
	["22.284419", "114.159510"],
	["22.326442", "114.167811"]
]
```

Response example:

```json
{ "token": "9d3503e0-7236-4e47-a62f-8b01b5646c16" }
```

### Get shortest driving route
Get shortest driving route for submitted locations (sequence of `[lat, lon]` values starting from start location resulting in shortest path)

Method:  
- `GET`

URL path:  
- `http://localhost:8080/route/<TOKEN>`

Response body:  
- HTTP 200  

```json
{
	"status": "success",
	"path": [
		["ROUTE_START_LATITUDE", "ROUTE_START_LONGITUDE"],
		["DROPOFF_LATITUDE_#1", "DROPOFF_LONGITUDE_#1"],
		...
	],
	"total_distance": DRIVING_DISTANCE_IN_METERS,
	"total_time": ESTIMATED_DRIVING_TIME_IN_SECONDS
}
```  
or  

```json
{
	"status": "in progress"
}
```  
or  

```json
{
	"status": "failure",
	"error": "ERROR_DESCRIPTION"
}
```

---

URL example:  
 - `http://localhost:8080/route/9d3503e0-7236-4e47-a62f-8b01b5646c16`

Response example:  
```json
{
	"status": "success",
	"path": [
		["22.372081", "114.107877"],
		["22.326442", "114.167811"],
		["22.284419", "114.159510"]
	],
	"total_distance": 20000,
	"total_time": 1800
}
```