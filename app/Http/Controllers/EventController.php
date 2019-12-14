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
        $date_now = date("Y-m-d H:i:s");

        $idsEvents = $this->guest->where('user_id', $user->id)
            ->select('event_id')
            ->pluck('event_id')
            ->toArray();

        $events = $this->event->whereNotIn('id', $idsEvents)
//            ->where('user_creator_id', '!=', $user->id)
            ->where('end_date', '>', $date_now);


        if (request()->filled('q')) {
            $query = strtolower('%'.request('q').'%');
            $events = $events->where(DB::raw('title'), 'LIKE', $query);
        }

        return $events->get();
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
                'id' => $event->id
            ]);
        }

        return response()->json([
            'error' => 'Nao foi possivel cadastrar'
        ], 500);
    }

    public function storeFillEvents()
    {
        $user = JWTAuth::parseToken()->toUser();
        $eventStaticBeans = [
            'title' => 'Feijoada ao Sol',
            'about' => 'Feijoada repleta de bacon e linguiça. Venha saborear. Música regional ao vivo',
            'address' => 'R. Alm. Barroso, 2385, Ns Das Gracas - Porto Velho, RO',
            'price' => 28,
            'max_guests' => 15,
            'start_date' => '2019-12-10 09:30:00',
            'end_date' => '2019-12-12 23:30:00',
            'url_image' => 'events/6.jpg',
            'user_creator_id' => 8
        ];
        $eventStaticMeat = [
            'title' => 'Festival da Carne',
            'about' => 'Churrasco de qualidade com picanha e costela, além de cerveja gelada',
            'address' => 'R. Tabajara, 2814, Liberdade - Porto Velho, RO',
            'price' => 25,
            'max_guests' => 20,
            'start_date' => '2019-12-10 09:30:00',
            'end_date' => '2019-12-13 23:30:00',
            'url_image' => 'events/7.jpg',
            'user_creator_id' => 9
        ];
        $eventStaticBrazil = [
            'title' => 'Brasil à la carte',
            'about' => 'Acompanhe o Campeonato Brasileiro com cerveja gelada e petiscos variados',
            'address' => 'R. Sen. Álvaro Maia, 3323, Embratel - Porto Velho, RO',
            'price' => 5,
            'max_guests' => 25,
            'start_date' => '2019-12-10 09:30:00',
            'end_date' => '2019-12-12 23:30:00',
            'url_image' => 'events/8.jpg',
            'user_creator_id' => 7
        ];
        $eventStaticMilho = [
            'title' => 'Festival do Milho',
            'about' => 'Saborei receitas de milho, como: canjica, pamonha, bolos e milho cozido. Preços variados',
            'address' => 'R. Sen. Álvaro Maia, 3323, Embratel - Porto Velho, RO',
            'price' => 5,
            'max_guests' => 25,
            'start_date' => '2019-12-08 09:30:00',
            'end_date' => '2019-12-09 18:30:00',
            'url_image' => 'events/4.jpg',
            'user_creator_id' => 7
        ];
        $guestEventMilho = [
            'payment_confirmed' => 1,
            'event_id' => 55,
            'user_id' => 9
        ];

        if (DB::table('events')->insert([$eventStaticBeans, $eventStaticMeat, $eventStaticBrazil, $eventStaticMilho])) {
            DB::table('guests')->insert($guestEventMilho);
            return response()->json([
                'success' => 'Evento estatico cadastrado com sucesso'
            ]);
        }

        return response()->json([
            'error' => 'Nao foi possivel cadastrar o evento estatico'
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
        $isOwner = $event->user_creator_id == $user->id ?? null;
        $guests = [];

        if ($isOwner) {
            $guests = $event->guests()->join('users', 'users.id', '=', 'guests.user_id')
                ->select('users.name', 'users.email', 'payment_confirmed', 'guests.created_at')
                ->get()
                ->toArray();
        }

        return response()->json([
            'id' => $event->id,
            'title' => $event->title,
            'about' => $event->about,
            'address' => $event->address,
            'price' => $event->price,
            'url_image' => $event->url_image,
            'max_guests' => $event->max_guests,
            'start_date' => $event->start_date->format('Y-m-d H:i:s'),
            'end_date' => $event->end_date->format('Y-m-d H:i:s'),
            'deleted_at' => $event->deleted_at ? $event->deleted_at->format('Y-m-d H:i:s') : null,
            'is_owner' => $isOwner,
            'is_guest' => $event->guests->where('user_id', $user->id)->count() > 0,
            'is_paid' => $event->guests->where('payment_confirmed', '=', 1 )->count() > 0,
            'total_guests' => $event->guests->where('payment_confirmed', '=', 1)->count(),
            'user_creator' => $event->userCreator,
            'guests' => $guests
        ]);
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

        // buscando e armazena em caminho especifico e com um mome unido
        $event->url_image = request()->file('file')->storeAs('events', $id.'.jpg');

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

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteEvent($id)
    {
        $user = JWTAuth::parseToken()->toUser();
        $event = $this->event->withTrashed()->find($id);


        if ($event->guests()->forceDelete()) {
            $event->forceDelete();

            return response()->json([
                'success' => 'Evento e convidados excluidos com sucesso',
            ]);
        }

        if ($event->forceDelete()) {

            return response()->json([
                'success' => 'Evento excluido com sucesso',
            ]);
        }

        return response()->json([
            'error' => 'Nao foi possivel excluir evento'
        ], 500);
    }
}
