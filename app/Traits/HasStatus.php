<?php

namespace App\Traits;

use DB;

/**
 * Functionality to determine the status of jobs
 */
trait HasStatus
{
    public static function isRunning($id = 0)
    {
        $jobQuery = DB::table('jobs')->where('payload', 'like', '%'.static::class.'%');

        if ($id) {
            $jobQuery->where('payload', 'like', "%\\\\\"id\\\\\";i:{$id};%");
        }

        $job = $jobQuery->first();

        // \Log::debug('DebtImportJob is '.($job ? '' : 'not').' running');

        return $job && $job->attempts > 0 ? true : false;
    }
}
