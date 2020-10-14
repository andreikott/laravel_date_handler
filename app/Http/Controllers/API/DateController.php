<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class DateController extends Controller
{
    /**
     * Holidays.
     *
     * @return \Illuminate\Http\Response
     */
    public function holidays(Request $request)
    {
        $validator = Validator::make($request->all(),
            ['date' => 'required|date',],
            ['date.date' => 'The date is not a valid.']
        );

        if ($validator->fails()) {
            return response()->json(['error' => collect($validator->errors())->collapse()->first()]);
        }

        $date = Carbon::parse($request->input('date'));

        $holidays = collect(config('holidays'))->map(function ($holiday) use ($date) {
            $holiday['start'] = Carbon::parse($holiday['start'])->setYear($date->format('Y'))->timestamp;

            $holidayEnd = $holiday['start'];
            if (isset($holiday['end']) AND ! empty($holiday['end'])) {
                $holidayEnd = $holiday['end'];
            }
            $holiday['end'] = Carbon::parse($holidayEnd)->setYear($date->format('Y'))->timestamp;

            return $holiday;
        });

        $filteredHolidays = $holidays->where('start', '>=', $date->timestamp)
            ->where('end', '<=', $date->timestamp)->pluck('name');

        return response()->json(['holidays' => $filteredHolidays]);
    }
}
