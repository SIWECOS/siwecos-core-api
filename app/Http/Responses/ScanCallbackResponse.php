<?php

namespace App\Http\Responses;

use App\Scan;
use Illuminate\Database\Eloquent\Model;

class ScanCallbackResponse extends Model
{
    public function __construct(Scan $scan)
    {
        $this->url = $scan->url;
        $this->dangerLevel = $scan->dangerLevel;
        $this->started_at = $scan->created_at->toDateTimeString();
        $this->finished_at = now()->toDateTimeString();
        $this->version = file(base_path('VERSION'), FILE_IGNORE_NEW_LINES)[0];
        $this->results = $this->getFormattedResults($scan);
    }

    public function getFormattedResults(Scan $scan)
    {
        $results = collect();

        foreach ($scan->results as $result) {
            $results->push(array_merge([
                'started_at' => $result->created_at->toDateTimeString(),
                'finished_at' => $result->updated_at->toDateTimeString(),
            ], $result->result->toArray()));
        }

        return $results;
    }
}
