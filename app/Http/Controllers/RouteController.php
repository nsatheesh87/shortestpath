<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Router\RouteServices as RouteServices;
use App\Token;
use App\Jobs\RouterJob;

/**
 * Class RouteController
 * @package App\Http\Controllers
 */
class RouteController extends Controller
{
    /**
     * @var RouteServices
     */
    protected $routeServices;

    /**
     * RouteController constructor.
     * @param RouteServices $routeServices
     */
    public function __construct(RouteServices $routeServices)
    {
        $this->routeServices = $routeServices;
    }

    /**
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
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
                'error'   => 'Invalid_Token'
            ], 400);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $routeInput  = $request->json()->all();

        $validated = $this->routeServices->isValid($routeInput);

        if($validated['success']) {
            $token =  $this->generateUuidToken();
            $requestObject = ['path' => $validated['data'], 'token' => $token];
            $tokenObject = Token::create($requestObject);

            if($tokenObject) {
                $job = (new RouterJob($tokenObject))->onQueue('RouteQueue');
                dispatch($job);

                return response()
                    ->json([
                        'token'   => $token
                    ], 200);
            }
        }


        return response()
            ->json([
                'status'    => 'failure',
                'error'   => $validated['error']
            ], 400);
    }

    /**
     * @return string
     */
    private function generateUuidToken() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

}
