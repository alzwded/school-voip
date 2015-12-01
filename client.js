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
    var session = {
audio: true,
       video: false
    }
    var recordRTC = null
    navigator.webkitGetUserMedia(session, initializeRecorder, onError)
}

function initializeRecorder(stream) {
    var audioContext = window.AudioContext
    var context = new AudioContext()
    var audioInput = context.createMediaStreamSource(stream)
    var bufferSize = 2048
    // create a javascript node
    var recorder = context.createScriptProcessor(bufferSize, 1, 1)
    // specify the processing function
    recorder.onaudioprocess = recorderProcess
    // connect stream to our recorder
    audioInput.connect(recorder)
    // connect our recorder to the previous destination
    recorder.connect(context.destination)
}

function recorderProcess(e) {
    var samp = e.inputBuffer.getChannelData(0)
    for(var i = 0; i < e.inputBuffer.length; i++) {
        if(Math.abs(samp[i]) > 0.7) {
            console.log("big sample")
        }
    }
}

function onError(e) {
    alert('cannot microphone')
}
