<?php

namespace App\Helpers;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateInterval;
use DatePeriod;
use DateTime;

class DateHelper
{
    const INPUT_DATE_FORMAT = 'd/m/Y';

    public static function format_date($date, $input_format = self::INPUT_DATE_FORMAT, $output_format = 'Y-m-d')
    {
        $formatted_date = \DateTime::createFromFormat($input_format, $date);

        return $formatted_date->format($output_format);
    }

    public static function get_month_year($month_year, $delimiter)
    {
        $explode_month_year = explode($delimiter, $month_year);
        $response = [];
        $response['month_id'] = (int) $explode_month_year[0];
        $response['year_id'] = (int) $explode_month_year[1];

        return $response;
    }

    public static function get_month_name($month_id)
    {
        $date_obj = \DateTime::createFromFormat('!m', $month_id);

        return $date_obj->format('F');
    }

    public static function convert_date_to_english($bn_date)
    {
        $search_array = ['১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', '০', '/'];

        $replace_array = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '/'];

        // convert all bangle char to English char
        $en_number = str_replace($search_array, $replace_array, $bn_date);

        // remove unwanted char
        return preg_replace('[^A-Za-z0-9:\-]', ' ', $en_number);
    }

    public static function convert_date_time_to_millisecond($date, $time)
    {
        $concat = $date.' '.$time;

        return strtotime($concat) * 1000;
    }

    public static function convert_current_date_time_to_millisecond()
    {
        return time() * 1000;
    }

    public static function get_month_start_date($month, $year)
    {
        $temp_date = $year.'-'.$month.'-'.'05';

        return date('Y-m-01', strtotime($temp_date));
    }

    public static function get_month_end_date($month, $year)
    {
        $temp_date = $year.'-'.$month.'-'.'05';

        return date('Y-m-t', strtotime($temp_date));
    }

    public static function find_nth_date($date, $n, $output_format = 'Y-m-d')
    {
        if ($n == 0) {
            $n = 1;
        }
        $n -= 1; // to include current day
        $date = new \DateTime(date($date));
        $date->modify("$n day");

        return $date->format($output_format);
    }

    public static function find_previous_date($date, $output_format = 'Y-m-d')
    {
        $date = new \DateTime(date($date));
        $date->modify('-1 day');

        return $date->format($output_format);
    }

    public static function get_time_elapsed_from_milliseconds($millisecond_time)
    {
        $time = '';
        $datetime = $millisecond_time / 1000;
        if ($millisecond_time > 86400000) {
            $days = round($millisecond_time / (1000 * 60 * 60 * 24));
            $time = $days.' days ago';
        } elseif ($millisecond_time > 3600000) {
            $hours = floor($datetime / 3600);
            $minutes = floor(($datetime / 60) - ($hours * 60));
            $seconds = round($datetime - ($hours * 3600) - ($minutes * 60));
            $time = $hours.' hours '.$minutes.' minutes ago';
        } elseif ($millisecond_time > 60000) {
            $hours = floor($datetime / 3600);
            $minutes = floor(($datetime / 60) - ($hours * 60));
            $seconds = round($datetime - ($hours * 3600) - ($minutes * 60));
            $time = $minutes.' minutes '.$seconds.' seconds ago';
        } elseif ($millisecond_time > 1000) {
            $hours = floor($datetime / 3600);
            $minutes = floor(($datetime / 60) - ($hours * 60));
            $seconds = round($datetime - ($hours * 3600) - ($minutes * 60));
            $time = $seconds.' seconds ago';
        }

        return $time;
    }

    public static function get_month($date, $interval)
    {
        $format_date = DateHelper::format_date($date);
        $now = new \DateTime(date($format_date));
        $lastMonth = $now->sub(new \DateInterval($interval));

        return $lastMonth->format('m/Y');
    }

    public static function get_dates_from_range($start, $end, $format = 'Y-m-d')
    {
        $array = [];
        $interval = new \DateInterval('P1D');

        $realEnd = new \DateTime($end);
        $realEnd->add($interval);

        $period = new \DatePeriod(new \DateTime($start), $interval, $realEnd);

        foreach ($period as $date) {
            $array[] = $date->format($format);
        }

        return $array;
    }

    public static function get_month_list($fromMonth, $toMonth, $output_format = 'F y')
    {
        $monthNames = [];
        $months = CarbonPeriod::create(Carbon::parse($fromMonth)->format('Y-m-d'), ($toMonth))->month();

        foreach ($months as $key => $value) {
            $monthNames[] = $value->format($output_format);
        }

        return $monthNames;
    }

    public static function get_year_list($fromMonth, $toMonth)
    {
        $all_years = CarbonPeriod::create(Carbon::parse($fromMonth)->format('Y-m-d'), ($toMonth))->year();
        $years = [];
        foreach ($all_years as $key => $value) {
            array_push($years, $value->format('Y'));
        }

        return $years;
    }

    public static function millisecond_to_date_time($millisecond, $format = 'Y-m-d H:i:s')
    {
        return date($format, $millisecond / 1000);
    }

    public static function millisecond_to_date($millisecond, $format = 'Y-m-d')
    {
        return date($format, $millisecond / 1000);
    }

    public static function get_interval_between_dates($start = self::INPUT_DATE_FORMAT, $end = self::INPUT_DATE_FORMAT)
    {
        $start = DateHelper::format_date($start);
        $end = DateHelper::format_date($end);
        $start_date = new \DateTime($start);
        $end_date = new \DateTime($end);
        $length = $start_date->diff($end_date);
        if (strtotime($start) > strtotime($end)) {
            return '-'.($length->days + 1);
        } else {
            return $length->days + 1;
        }
    }

    public static function get_weeks_in_range($start_date, $end_date)
    {
        $weeks = [];
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $end->modify('+1 day');
        $interval = DateInterval::createFromDateString('1 week');
        $period = new DatePeriod($start, $interval, $end);
        $weekCount = 1;

        foreach ($period as $dt) {
            if ($weekCount <= 40) {
                $weekStart = $dt->format('Y-m-d');
                $weekEnd = $dt->modify('+6 days')->format('Y-m-d');

                $weeks[] = [
                    'week' => $weekCount,
                    'start' => $weekStart,
                    'end' => $weekEnd,
                    'dates' => self::get_dates_from_range($weekStart, $weekEnd),
                ];
            }
            $weekCount++;
        }

        return $weeks;
    }

    public static function getWeekDateRange($date, $weekNumber, $dateFormat = self::INPUT_DATE_FORMAT)
    {
        // Convert input date to a DateTime object
        $startDate = new DateTime($date);

        // Calculate the number of days to add to get to the specified week
        $daysToAdd = ($weekNumber) * 7;

        // Add the calculated days to get the start date of the specified week
        $startDate->modify("+$daysToAdd days");

        // Calculate the end date by adding 6 days to the start date
        $endDate = clone $startDate;
        $endDate->modify('+6 days');

        // Format the dates as per your requirement (e.g., 'd-m-Y')
        $startDate = $startDate->format($dateFormat);
        $endDate = $endDate->format($dateFormat);

        // Return an associative array with start_date and end_date
        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }
}
