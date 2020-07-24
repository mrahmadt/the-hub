<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="{{asset('css/app.css')}}">
<title>{{__('messages.AppName')}} - Logout</title>

</head>
<body class="theme-light">
<div class="container text-center" style="padding-top:10em;">

    <div class="d-flex text-center justify-content-center" id="spinner">
    <div class="spinner-grow text-info" style="width: 3rem; height: 3rem;" role="status">
        <span class="sr-only">Loading...</span>
    </div>
    </div>
<div class="text-center" id="logs">
</div>

</div>
<script src="{{asset('js/app.js')}}"></script>
<script src="https://unpkg.com/@microsoft/teams-js@1.5.0/dist/MicrosoftTeams.min.js"></script>
<script>

(function () {
    'use strict';
    microsoftTeams.initialize();

    // Check the initial theme user chose and respect it
    microsoftTeams.getContext(function (context) {
        if (context && context.theme) {
            setTheme(context.theme);
        }
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
  (function () {
    'use strict';

    function getClientSideToken() {
        return new Promise((resolve, reject) => {
            microsoftTeams.authentication.getAuthToken({
                successCallback: (result) => {
                    resolve(result);
                },
                failureCallback: function (error) {
                    reject("Error getting token: " + error);
                }
            });

        });

    }

    function getServerSideToken(clientSideToken) {
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
                    credentials: 'same-origin',
                    redirect: 'follow'
                })
                .then((response) => {
                    microsoftTeams.appInitialization.notifyAppLoaded();
                    microsoftTeams.appInitialization.notifySuccess();
                    if (response.ok) {
                        return response.json();
                    } else {
                        reject(response.error);
                    }
                })
                .then((responseJson) => {
                    
                    console.log(responseJson);
                    if (responseJson.error) {
                        reject(responseJson.error);
                    } else {
                        window.location.href = responseJson;
                        resolve(responseJson);
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
        var logDiv = document.getElementById('logs');
        var p = document.createElement(elementTag ? elementTag : "p");
        p.innerText = text;
        logDiv.append(p);
        console.log(text);
        return p;
    }

    // In-line code
    getClientSideToken()
        .then((clientSideToken) => {
            return getServerSideToken(clientSideToken);
        })
        .catch((error) => {
            
            //var spinnerDiv = document.getElementById('spinner');
            //spinnerDiv.style.display = "none";
            $('#spinner').addClass('d-none');
            if (error === "invalid_grant") {
                display(`User consent required`);
                // Display in-line button so user can consent
                let button = display("Consent", "button");
                button.className ="btn btn-primary btn-xl"
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
                display(`Error: ${error}`);
            }
        });

})();
</script>
</body>
</html>
