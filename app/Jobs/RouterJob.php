<?php

namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\Router\RouteServices;
use App\Token;
use Illuminate\Support\Facades\Log;

class RouterJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $routerService;

    protected $token;
    /**
     * Create a new job instance.s
     */
    public function __construct(Token $token)
    {
        $this->token = $token;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(RouteServices $routerService)
    {
        $shortestPath = $routerService->processRoute($this->token->path);

        if($shortestPath['error'] != '') {
            $this->token->status = 'failure';
            $this->token->error = $shortestPath['error'];
        } else {
            $this->token->total_distance = $shortestPath['total_distance'];
            $this->token->total_time = $shortestPath['total_time'];
            $this->token->status = 'success';
        }
        $this->token->save();
        Log::info('Scrap email address job process completed - Url: '.$this->token->token);
    }

    /**
     * @param \Exception $exception
     */
    public function failed(\Exception $exception)
    {
        // Send user notification of failure, etc...
    }
}