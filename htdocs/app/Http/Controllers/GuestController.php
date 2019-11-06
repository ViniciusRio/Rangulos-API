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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $guest = $this->guest->newInstance();
        $payment_confirmed = filter_var(request('payment_confirmed'), FILTER_VALIDATE_BOOLEAN);

        $guest->payment_confirmed = $payment_confirmed;
        $guest->event_id = request('event_id');
        $guest->user_id = request('user_id');

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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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

}
