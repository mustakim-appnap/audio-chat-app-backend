<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddFavouriteChannelRequest;
use App\Http\Requests\RemoveFavouriteChannelRequest;
use App\Services\v1\FavouriteChannelServiceV1;
use Illuminate\Http\Response;

class FavouriteChannelControllerV1 extends Controller
{
    public function __construct(protected FavouriteChannelServiceV1 $favouriteChannelServiceV1)
    {

    }

    public function index()
    {
        try {
            $response = $this->favouriteChannelServiceV1->getFavouriteChannels();
            if ($response) {
                return response(['success' => true, 'error' => null, 'data' => $response], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(AddFavouriteChannelRequest $request)
    {
        try {
            $response = $this->favouriteChannelServiceV1->addFavouriteChannel($request->validated());
            if ($response) {
                return response(['success' => true, 'error' => null], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(RemoveFavouriteChannelRequest $request)
    {
        try {
            $response = $this->favouriteChannelServiceV1->deleteFavouriteChannel($request->validated());
            if ($response) {
                return response(['success' => true, 'error' => null], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
