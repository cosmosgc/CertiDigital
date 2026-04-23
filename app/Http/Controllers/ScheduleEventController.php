<?php

namespace App\Http\Controllers;

class ScheduleEventController extends Controller
{
    /**
     * Display a listing of schedule events management page.
     */
    public function index()
    {
        return view('schedule-events.index');
    }
}
