#!/usr/bin/node

var dir = require('node-dir');
var fs = require('fs');
var _ = require('underscore');
var sleep = require('sleep');
var nodemailer = require('nodemailer');
var sendmailTransport = require('nodemailer-sendmail-transport');

// global message data
var users = {emails: []};
var transporter = nodemailer.createTransport(sendmailTransport({
    path: '/usr/bin/sendmail'
}));

// checks if a string ends with a certain string
function endsWith(str, suffix) {
    return str.indexOf(suffix, str.length - suffix.length) !== -1;
}

function sendEmail(address){
    var msg = fs.readFileSync('/home/public/private/message.txt'); 

    var mailOpts = {
        from: 'Barbra Birthday Photobooth <no-reply@henryschmale.org>',
        to: 'hschmale16@gmail.com',
        subject: 'Barbara\'s Birthday Party Photos are up',
        text: msg
    };
    console.log(mailOpts); 
}


dir.files(__dirname, function(err, files) {
    if(err) throw err;

    for(var path in files) {
        if(endsWith(files[path], 'json')){
            var data = fs.readFileSync(files[path], 'utf8');
            if(!data){continue;}
            var json = JSON.parse(data);
            json.email.forEach(function(email){
                users.emails.push(email.trim());
            });
        }
    }
    users.emails = _.uniq(users.emails);
    console.log(users);
});

