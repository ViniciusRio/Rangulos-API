<?php

namespace App\Http\Controllers;

use App\Event;
use App\Guest;
use Tymon\JWTAuth\Facades\JWTAuth;
use DB;
use File;

class EventController extends Controller
{
    protected $guest;
    protected $event;

    public function __construct(Event $event, Guest $guest)
    {
        $this->event = $event;
        $this->guest = $guest;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = JWTAuth::parseToken()->toUser();
        $idsEvents = $this->guest->where('user_id', $user->id)
            ->select('event_id')
            ->pluck('event_id')
            ->toArray();

        $events = $this->event->whereNotIn('id', $idsEvents)
            ->get();

        return $events;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $user = JWTAuth::parseToken()->toUser();
        $event = $this->event->newInstance();
        $event->title = request('title');
        $event->about = request('about');
        $event->address = request('address');
        $event->price = request('price');
        $event->max_guests = request('max_guests');
        $event->start_date = request('start_date');
        $event->end_date = request('end_date');
        $event->user_creator_id = $user->id;

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
        $user = JWTAuth::parseToken()->toUser();

        $event = $this->event->withTrashed()->find($id);
        $event->is_owner = $event->user_creator_id == $user->id;
        $event->total_guests = $event->guests->count();
        $event->owner = $event->userCreator->name;
        $event->is_guest = $event->guests->where('user_id', $user->id)->count() > 0;

        return response()->json($event);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
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
        $event->user_creator_id = $user->id;

        if ($event->save()) {
            return response()->json([
                'success' => 'Evento atualizado com sucesso'
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
                'success' => 'Evento cancelado',
            ]);
        }

        return response()->json([
            'error' => 'Nao foi possivel cancelar evento'
        ], 500);
    }

    /**
     * @return mixed
     */
    public function pastEvents()
    {
        $user = JWTAuth::parseToken()->toUser();

        $date_now = date("Y-m-d H:i:s");
        $pastEvents = $this->event->join('guests', 'events.id', '=', 'guests.event_id')
            ->withTrashed()
            ->where('guests.user_id', $user->id)
            ->where('end_date' ,'<', $date_now)
            ->where('guests.payment_confirmed', 1)
            ->get();

        return $pastEvents;
    }

    /**
     * @return mixed
     */
    public function currentEvents()
    {
        $user = JWTAuth::parseToken()->toUser();

        $now = date("Y-m-d H:i:s");
        $events = $this->event->join('guests', 'events.id', '=', 'guests.event_id')
           ->withTrashed()
           ->where('guests.user_id', $user->id)
           ->where('end_date' ,'>=', $now)
           ->select([
               'events.id',
               'guests.payment_confirmed',
               'events.title',
               'events.price',
               'events.start_date',
               'events.end_date',
               'events.deleted_at',
               'events.url_image'
           ])
           ->get();

           return $events;
    }

    /**
     * @return mixed
     */
    public function myEvents()
    {
        $user = JWTAuth::parseToken()->toUser();

        $myEvents = $this->event
            ->withTrashed()
            ->where('user_creator_id', $user->id)
            ->get();


        return $myEvents;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function payEvent()
    {
        $user = JWTAuth::parseToken()->toUser();

        $guest = $this->guest->where('event_id', request('id'))
            ->where('user_id', $user->id)
            ->first();

        if($guest) {
            $guest->payment_confirmed = true;
            $guest->save();
            return response()->json(true);
        }

        return response()->json(false);

    }

    /**
     * @param $id
     * @return mixed
     */
    public function restore($id)
    {
        JWTAuth::parseToken()->toUser();
        
        $this->event->withTrashed()
            ->where('id', $id)
            ->restore();

        return response()->json(true);

    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload($id)
    {
        JWTAuth::parseToken()->toUser();

        $event = $this->event->withTrashed()->find($id);
        $path = base_path();
        $path .= '/app/storage/app/events';
        // buscando e armazena em caminho especifico e com um mome unido
        $event->url_image = request()->file('file')->storeAs($path, $id.'.jpg');

        if ($event->save()) {
            return response()->json([
                'success' => 'Imagem salva'
            ]);
        }

        return response()->json([
            'error' => 'Imagem nao salva'
        ], 500);

    }

    public function getImage($id)
    {
        JWTAuth::parseToken()->toUser();

        $event = $this->event->withTrashed()->find($id);

        if (!$event->url_image)
        {
            return response()->json(null);
        }

        $path = storage_path('app/' . $event->url_image);
        dd($path);
        if (!File::exists($path)) {
            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        return response()->make(
            $file, 200, [
                'Content-Type'=> $type
            ]
        );
    }
}
