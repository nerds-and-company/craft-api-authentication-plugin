# craft-api-authentication
[![Build Status](https://scrutinizer-ci.com/g/nerds-and-company/craft-api-authentication-plugin/badges/build.png?b=master)](https://scrutinizer-ci.com/g/nerds-and-company/craft-api-authentication-plugin/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nerds-and-company/craft-api-authentication-plugin/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nerds-and-company/craft-api-authentication-plugin/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/nerds-and-company/craft-api-authentication-plugin/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/nerds-and-company/craft-api-authentication-plugin/?branch=master)

Plugin to allow generation of an access token for use in api clients

## Routes

`/api/authenticate`

You can post a username and password to the url /api/authenticate
When these are valid credentials you will get an access token,
This access token can be authenticated in the ApiAuthService

`/api/resetPassword`

You can post a username to /api/resetPassword, when a user is found a password reset mail is send.
The link in the mail links to the craft web environment.
When the user is not found a success message is also given.
