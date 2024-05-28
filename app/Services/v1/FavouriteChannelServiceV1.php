<?php

namespace App\Services\v1;

use App\Repositories\v1\FavouriteChannelRepositoryV1;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class FavouriteChannelServiceV1
{
    public function __construct(protected FavouriteChannelRepositoryV1 $favouriteChannelRepositoryV1)
    {
    }

    public function addFavouriteChannel($data)
    {
        return $this->favouriteChannelRepositoryV1->setUserId(Auth::id())
            ->setChannelFrequency($data['channel'])
            ->setChannelType($data['channel_type'])
            ->setChannelId()
            ->store();
    }

    public function deleteFavouriteChannel($data)
    {
        return $this->favouriteChannelRepositoryV1->setUserId(Auth::id())
            ->setChannelFrequency($data['channel'])
            ->setChannelType($data['channel_type'])
            ->delete();
    }

    public function getFavouriteChannels()
    {
        $publicChannel = $this->favouriteChannelRepositoryV1->setUserId(Auth::id())->setChannelType(Config::get('variable_constants.channel_types.public'))->index();

        $privateChannel = $this->favouriteChannelRepositoryV1->setUserId(Auth::id())->setChannelType(Config::get('variable_constants.channel_types.private'))->index();

        return [
            'public_channel' => $publicChannel,
            'private_channel' => $privateChannel,
        ];

    }
}
