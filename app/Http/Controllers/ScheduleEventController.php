<?php

namespace App\Http\Controllers;

class ScheduleEventController extends Controller
{
    public function index()
    {
        return view('schedule-events.index');
    }
}
