<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\CreateNewDriver;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredDriverController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('(mobile)/driver/signup/Page');
    }

    /**
     * Handle an incoming registration request.
     *
     * @return RedirectResponse
     */
    public function store(Request $request, CreateNewDriver $creator)
    {
        $creator->create($request->all());

        return redirect()->route('login')->with('status', 'Registration successful. Waiting for admin approval.');
    }
}
