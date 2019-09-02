---
title: SIWECOS CORE API Reference

language_tabs: # must be one of https://git.io/vQNgJ
  - http
  - shell

toc_footers:
  - <a href='https://github.com/tripit/slate'>Documentation Powered by Slate</a>

includes:
  - scans
  - logs

search: true
---

# Introduction

This is the documentation for the [SIWECOS Core API](https://github.com/SIWECOS/siwecos-core-api).

The main purpose of the this project is to orchestrate all [SIWECOS](https://siwecos.de) related scaners and aggregate their results via one very simple API.


## Supported Scanners

At the moment the following scanners are offically supported by the Core API:

| Name                                                     | Purpose                                                                             |
| -------------------------------------------------------- | ----------------------------------------------------------------------------------- |
| [BLACKLIST](https://github.com/SIWECOS/Ini-S-Scanner)    | Check offical blacklists for a given URL (SPAM, Phishing, Malware)                  |
| [DOMXSS](https://github.com/SIWECOS/HSHS-DOMXSS-Scanner) | Check for DOMXSS-related sources and sinks                                          |
| [HEADER](https://github.com/SIWECOS/HSHS-DOMXSS-Scanner) | Check for secure HTTP-Header configurations                                         |
| [INFOLEAK](https://github.com/SIWECOS/InfoLeak-Scanner)  | Check for unintentional published information (phone numbers, mail addresses, ... ) |
| [PORT](https://github.com/SIWECOS/WS-Port-Scanner)       | Check for critical opened ports on the webserver                                    |
| [TLS](https://github.com/SIWECOS/WS-TLS-Scanner)         | Check for a secure TLS implementation and configuration                             |
| [VERSION](https://github.com/SIWECOS/Version-Scanner)    | Check for up to date CMS version                                                    |
