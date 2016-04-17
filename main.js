/*
 * @file main.js
 * Created by hschmale on 3/6/16.
 */

// How long to wait before snapping the picture
const COUNTDOWNLENGTH = 5;
const COUNTDOWN_TIMEOUT = COUNTDOWNLENGTH * 1000;

var PhotoBooth = {
    onMediaStream: function(stream) {
        PhotoBooth.preview = $('canvas')[0];

        PhotoBooth.image = document.createElement('canvas');
        PhotoBooth.image.width = 800;
        PhotoBooth.image.height = 600;

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
    countdown(COUNTDOWNLENGTH);
    setTimeout(function() {
        PhotoBooth.image.getContext('2d')
            .drawImage(PhotoBooth.localVideo, 0, 0,
                PhotoBooth.image.width,
                PhotoBooth.image.height);
        PhotoBooth.preview.getContext('2d')
            .drawImage(PhotoBooth.image, 0, 0, 160, 120);
        $('#preview').show();
    }, COUNTDOWN_TIMEOUT);
}

// returns true if <value> exists in <arr>
function existsInArray(arr, value){
    for(i in arr){
        if(value === arr[i]){
            return true;
        }
    }
    return false;
}

function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

// uploads the image to the server
function makeNewspaper() {
    var dataUrl = PhotoBooth.image.toDataURL("image/jpeg");
    var data = {
        name: $('#username').val(),
        email: $('#emailAddress').val(),
        notes: $('#notes').val(),
        caption: $('#caption').val(),
        image: dataUrl
    };
    // validate inputs
    if(data.name.length <= 1){
        alert("Name Too Short. Enter longer name");
        return;
    }
    data.email.split(',').forEach(function(email){
        if(!validateEmail(email)){
            alert("Email Invalid:" + email);
            return;
        }
    });
    /*
    if(!existsInArray([1,2,3], parseInt(data.printControl))){
        alert("Invalid Print Request");
        return;
    }
    */
    // upload it now because it has not failed
    $.post(
        '/api/upload.php',
        data,
        function(resp, status){
            alert(resp + ' ' + status);
            if(status == 200){
                clearForm();
            }
        }
    );
}

function clearForm(){
    $('#username').val('');
    $('#emailAddress').val('');
    $('#notes').val('');
    $('#caption').val('');
    // clear the canvas and replace with picture to tell user to take picture
    var ctx = PhotoBooth.preview.getContext('2d');
    ctx.clearRect(0, 0, 160, 40);
    ctx.font = "48px serif";
    ctx.fillText("Take\nPicture", 5, 80);
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

