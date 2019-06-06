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
        $this->startedAt = $scan->created_at->toDateTimeString();
        $this->finishedAt = now()->toDateTimeString();
        $this->version = file(base_path('VERSION'), FILE_IGNORE_NEW_LINES)[0];
        $this->results = $this->getFormattedResults($scan);
    }

    public function getFormattedResults(Scan $scan)
    {
        $results = collect();
        $missingResults = collect();

        foreach ($scan->results as $result) {
            if ($result->result->isNotEmpty()) {
                $results->push(array_merge([
                    'startedAt' => $result->created_at->toDateTimeString(),
                    'finishedAt' => $result->updated_at->toDateTimeString(),
                ], $result->result->toArray()));
            } else {
                $missingResults->push($result->scanner_code);
            }
        }

        $missingResults->isEmpty() ?: $this->withMissingScannerResults = $missingResults;

        return $results;
    }
}
