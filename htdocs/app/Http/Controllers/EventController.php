<?php

namespace App\Http\Controllers;

use App\Event;
use App\Guest;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use DB;

class EventController extends Controller
{
    protected $event;


    public function __construct(Event $event)
    {
        $this->event = $event;


    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $today = date('Y-m-d H:i:s');
        $events = $this->event->get();
//            ->where('end_date', '<=', DB::raw('now()'))
//            ->get();

        return $events;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        JWTAuth::parseToken()->toUser();
        $event = $this->event->newInstance();
        $event->fill(request()->params["newEvent"]);

        if ($event->save()) {
            return response()->json([
                'success' => 'Evento cadastrado com sucesso',
                'event' => $event
            ]);
        }

        return response()->json([
            'error' => 'Nao foi possivel cadastrar'
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
        JWTAuth::parseToken()->toUser();

        $events = $this->event->find($id);

        return $events;
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
        $user = JWTAuth::parseToken()->toUser();
        $event = $this->event->find($id);
        $event->title = request('title') ?? $event->title;
        $event->about = request('about') ?? $event->about;
        $event->address = request('address') ?? $event->address;
        $event->price = request('price') ?? $event->price;
        $event->max_guests = request('max_guests') ?? $event->max_guests;
        $event->start_date = request('start_date') ?? $event->start_date;
        $event->end_date = request('end_date') ?? $event->end_date;
        $event->url_image = 'https://estrangeira.com.br/wp-content/uploads/2016/09/Captura-de-Tela-2016-09-12-a%CC%80s-18.36.47-602x500.png';


        if ($event->save()) {
            return response()->json([
                'success' => 'Evento atualizado com sucesso',
                'event' => $event
            ]);
        }

        return response()->json([
            'error' => 'Nao foi possivel atualizar'
        ], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = JWTAuth::parseToken()->toUser();

        $event = $this->event->find($id);

        if ($event->delete()) {
            return response()->json([
                'success' => 'Evento excluido com sucesso',
            ]);
        }

        return response()->json([
            'error' => 'Nao foi possivel excluir'
        ], 500);
    }

    public function pastEvents()
    {
        $user = JWTAuth::parseToken()->toUser();

        $date_now = date("Y-m-d H:i:s");
        $pastEvents = $this->event
            ->where('end_date' ,'<', $date_now)
            ->get();

        return $pastEvents;
    }

    public function currentEvent()
    {
        $now = date("Y-m-d H:i:s");
        $user = JWTAuth::parseToken()->toUser();

        $events = $this->event->join('guests', 'events.id', '=', 'guests.event_id')
            ->where('guests.user_id', $user->id)
            ->where('guests.payment_confirmed', 1)
            ->whereRaw("'$now' BETWEEN start_date AND end_date")
            ->select([
                'events.id',
                'events.title',
                'events.price',
                'events.start_date',
                'events.url_image'
            ])
            ->first();

           return $events;

    }
}
