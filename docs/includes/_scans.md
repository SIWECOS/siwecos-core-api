# Scanning

## Starting a Scan

> The **Request** must have the following structure:

```shell
curl -X POST \
  http://siwecos-core-api/api/v2/scan \
  -H 'Content-Type: application/json' \
  -d '{
    "url": "https://siwecos.de",
    "dangerLevel": 0,
    "callbackurls": [
      "http://172.17.0.1:9000"
    ],
    "scanners": [
      "TLS", "HEADER", "INI_S"
    ]
  }'
```

```http
POST /api/v2/scan HTTP/1.1
Host: siwecos-core-api
Content-Type: application/json

{
  "url": "https://siwecos.de",
  "dangerLevel": 0,
  "callbackurls": [
    "http://172.17.0.1:9000"
  ],
  "scanners" : [
    "TLS", "HEADER", "INI_S"
  ]
}
```

A Scan can be started.


### HTTP Request

`POST /api/v2/scan`


### Query Parameters

| Parameter      | Type                           | Description                                                                       |
| -------------- | ------------------------------ | --------------------------------------------------------------------------------- |
| url            | `url`                          | The URL to the domain that should be scanned                                      |
| dangerLevel    | `integer`, min: `0`, max: `10` | Define how dangerous the scanner's tests are allowed to be                        |
| callbackurls   | `array`                        | The callbackurls to which the results should be sent                              |
| callbackurls.* | `url`                          | The URL that should receive the scan result                                       |
| *scanners*     | *`array`*                      | *A subset of available scanners that should be started*                           |
| *scanners.\**  | *`string`*                     | *An available scanner name; valid entries are listed [here](#supported-scanners)* |


### Response Status Codes

| Code | Meaning           |
| ---- | ----------------- |
| 200  | Scan started      |
| 422  | Validation failed |


## Scan Result

> The **Response** has the following structure:

```json
{
    "url": "http:\/\/www.siwecos.de",
    "dangerLevel": 10,
    "startedAt": "2019-05-09T07:05:38Z",
    "finishedAt": "2019-05-09T07:05:55Z",
    "withMissingScannerResults": ["INI_S", "INFOLEAK"],
    "results": [
        {
          "startedAt": "2019-05-09T07:05:38Z",
          "finishedAt": "2019-05-09T07:05:40Z",
          // Scanner 1 results
        },
        {
          "startedAt": "2019-05-09T07:05:41Z",
          "finishedAt": "2019-05-09T07:05:52Z",
          // Scanner 2 results
        },
        ...
    ]
}

```

The result that will be send to the callbackurls has the following structure.

<br><br>

**NB:** <br>
The `withMissingScannerResults` array will only be sent if there were conflicts in the Core-API so no result was delivered by the particular scanner.

<br><br><br>

For further information regarding the results please check the related scanner repo and the [Scanner Interface Documentation](https://github.com/SIWECOS/siwecos-core-api/tree/develop#scanner-interface-documentation).
