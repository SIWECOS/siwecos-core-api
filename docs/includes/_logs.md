# Log Messages
The Core-API logs some events that might be useful for debugging and statistics.

## Start Scanners
`app/Jobs/StartScannerJob.php`

| Log Level  | Message Format                          |
| ---------- | --------------------------------------- |
| `debug`    | Sending scan start request ...          |
| `info`     | Scan successful started ...             |
| `critical` | Failed to start scan ...                |
| `critical` | The following Exception was thrown: ... |


## Notify Callback URLs
`app/Jobs/NotifyCallbacksJob.php`

| Log Level  | Message Format                                                    |
| ---------- | ----------------------------------------------------------------- |
| `info`     | Scan results for Scan ID {id} successfully sent to: {callbackurl} |
| `info`     | Scan with ID {id} finished successfully                           |
| `warning`  | Scan results for Scan ID {id} could not be sent to: {callbackurl} |
| `critical` | Scan with ID {id} could not be sent to any given callbackurls     |
| `critical` | The following Exception was thrown: ...                           |
