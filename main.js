/**
 * main.js
 * Created by hschmale on 3/6/16.
 */

var PhotoBooth = {
    onMediaStream: function(stream) {
        PhotoBooth.canvas = $('canvas')[0];
        PhotoBooth.context = PhotoBooth.canvas.getContext('2d');

        PhotoBooth.localVideo = $('video')[0];
        PhotoBooth.localVideo.src = window.URL.createObjectURL(stream);
    },
    noStream: function() {
        console.log('FAIL TO GET WEBCAM ACCESS');
    }
};

function countdown(secs){
    var countdownDiv = $('.countdown');
    if(secs == 0){
        countdownDiv.hide();
        return;
    }
    countdownDiv.html(secs);
    secs--;
    setTimeout(function() {
        countdown(secs);
    }, 1000);
}

// shows countdown then captures the image at end of countdown
function takePicture() {
    $('.countdown').show();
    countdown(3);
    setTimeout(function() {
        PhotoBooth.context.drawImage(PhotoBooth.localVideo, 0, 0, 200, 150);
        $('#preview').show();
    }, 3000);
}

// uploads the image to the server
function makeNewspaper() {
    var dataUrl = PhotoBooth.canvas.toDataURL();
    var data = {
        name: $('#username').val(),
        email: $('#emailAddress').val(),
        image: dataUrl
    };
    $.post(
        '/api/upload.php',
        data,
        function(resp, status){
            alert(resp);
        }
    );
}

// init the program
function init() {
    // set up event listeners
    $('#takePicture').click(takePicture);
    $('#usePicture').click(makeNewspaper);

    // open the video stream
    getUserMedia(
        {video: true},
        PhotoBooth.onMediaStream,
        PhotoBooth.noStream
    );
}

