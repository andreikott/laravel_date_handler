<?php
/**
 * Created by PhpStorm.
 * User: kot
 * Date: 10/15/20
 * Time: 1:37 AM
 */

namespace App\Services;

use Carbon\Carbon;

class DateService
{
    public static function getDateHolidays($date)
    {
        $date = Carbon::parse($date);

        $holidays = collect(config('holidays'))->map(function ($holiday) use ($date) {
            $holiday['start'] = Carbon::parse($holiday['start'])->setYear($date->format('Y'))->timestamp;

            $holidayEnd = $holiday['start'];
            if (isset($holiday['end']) AND ! empty($holiday['end'])) {
                $holidayEnd = $holiday['end'];
            }
            $end = Carbon::parse($holidayEnd)->setYear($date->format('Y'));
            if ($end->isWeekend()) {
                $end = $end->addDay();
            }

            $holiday['end'] = $end->timestamp;

            return $holiday;
        });

        $filteredHolidays = $holidays->where('start', '<=', $date->timestamp)
            ->where('end', '>=', $date->timestamp)->pluck('name');

        return $filteredHolidays;
    }
}