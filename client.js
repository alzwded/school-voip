function button_onclick(e) {
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

var playbackCtx,
    dontAskForMore = false

function init_voip()
{
    var session = {
audio: true,
       video: false
    }
    var recordRTC = null
    navigator.webkitGetUserMedia(session, initializeRecorder, onError)

    var audioContext = window.AudioContext
    playbackCtx = new AudioContext()

    setInterval(function() {
        if(dontAskForMore) return
        if(getState() != 'silence') return
        var request = new XMLHttpRequest();
        request.open('GET', 'listen.php', true);
        request.responseType = 'arraybuffer';

        // Decode asynchronously
        request.onload = function() {
            if(request.response.byteLength == 0) return
            console.log('received ' + request.response.byteLength)
            var source = playbackCtx.createBufferSource()
            source.onended = function() {
                dontAskForMore = false
            }
            //buffer = playbackCtx.createBuffer(1, request.response.byteLength, playbackCtx.sampleRate);
            buffer = playbackCtx.createBuffer(1, request.response.byteLength, playbackCtx.sampleRate);
            var len = Math.floor(request.response.byteLength / 4)
            var f32 = new Float32Array(request.response, 0, len)
            buffer.copyToChannel(f32, 0)
            source.buffer = buffer
            source.connect(playbackCtx.destination)
            source.loop = false
            source.connect(playbackCtx.destination)
            source.start()
            dontAskForMore = true
        }
        request.onerror = function(e) {
            alert(e.target.status + "\n" + e.target.responseText)
        }
        request.send();
    }, 100)
}

function initializeRecorder(stream) {
    var audioContext = window.AudioContext
    var context = new audioContext()
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

var leBuf = new Array(),
    leDuf = new Array(),
    state = 'stop'

function getState() {
    var el = $('#status')
    if(el.hasClass('gray')) {
        state = 'stop'
        return 'stop'
    } else if(state == 'stop') {
        state = 'silence'
    }
    return state
}

function recorderProcess(e) {
    var samp = e.inputBuffer.getChannelData(0)
    state = getState()
    if(state == 'gray') return
    for(var i = 0; i < e.inputBuffer.length; i++) {
        if(Math.abs(samp[i]) > 0.25) {
            if(state == 'recording') {
                leBuf.push(samp[i])
            } else if(state == 'waiting') {
                state = 'recording'
                leBuf.push.apply(leBuf, leDuf)
                leDuf = new Array()
            } else if(state == 'silence') {
                state = 'recording'
                $('#status').removeClass('idling playing talking')
                $('#status').addClass('talking')
                console.log('changed status lol')
                leBuf.push(samp[i])
            }
        } else {
            if(state == 'recording') {
                state = 'waiting'
                leDuf.push(samp[i])
            } else if(state == 'waiting') {
                leDuf.push(samp[i])
                if(leDuf.length > 44100 / 1.5) {
                    $('#status').removeClass('idling playing talking')
                    $('#status').addClass('idling')
                    state = 'silence'
                    sendBuffer()
                }
            } else if(state == 'silence') {
                // NOP
            }
        }
    }
}

function onError(e) {
    alert('cannot microphone')
}

function sendBuffer() {
    var ddata = Float32Array.from(leBuf)
    console.log('sending ' + (leBuf.length*4) + ' bytes')
    jQuery.ajax('speak.php', {
        'processData': false,
        'contentType': 'application/octet-stream',
        'data': ddata,
        'method': 'PUT',
        'error': function(jqXHR, textStatus, errorThrown) {
            alert(textStatus + "\n" + errorThrown + "\n" + jqXHR.responseText)
        },
    }).done(function(data) {
        console.log('upload done?')
        console.log(data)
    })

    leBuf = new Array()
    leDuf = new Array()
}
