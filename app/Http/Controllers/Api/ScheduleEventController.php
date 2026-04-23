<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScheduleEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class ScheduleEventController extends Controller
{
    private const EVENT_TYPES = [
        'weekly_class',
        'exam',
        'holiday',
        'meeting',
        'deadline',
        'other',
    ];

    /**
     * Display a listing of schedule events with optional filtering.
     */
    public function index(Request $request)
    {
        $request->validate([
            'course_class_id' => 'nullable|integer|exists:course_classes,id',
            'event_type' => ['nullable', 'string', Rule::in(self::EVENT_TYPES)],
        ]);

        $events = ScheduleEvent::with(['courseClass.course', 'courseClass.instructor'])
            ->when($request->filled('course_class_id'), function ($query) use ($request) {
                $query->where('course_class_id', $request->integer('course_class_id'));
            })
            ->when($request->filled('event_type'), function ($query) use ($request) {
                $query->where('event_type', $request->string('event_type')->toString());
            })
            ->orderBy('start_date')
            ->orderByRaw('COALESCE(start_time, "23:59:59")')
            ->paginate(15);

        return response()->json($events, Response::HTTP_OK);
    }

    /**
     * Store a new schedule event.
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $event = ScheduleEvent::create($data);

        return response()->json(
            $event->load(['courseClass.course', 'courseClass.instructor']),
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified schedule event details with related data.
     */
    public function show(ScheduleEvent $scheduleEvent)
    {
        return response()->json(
            $scheduleEvent->load(['courseClass.course', 'courseClass.instructor']),
            Response::HTTP_OK
        );
    }

    /**
     * Update the specified schedule event.
     */
    public function update(Request $request, ScheduleEvent $scheduleEvent)
    {
        $data = $this->validateData($request, true);

        $scheduleEvent->update($data);

        return response()->json(
            $scheduleEvent->load(['courseClass.course', 'courseClass.instructor']),
            Response::HTTP_OK
        );
    }

    /**
     * Remove the specified schedule event.
     */
    public function destroy(ScheduleEvent $scheduleEvent)
    {
        $scheduleEvent->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Validate schedule event data from request.
     * Handles automatic date/time calculations for recurring events and all-day events.
     */
    private function validateData(Request $request, bool $partial = false): array
    {
        $required = $partial ? ['sometimes', 'required'] : ['required'];

        $data = $request->validate([
            'course_class_id' => 'nullable|exists:course_classes,id',
            'title' => [...$required, 'string', 'max:255'],
            'event_type' => [...$required, 'string', Rule::in(self::EVENT_TYPES)],
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'start_date' => [...$required, 'date'],
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'weekday' => 'nullable|integer|min:0|max:6',
            'is_all_day' => 'nullable|boolean',
            'is_recurring_weekly' => 'nullable|boolean',
        ]);

        $isRecurring = array_key_exists('is_recurring_weekly', $data)
            ? (bool) $data['is_recurring_weekly']
            : (bool) $request->boolean('is_recurring_weekly');

        $isAllDay = array_key_exists('is_all_day', $data)
            ? (bool) $data['is_all_day']
            : (bool) $request->boolean('is_all_day');

        if ($isRecurring) {
            $weekday = $data['weekday'] ?? null;
            $startDate = $data['start_date'] ?? $request->input('start_date');

            if ($weekday === null && $startDate) {
                $data['weekday'] = Carbon::parse($startDate)->dayOfWeek;
            }
        }

        if ($isRecurring && ($data['weekday'] ?? null) === null) {
            validator(
                ['weekday' => $data['weekday'] ?? null],
                ['weekday' => 'required|integer|min:0|max:6']
            )->validate();
        }

        if ($isAllDay) {
            $data['start_time'] = null;
            $data['end_time'] = null;
        }

        if ($isRecurring && empty($data['end_date'])) {
            $startDate = $data['start_date'] ?? $request->input('start_date');

            if ($startDate) {
                $data['end_date'] = Carbon::parse($startDate)->endOfYear()->toDateString();
            }
        }

        return $data;
    }
}
