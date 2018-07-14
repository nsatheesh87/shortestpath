<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Router\RouteServices as RouteServices;
use App\Token;
use App\Jobs\RouterJob;

class RouteController extends Controller
{
    protected $routeServices;

    public function __construct(RouteServices $routeServices)
    {
        $this->routeServices = $routeServices;
    }

    public function getRoute($token)
    {
        $token = Token::where(['token' => $token])->first();
        if($token) {
            $response = ['status' => $token->status];

            switch ($token->status) {
                case 'success':
                    $response['path'] = $token->path;
                    $response['total_distance'] = $token->total_distance;
                    $response['total_time'] = $token->total_time;
                    break;
                case 'failure':
                    $response['error'] = $token->error;
                    break;
            }

            return response()->json($response, 200);
        }
        return response()
            ->json([
                'status'    => 'failure',
                'error'   => 'Invalid Token'
            ], 400);
    }

    public function create(Request $request)
    {
        $routeInput  = $request->json()->all();

        if($this->routeServices->isValid($routeInput)) {
            $token =  bin2hex(random_bytes(25));
            $requestObject = ['path' => $routeInput, 'token' => $token];
            $tokenObject = Token::create($requestObject);

            $job = (new RouterJob($tokenObject))->onQueue('RouteQueue');
            dispatch($job);

            return response()
                ->json([
                    'token'   => $token
            ], 200);
        }


        return response()
            ->json([
                'status'    => 'failure',
                'message'   => 'BAD REQUEST-INVALID PARAMETERS'
            ], 400);
    }

}
