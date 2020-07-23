require('./bootstrap');

alert('New Build!');
if ($(window).width() <= 576) {
    $('.collapse-sm').each(function(){
      $(this).addClass('collapse'); 
    })
  }

$(document).ready(function(){
    $( ".show-unpin-apps" ).click(function( event ) {
        event.preventDefault();
        $('.btn-unpin-app').toggleClass('d-none');
        $(".show-unpin-apps").toggleClass('active');
    });

    $( ".btn-unpin-app" ).click(function( event ) {
        event.preventDefault();
        var app_id = $(this).attr('data-id');
        $('#AppID'+app_id).remove();
        var resouce_url = $(this).attr('data-url');

        var jqxhr = $.get( resouce_url, function(data) {
        })
        .fail(function() {
            alert('Unknown error, please try again later!');
        });

    });
    
    $( ".btn-pin-app" ).click(function( event ) {
            event.preventDefault();
            var app_id = $(this).attr('data-id');
            var resouce_url = $(this).attr('data-url');
            var $newobj = $('<span></span>').replaceAll(this)
            $newobj.addClass('spinner-border spinner-border-sm').attr('role','status');
            var jqxhr = $.get( resouce_url, function(data) {
                var html = '<svg width="2em" height="2em" viewBox="0 0 16 16" class="bi bi-file-plus-fill pin-icon-gray" fill="currentColor" xmlns="http://www.w3.org/2000/svg">';
                html = html + '<path fill-rule="evenodd" d="M12 1H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zM8.5 6a.5.5 0 0 0-1 0v1.5H6a.5.5 0 0 0 0 1h1.5V10a.5.5 0 0 0 1 0V8.5H10a.5.5 0 0 0 0-1H8.5V6z"/>';
                html = html + '</svg>';
                $newobj.removeClass('spinner-border spinner-border-sm').attr('role','');
                $newobj.html(html);
            })
            .fail(function() {
                alert('Unknown error, please try again later!');
            });
            
    });
  
    $( ".btn-info-app" ).click(function( event ) {
        event.preventDefault();
        var app_id = $(this).attr('data-id');
        var resouce_url = $(this).attr('data-url');

        $('#app-infoLabel').text($('.app-name-' + app_id).text());
        $('#app-info-body').html('<div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div> Please Wait..');
        $('#app-info-modal').modal();
        
        var jqxhr = $.get( resouce_url, function(data) {
            var html = '';
            if(typeof data.application.icon != "undefined" && data.application.icon !=null){
                html = '<img src="'+data.application.icon+'" class="rounded img-fluid app-icon d-block mx-auto">'
            }
            if(typeof data.application.description != "undefined" && data.application.description !=null){
                html = html + '<br/>'+data.application.description
            }
            if(html == '' && typeof data.application.name != "undefined" && data.application.name !=null){
                html = data.application.name;
            }
            $('#app-info-body').html(html);
        })
        .fail(function() {
            $('#app-info-body').html('Unknown error, please try again later!');
        });
    });

  if(inIframe()){
      console.log('iframe');
    $('.NewPageForIframe').each(function( index ) {
        console.log( index + ": " + $( this ).attr('href') );
        $( this ).attr('target','_new');
    
    });
  }else{
    console.log('NO iframe');
  }
});

function inIframe () {
    try {
        return window.self !== window.top;
    } catch (e) {
        return true;
    }
}

function showUnDismissedAlerts(){
    //if there is no localStorage, just show all alerts and return
    if (!localStorage.getItem(`dismissedAlerts_${document.title}`)) {
        $('.alert').removeClass('d-none')
        return;
    }
    //get dismissed alert ID's
    let dismissedAlerts = JSON.parse(localStorage.getItem(`dismissedAlerts_${document.title}`))
    //look for each alert if it was dismissed
    $('.alert').map((index, el) => {
        //get the alert ID
        let alertId = $(el).attr('id')
        //if there is no ID, return (next alert)
        if (!alertId) return;
        //assuming the alert isn' dismissed
        let dismissed = false
        for (let i = 0; i < dismissedAlerts.length; i++) {
            //if the alert is present, it was dismissed, therefore set dismissed to true and exit for
            if (alertId == dismissedAlerts[i]) {
                dismissed = true
                break;
            }
        }
        //if alert isn't dismissed, show it
        if (!dismissed) $(el).removeClass('d-none')
    })
}

//fires if there are alerts on page
if ($('.alert').length > 0) {
    $('.alert').on('click', '.close', function (e) {
        e.preventDefault()
        //get alert element
        let parent = $(this).parent()
        //get alert ID
        let id = $(parent).attr('id')
        //return if no ID is defined
        if (!id) return;
        //get all dismissed alerts, based om localStorage. The document title is in key to prevent long data arrays
        if (!localStorage.getItem(`dismissedAlerts_${document.title}`)) {
            //if storage doesn't exists, add it with the dismissed alert ID
            localStorage.setItem(`dismissedAlerts_${document.title}`, JSON.stringify([id]))
        } else {
            //localStorage exists, so get it and parse it to an array
            let alerts = JSON.parse(localStorage.getItem(`dismissedAlerts_${document.title}`))
            //assuming the alert isn't already dismissed
            let alreadyDismissed = false
            //loop trough dismissed alerts and find out if there is a double
            alerts.map(alertId => {
                //if id is already there, return
                if (alertId == id) {
                    alreadyDismissed = true
                    return;
                }
            })
            //if id alert ID isn't added, so add it an store the new dismissed alerts array
            if (!alreadyDismissed) {
                alerts.push(id)
                localStorage.setItem(`dismissedAlerts_${document.title}`, JSON.stringify(alerts))
            }
        }
    })

    //show all the undismissed alerts
    showUnDismissedAlerts()
}
