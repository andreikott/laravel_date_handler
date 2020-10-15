<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\DateService;

class DateController extends Controller
{
    public $dateService;

    public function __construct(DateService $dateService)
    {
        $this->dateService = $dateService;
    }

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

        $holidays = $this->dateService->getDateHolidays($request->input('date'));

        return response()->json(['holidays' => $holidays]);
    }
}
