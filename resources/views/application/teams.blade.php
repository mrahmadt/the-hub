<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="{{asset('css/app.css')}}">
<title>{{__('messages.AppName')}} - Logout</title>

</head>
<body class="theme-light">
<div class="container" style="padding-top:10em;">
<div class="surface">
  <div class="panel">


  </div>
</div>
<div class="card"><div class="card-body img-auto-fluid" id="logs">
    Hello Teams
</div></div>

</div>
<script src="{{asset('js/app.js')}}"></script>
<script src="https://unpkg.com/@microsoft/teams-js@1.5.0/dist/MicrosoftTeams.min.js"></script>
<script>

//initTeamsTab.js
(function () {
    'use strict';

    // Call the initialize API first
    microsoftTeams.initialize();

    // Check the initial theme user chose and respect it
    microsoftTeams.getContext(function (context) {
        if (context && context.theme) {
            setTheme(context.theme);
        }
        //microsoftTeams.appInitialization.notifyAppLoaded();
    });

    // Handle theme changes
    microsoftTeams.registerOnThemeChangeHandler(function (theme) {
        setTheme(theme);
    });


    // Set the desired theme
    function setTheme(theme) {
        if (theme) {
            // Possible values for theme: 'default', 'light', 'dark' and 'contrast'
            document.body.className = 'theme-' + (theme === 'default' ? 'light' : theme);
        }
    }

})();


//ssoDemo.js

  (function () {
    'use strict';

    // 1. Get auth token
    // Ask Teams to get us a token from AAD
    function getClientSideToken() {

        return new Promise((resolve, reject) => {

            display("1. Get auth token from Microsoft Teams");

            microsoftTeams.authentication.getAuthToken({
                successCallback: (result) => {
                    display(result)
                    resolve(result);
                },
                failureCallback: function (error) {
                    reject("Error getting token: " + error);
                }
            });

        });

    }

    // 2. Exchange that token for a token with the required permissions
    //    using the web service (see /auth/token handler in app.js)
    function getServerSideToken(clientSideToken) {

        display("2. Exchange for server-side token");

        return new Promise((resolve, reject) => {

            microsoftTeams.getContext((context) => {

                fetch('/teams/auth/token', {
                    method: 'post',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        'tid': context.tid,
                        'token': clientSideToken 
                    }),
                    mode: 'cors',
                    //mode: 'no-cors',
                    //cache: 'default',
                    credentials: 'same-origin',
                    redirect: 'follow'
                })
                .then((response) => {
                    display("2. then response");
                    console.log(response);
                    if (response.ok) {
                        display("2. then response.ok");
                        return response.json();
                    } else {
                        display("2. then response.ok else");
                        reject(response.error);
                    }
                })
                .then((responseJson) => {
                    display("2. then responseJson");
                    console.log(responseJson);
                    if (responseJson.error) {
                        display("2. then responseJson.error");
                        reject(responseJson.error);
                    } else {
                    //     display("2. then responseJson.error else");
                    //     console.log(responseJson);
                    //     window.location.href = responseJson;
                    //     const serverSideToken = responseJson;
                    //     //display(serverSideToken);
                    //     resolve(serverSideToken);
                    }
                });
            });
        });
    }

    // 3. Get the server side token and use it to call the Graph API
    function useServerSideToken(data) {

        display("3. Call https://graph.microsoft.com/v1.0/me/ with the server side token");

        return fetch("https://graph.microsoft.com/v1.0/me/",
            {
                method: 'GET',
                headers: {
                    "accept": "application/json",
                    "authorization": "bearer " + data
                },
                //mode: 'cors',
                cache: 'default',
                credentials: 'same-origin'
            })
            .then((response) => {
                if (response.ok) {
                    return response.json();
                } else {
                    throw (`Error ${response.status}: ${response.statusText}`);
                }
            })
            .then((profile) => {
                display(JSON.stringify(profile, undefined, 4), 'pre');
            });

    }

    // Show the consent pop-up
    function requestConsent() {
        return new Promise((resolve, reject) => {
            microsoftTeams.authentication.authenticate({
                url: window.location.origin + "/teams/auth/auth-start",
                width: 600,
                height: 535,
                successCallback: (result) => {
                    let data = localStorage.getItem(result);
                    localStorage.removeItem(result);
                    resolve(data);
                },
                failureCallback: (reason) => {
                    reject(JSON.stringify(reason));
                }
            });
        });
    }

    // Add text to the display in a <p> or other HTML element
    function display(text, elementTag) {
      console.log('display');
      console.log(text);

        var logDiv = document.getElementById('logs');
        var p = document.createElement(elementTag ? elementTag : "p");
        p.innerText = text;
        logDiv.append(p);
        console.log("ssoDemo: " + text);
        return p;
    }

    // In-line code
    getClientSideToken()
        .then((clientSideToken) => {
            return getServerSideToken(clientSideToken);
        })
        .catch((error) => {
            if (error === "invalid_grant") {
                display(`Error: ${error} - user or admin consent required`);
                // Display in-line button so user can consent
                let button = display("Consent", "button");
                button.onclick = (() => {
                    requestConsent()
                        .then((result) => {
                            // Consent succeeded - use the token we got back
                            let accessToken = JSON.parse(result).accessToken;
                            display(`Received access token ${accessToken}`);
                            //TODO FIX ME
                            alert('FIX ME');
                            //useServerSideToken(accessToken);
                        })
                        .catch((error) => {
                            display(`ERROR ${error}`);
                            // Consent failed - offer to refresh the page
                            button.disabled = true;
                            let refreshButton = display("Refresh page", "button");
                            refreshButton.onclick = (() => { window.location.reload(); });
                        });
                });
            } else {
                // Something else went wrong
                display(`Error from web service: ${error}`);
            }
        });

})();
</script>
</body>
</html>
