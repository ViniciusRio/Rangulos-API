<?php

namespace App\Http\Controllers;

use App\Guest;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class GuestController extends Controller
{

    protected $guest;

    public function __construct(Guest $guest)
    {
        $this->guest = $guest;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($id)
    {
        $user = JWTAuth::parseToken()->toUser();
        $guest = $this->guest->newInstance();

        $guest->payment_confirmed = false;
        $guest->event_id = $id;
        $guest->user_id = $user->id;

        if ($guest->save()) {
            return response()->json([
                'success' => 'Convidado criado com sucesso',
                'guest' => $guest
            ]);
        }

        return response()->json([
            'error' => 'Nao foi possivel cadastrar o convidado'
        ], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($eventId)
    {
        $user = JWTAuth::parseToken()->toUser();
        $guest = $this->guest->where('user_id', $user->id)
            ->where('event_id', $eventId)
            ->first();

        if ($guest && $guest->delete()) {
            return response()->json([
                'success' => 'Convidado Excluido'
            ]);
        }

        return response()->json([
            'error' => 'Nao foi possivel excluir o convidado'
        ]);
    }

    public function rate($eventId) {

        $user = JWTAuth::parseToken()->toUser();
        $guest = $this->guest->where('user_id', $user->id)
            ->where('event_id', $eventId)
            ->first();

        $guest->rate = request('rate');

        if ($guest->save()) {
            return response()->json(true);
        }
    }
}
