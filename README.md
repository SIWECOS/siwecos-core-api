# SIWECOS-Core-API

With this project you can simply run any of the offical [SIWECOS](https://siwecos.de) scanners.

## API-Documentation

You can find a builded draft for the API V2 here: https://siwecos-core-api-v2-documentation.netlify.com/

### Editing the API-Documentation via Docker

See this project for a How-To use the Docker `slate` image: https://github.com/davidheryanto/slate-docker

Make sure you first bild the image and than run the `docker run` command in the actual `siwecos-business-layer` directory on the latest `develop` branch.

# Scanner-Interface Documentation

This documentation describes the Scanner-Interface for scanners that want to be used with the [SIWECOS/siwecos-core-api](https://github.com/SIWECOS/siwecos-core-api).
Every scanner have to follow the listed rules and best practices in order to be integrated into the [SIWECOS](https://siwecos.de) project.


## Programming Requirements
No specific programming language is required for your scanner.
At the moment, we have scanners written in Java and PHP, but feel free to use any language you prefer.

To ensure a high coding quality of your project, you are encouraged to follow these guidelines:

1. Document your code
2. Write readable code, so programmers that are not familiar with your project and language can read and understand what you are doing
3. Test your code, ideally with automated tests (Unit-Tests, Integration-Tests, etc.)
4. Reduce your dependencies to a reasonable amount
6. Document how to use your scanner on arbitrary systems
7. Document the tests your scanner performs (Reason, Possible results, Influence on the service/users/etc.)
8. [Choose a license](https://choosealicense.com/) & [Check depending licenses](https://tldrlegal.com/) for your project
9. Write a clean and readable `README.md` for your project
10. Follow the [Semantic Versioning](https://semver.org/) guidelines and [keep a changelog](https://keepachangelog.com/en/1.0.0/)
11. Provide a `Dockerfile` for a ready-to-use docker image and document how to use it within a *Quick-Start* section in the `README.md`
12. Implement the following [Request-](#request-interface) and [Response-Interface](#response-interface) for your scanner


## Request Interface

You have to implement one single endpoint that's reachable via a HTTP-POST Request that accepts the following JSON values:

| Name             | Type                          | Description                                                                                                                                                                              |
| ---------------- | ----------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `url`            | `url`                         | The URL that should be scanned                                                                                                                                                           |
| `callbackurls`   | `array`                       | A list of URLs that should receive the finished scan results                                                                                                                             |
| `callbackurls.*` | `url`                         | A URL that should receive the finished scan results                                                                                                                                      |
| *`dangerLevel`*  | *`integer` (min: 0; max: 10)* | *Some tests in your scanner might affect the scanned service regarding availability or stability. With this parameter a user can define what kind of tests your scanner should perform.* |

You are free to implement further endpoints, maybe a HTTP-GET Requests to receive the results directly, but for SIWECOS the above listed is mandatory.

## Response Interface

Your scanner must send a valid JSON Response to the defined `callbackurls` above.

Here is a shortened example output for a correct implemented JSON-Response:

```
{
    "name": "HEADER",
    "version": "1.5.0",
    "hasError": false,
    "errorMessage": null,
    "score": 85,
    "tests": [
        {
            "name": "REFERRER_POLICY",
            "hasError": false,
            "errorMessage": null,
            "score": 0,
            "scoreType": "bonus",
            "testDetails": [
                {
                    "translationStringId": "DIRECTIVE_SET",
                    "placeholders" : [
                        "DIRECTIVE": "no-referrer-when-downgrade"
                    ]
                }
            ]
        },
        {
            "name": "PUBLIC_KEY_PINS",
            "hasError": true,
            "errorMessage": {
                "translationStringId": "HEADER_NOT_SET",
                "placeholders" : [
                    "HEADER": "Public-Key-Pins"
                ]
            },
            "score": 0,
            "scoreType": "bonus",
            "testDetails": []
        }
    ]
}
```

### Global `name` Attribute [string]
The first `name` Attribute is an abbreviation for your scanner.
It has to be unique among all SIWECOS scanners.

At the time of writing we have the following reserved abbreviations:
`DOMXSS`, `HEADER`, `INFOLEAK`, `INI_S`, `TLS` and `VERSION`

### Global `version` Attribute [string]
Regarding the [Programming Requirements](#programming-requirements) you have to implement a semantic versioning scheme.
The actual version number has to be included in the response.

### Global `hasError` Attribute [boolean]
The global `hasError` should be set to `true` if the scanner could not perform it's tests.
An error case could be that the request to the given `url` does not send any response.

### Global `errorMessage` Attribute [TranslatableMessage|null]
The global `errorMessage` should be set to a related [`TranslatableMessage`] if the global `"hasError": true` is set.
Otherwise it should be `null`.

### Global `score` Attribute [integer (min:0; max:100)]
The global `score` should be a total score over all the different tests the scanner performs.
It is left on you to calculate the score to a reasonable value.

### Global `tests` Array [array]
Within the `tests` array you will list all of the performed tests.
Please note, that all test cases should also follow a defined scheme.

Further details regarding the test's structure are listed below.

### Each `test.name` Attribute [string]
Each test must have an unique name that identifies the test.
The `name` attribute must only consists of uppercased letters and underscores (SCREAMING_SNAKE_CASE).

### Each `test.hasError` Attribute [boolean]
The attributes determines if the related test had an error.

### Each `test.errorMessage` Attribute [TranslatableMessage|null]
The test's `errorMessage` should be set to a corresponding [`TranslatableMessage`] if the related `"hasError": true` is set.
Otherwise it should be `null`.

### Each `test.score` Attribute [integer (min:0; max:100)]
The score for the related test can be set in a reasonable way.
A higher score value determines a more secure configuration.

### Each `test.scoreType` Attribute [string]
For each test a `scoreType` must be defined.
There are several values you can choose from:

| Value      | Description                                                           | Score-Influence                                         |
| ---------- | --------------------------------------------------------------------- | ------------------------------------------------------- |
| `hidden`   | Hidden test; should not be displayed by the webapp                    | None                                                    |
| `bonus`    | Featured configuration, recommended but not required                  | Raises the score if positive, no drop if not configured |
| `success`  | Successful test                                                       | Raises the score                                        |
| `warning`  | Attention, a faulty or bad configuration was detected; not `critical` | Reduces the score                                       |
| `critical` | Critical missconfiguration; service or users are in danger            | Drops the score to a maximum of 20 points               |

### Each `test.testDetails` Array [array]
For each test you can provide further information via the `testDetails` array.
The array itself must only consist of [`TranslatableMessage`]s.

### TranslatableMessage Object
In order to provide translations for `errorMessage`s or `testDetails` there is the `TranslatableMessage` object with the following structure:

```
{
    "translationStringId": "EXAMPLE_STRING",
    "placeholders" : [
        "PLACEHOLDER_NAME": "VALUE_FOR_PLACEHOLDER"
    ]
}
```

**Example:**
Given is the translatable string with:

```
"FAVORITE_NUMBER" => "The best number of all numbers is :NUMBER!"
```

The `TranslatableMessage`:

```
{
    "translationStringId": "FAVORITE_NUMBER",
    "placeholders" : [
        "NUMBER": 42
    ]
}
```

Will be translated to: `The best number of all numbers is 42!`.

