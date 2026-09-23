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

    /**
     * Display the bulk-management page for schedule events
     * (holiday day-manager, bulk delete of events and attendances).
     */
    public function manage()
    {
        return view('schedule-events.manage');
    }
}
