var https = require('https');
var fs = require('fs');
var amqp = require('amqp');
var xml2js = require('xml2js');

var config = require('./secrets').config;

var app = https.createServer(config.https_options, handler).listen(config.port);
var io = require('socket.io').listen(app);
//io.set('log level', 2); //info

var events = [];
var clients = [];

function handler(req, res) {
  fs.readFile(__dirname + '/event.html',
  function (err, data) {
    if (err) {
      res.writeHead(500);
      return res.end('Error loading html');
    }
    res.writeHead(200);
    res.end(data);
  });
}

console.log("connecting to amqp");
var connection = amqp.createConnection(config.amqp);

function open_exchanges(callback) {
    console.log('amqp connection is ready - connecting to various exchanges');
    connection.exchange('oim', {type: 'topic', autoDelete: false}, function (oim_ex) {
        connection.exchange('rsv', {type: 'topic', autoDelete: false}, function (rsv_ex) {
            connection.exchange('ticket', {type: 'topic', autoDelete: false}, function (ticket_ex) {
                console.log("connected to oim/rsv/ticket exchanges");
                callback(oim_ex, rsv_ex, ticket_ex);
            });
        });
    });
}

function open_queue(oim_ex, rsv_ex, ticket_ex, callback) {
    console.log('amqp exchanged ready - preparing queues');
    connection.queue('', { durable: false, autoDelete: false}, function(queue) {
        queue.bind(oim_ex, '#', function() {
            queue.bind(rsv_ex, '#', function() {
                queue.bind(ticket_ex, '#', function() {
                    console.log("connected to queue and bound exchanges");
                    callback(queue);
                });
            });
        });
    });
}

connection.on('ready', function () {
    console.log('amqp ready');
    open_exchanges(function(oim_ex, rsv_ex, ticket_ex) {
        open_queue(oim_ex, rsv_ex, ticket_ex, function(queue) {
            console.log("subscribing...");
            queue.subscribe(function (message, headers, deliveryInfo) {
                console.log("event received:"+deliveryInfo.routingKey);
                //console.dir(deliveryInfo);
                //console.dir(headers);
                //console.dir(message);
                var parser = new xml2js.Parser();
                parser.parseString(message.data, function(err, obj) {
                    var event = {
                        time: new Date(),
                        content: obj,
                        key: deliveryInfo.routingKey,
                        exchange: deliveryInfo.exchange
                    };
                    clients.forEach(function(client) {
                        client.emit('event', event);
                    });
                    events.push(event);
                    if(events.length > 100) {
                        events.shift(); //push at the top
                    }
                }); 
            });
        });
    });
});

io.sockets.on('connection', function (socket) {
    console.log("client connected");
    socket.emit('events', events);
    clients.push(socket);

    socket.on('disconnect', function() {
        console.log("client disconnected"+socket);
        for(i = 0;i < clients.length; i++) {
            if(clients[i] == socket) {
                clients.splice(i,1);
                break;
            }
        }
        //TODO - remove disconnected client from sockets list?
    });
});

//TODO - remove client when disconnect?

