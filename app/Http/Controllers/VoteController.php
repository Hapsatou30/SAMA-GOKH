<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vote;
use App\Http\Requests\StoreVoteRequest;

use App\Traits\NotifiableTrait;
use App\Http\Requests\UpdateVoteRequest;

class VoteController extends Controller
{
    use NotifiableTrait; 
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVoteRequest $request)
{
    $vote = Vote::create($request->validated());

    $users = User::all(); // Récupérer tous les utilisateurs
    foreach ($users as $user) {
        $this->addNotification($user->id, $vote->projet_id, "Un nouveau vote a été ajouté.");
    }

    return response()->json(["message" => "Vote ajouté avec succès", "data" => $vote], 201);
}

    

    /**
     * Display the specified resource.
     */
    public function show(Vote $vote)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vote $vote)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVoteRequest $request, Vote $vote)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vote $vote)
    {
        //
    }
}
