<?php
/**
 * Created by PhpStorm.
 * User: kot
 * Date: 10/15/20
 * Time: 1:37 AM
 */

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DateService
{
    public $holidays;

    public function __construct()
    {
        $this->holidays = collect(config('holidays'));
    }

    public function getDateHolidays($date)
    {
        $date = Carbon::parse($date);

        $notChangingDateHolidays = $this->notChangingDateHolidays($date);

        $changingDateHolidays = $this->changingDateHolidays($date);

        $holidays = $notChangingDateHolidays->merge($changingDateHolidays);

        $filteredHolidays = $holidays->where('start', '<=', $date->timestamp)
            ->where('end', '>=', $date->timestamp)->pluck('name');

        return $filteredHolidays;
    }

    private function notChangingDateHolidays($date)
    {
        return $this->holidays->where('changing', false)->map(function ($holiday) use ($date) {
            $holiday['start'] = Carbon::parse($holiday['start'])->setYear($date->format('Y'))->timestamp;

            $holidayEnd = $holiday['start'];
            if (isset($holiday['end']) AND ! empty($holiday['end'])) {
                $holidayEnd = $holiday['end'];
            }
            $end = Carbon::parse($holidayEnd)->setYear($date->format('Y'));
            if ($end->isWeekend()) {
                $end = $end->startOfWeek()->addWeek();
            }

            $holiday['end'] = $end->timestamp;

            return $holiday;
        });
    }

    private function changingDateHolidays($date)
    {
        return $this->holidays->where('changing', true)->map(function ($holiday) use ($date) {
            $startRaw = Carbon::parse($holiday['start']);

            $weekOfMonth = $startRaw->weekOfMonth;
            $dayOfWeek = (int) $startRaw->format('N');

            $startInit = Carbon::create(
                $date->format('Y'),
                $startRaw->format('n'),
                (int) $startRaw->startOfMonth()->format('j')
            );

            $monthData = collect(
                CarbonPeriod::create($startInit->toDateString(), '1 days', $startInit->endOfMonth()->toDateString())
            )->map(function ($date) {
                return [
                    'dayOfMonth' => (int) $date->format('j'),
                    'dayOfWeek' => (int) $date->format('N'),
                    'weekOfMonth' => $date->weekOfMonth
                ];
            });

            $day = $monthData->where('weekOfMonth', $weekOfMonth)
                ->where('dayOfWeek', $dayOfWeek)->collapse()->get('dayOfMonth');

            if ( ! $day) {
                $day = $monthData->where('weekOfMonth', --$weekOfMonth)
                    ->where('dayOfWeek', $dayOfWeek)->collapse()->get('dayOfMonth');
            }

            $start = $startInit->setDay($day)->setTime(0, 0);

            $holiday['start'] = $start->timestamp;
            $end = Carbon::parse($holiday['start']);
            if ($end->isWeekend()) {
                $end = $end->startOfWeek()->addWeek();
            }
            $holiday['end'] = $end->timestamp;

            return $holiday;
        });
    }
}