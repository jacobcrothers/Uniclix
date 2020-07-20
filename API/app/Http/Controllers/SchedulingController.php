<?php

namespace App\Http\Controllers;

use App\Models\ScheduleTime;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SchedulingController extends Controller
{
    private $user;
    private $selectedChannel;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            $user_id = $this->user->id;
            if (!$this->user->hasPermission("scheduling", $user_id)) return response()->json(["error" => "You need to upgrade to unlock this feature."], 403);
            $this->selectedChannel = $this->user->selectedChannel();
            return $next($request);
        });
    }

    public function schedulingTimes(Request $request)
    {
        $times = ScheduleTime::where("channel_id", $this->selectedChannel->id)
            ->orderBy("id", "ASC")
            ->get();

        return response()->json(["items" => $times]);
    }

    public function schedulingStore(Request $request)
    {
        $times = $request->input('times');
        $scheduleTimes = $times['times'];

        foreach ($scheduleTimes as $scheduleTime) {
            ScheduleTime::create([
                'channel_id' => $this->selectedChannel->id,
                'time_id' => uniqid(),
                'schedule_week' => $scheduleTime["schedule_week"],
                'schedule_time' => $scheduleTime["schedule_time"],
            ]);
        }

        $times = ScheduleTime::where("channel_id", $this->selectedChannel->id)
            ->orderBy("id", "ASC")
            ->get();

        return response()->json(["items" => $times]);
    }

    public function schedulingEdit(Request $request)
    {
        $time_id = $request->input('time_id');
        $schedule_time = $request->input('schedule_time');

        ScheduleTime::where("time_id", $time_id)
            ->update([
                'schedule_time' => $schedule_time,
            ]);

        $times = ScheduleTime::where("channel_id", $this->selectedChannel->id)
            ->orderBy("id", "ASC")
            ->get();

        return response()->json(["items" => $times]);
    }

    public function destroy($timeId)
    {
        ScheduleTime::where("time_id", $timeId)
            ->delete();

        $times = ScheduleTime::where("channel_id", $this->selectedChannel->id)
            ->orderBy("id", "ASC")
            ->get();

        return response()->json(["items" => $times]);
    }

    public function clearAll(Request $request)
    {
        ScheduleTime::where("channel_id", $this->selectedChannel->id)
            ->delete();
        
        $times = ScheduleTime::where("channel_id", $this->selectedChannel->id)
            ->orderBy("id", "ASC")
            ->get();

        return response()->json(["items" => $times]);
    }
}
