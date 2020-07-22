<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="{{asset('css/app.css')}}">
<title>{{__('messages.AppName')}} - Logout</title>
</head>
<body class="">
<div class="container" style="padding-top:10em;">

<div class="card"><div class="card-body img-auto-fluid text-center">
    Hello Teams

    
</div></div>
</div>
<script src="{{asset('js/app.js')}}"></script>
<script src="https://unpkg.com/@microsoft/teams-js@1.5.0/dist/MicrosoftTeams.min.js"></script>
<script>
  // Call the initialize API first
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

  var authTokenRequest = {
  successCallback: function(result) { console.log("Success: " + result); },
  failureCallback: function(error) { console.log("Failure: " + error); },
};
microsoftTeams.authentication.getAuthToken(authTokenRequest);
</script>
</body>
</html>
