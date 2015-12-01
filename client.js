function button_onclick(e)
{
    alert('TODO connect')
    // $('#status').removeClass('idling playing')
    // $('#status').addClass('talking')

    jQuery.ajax('connect.php', {
        'data': {
            'username': $('#username').val()
        },
        'method': 'POST',
        'error': function(jqXHR, textStatus, errorThrown) {
            $('#status').removeClass('idling playing talking')
            $('#status').addClass('gray')
            alert(textStatus + "\n" + errorThrown + "\n" + jqXHR.responseText)
        },
    }).done(function(data) {
        $('#status').removeClass('gray')
        $('#status').addClass('idling')
        console.log('active users:')
        console.log(data)
    })
}

function init_voip()
{
}
