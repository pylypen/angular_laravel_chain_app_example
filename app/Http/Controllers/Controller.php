<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Responses\Response as AppResponse;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private static $headers =
        [
            'Content-Type' => 'application/json'
        ];

    /**
     * @param $data
     * @return Response
     */
    protected function _set_success($data)
    {
        $response = json_encode(AppResponse::success($data));
        return Response::create($response, Response::HTTP_OK, self::$headers);
    }

    /**
     * @param $data
     * @return Response
     */
    protected function _set_error($data, $status = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        $response = json_encode(AppResponse::error($data));
        return Response::create($response, $status, self::$headers);
    }
}
