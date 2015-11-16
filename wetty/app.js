var https = process.env.WETTY_HTTPS == 'true';
var http = https ? require('https') : require('http');
var ws = require('websocket').server;
var pty = require('pty.js');
var fs = require('fs');

process.on('uncaughtException', function(e) {
    console.error('Error: ' + e);
});

var opts = {};

if (https) {
    opts.key = fs.readFileSync( process.env.WETTY_CERT_KEY );
    opts.cert = fs.readFileSync( process.env.WETTY_CERT_CRT );
}

var httpserv = http.createServer(https ? opts : undefined).listen(4000);

var wss = new ws({
    httpServer: httpserv
});

wss.on('request', function(request) {
    var term;
    var container = '';
    var conn = request.accept('wetty', request.origin);
    console.log((new Date()) + ' Connection accepted.');
    if (request.resource.match('^/container/')) {
        container = request.resource;
        container = container.replace('/container/', '');
    }

    conn.on('message', function(msg) {
        var data = JSON.parse(msg.utf8Data);
        if (!term) {
            term = pty.spawn('docker', ["exec", "-ti", container, "bash"], {
                name: 'xterm-256color',
                cols: 80,
                rows: 30
            });
            console.log((new Date()) + " PID=" + term.pid + " STARTED in " + container);
            term.on('data', function(data) {
                conn.send(JSON.stringify({
                    data: data
                }));
            });
            term.on('exit', function() {
                term.end();
            })
        }
        if (!data)
            return;
        if (data.rowcol) {
            term.resize(data.col, data.row);
        } else if (data.data) {
            term.write(data.data);
        }
    });

    conn.on('error', function() {
        term.end();
    });

    conn.on('close', function() {
        term.end();
    })
});
