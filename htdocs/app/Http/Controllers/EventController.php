<?php

namespace App\Http\Controllers;

use App\Event;
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
        $event = $this->event->newInstance();
        $event->title = request('title');
        $event->about = request('about');
        $event->address = request('address');
        $event->price = request('price');
        $event->max_guests = request('max_guests');
        $event->start_date = request('start_date');
        $event->end_date = request('end_date');
        $event->start_date = request('start_date');

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
        $event = $this->event->find($id);
        $event->title = request('title');
        $event->about = request('about');
        $event->address = request('address');
        $event->price = request('price');
        $event->max_guests = request('max_guests');
        $event->start_date = request('start_date');
        $event->end_date = request('end_date');
        $event->start_date = request('start_date');

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
        $date_now = date("Y-m-d H:i:s");
        $currentEvents = $this->event
            ->where('end_date' ,'<', $date_now)
            ->get();

        return $currentEvents;

    }
}
