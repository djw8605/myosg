var https = require('https');
var fs = require('fs');
var amqp = require('amqp');
var xml2js = require('xml2js');

var options = {
  key: fs.readFileSync('/etc/grid-security/http/key.pem'),
  cert: fs.readFileSync('/etc/grid-security/http/cert.pem')
};
var app = https.createServer(options, handler).listen(12345);
var io = require('socket.io').listen(app);
io.set('log level', 2); //info

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

//connect to amqp
var connection = amqp.createConnection({ 
    host: 'event-itb.goc',
    login: 'myosg',
    password: 'myosg#checkApple',
    vhost: '/osg'
});

function open_exchanges(callback) {
    connection.on('ready', function () {
        console.log('amqp connection is ready');
        connection.exchange('oim', {type: 'topic', autoDelete: false}, function (oim_ex) {
            connection.exchange('rsv', {type: 'topic', autoDelete: false}, function (rsv_ex) {
                connection.exchange('ticket', {type: 'topic', autoDelete: false}, function (ticket_ex) {
                    console.log("connected to oim/rsv/ticket exchanges");
                    callback(oim_ex, rsv_ex, ticket_ex);
                });
            });
        });
    });
}

function open_queue(oim_ex, rsv_ex, ticket_ex, callback) {
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

var events = [];
var clients = [];//clients

//driver..
open_exchanges(function(oim_ex, rsv_ex, ticket_ex) {
    open_queue(oim_ex, rsv_ex, ticket_ex, function(queue) {
        console.log("subscribing...");
        queue.subscribe(function (message, headers, deliveryInfo) {
            console.log("event received:"+deliveryInfo.routingKey);
            console.dir(deliveryInfo);
            console.dir(headers);
            console.dir(message);
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
                    events.unshift(); //push at the top
                }
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
        //TODO - remove disconnected client from sockets list
        /*
        var idx = clients.indexOf(socket);
        console.log(idx + " has disconnected");
        clients.splice(idx, 1);
        */
    });
});


//TODO - remove client when disconnect

