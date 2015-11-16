(function() {
    angular
        .module('dt')
        .directive('dtTerminal', dir);

    function dir() {
        return {
            restrict: 'AE',
            scope: {
                socket: '=dtTerminal'
            },
            link: function(scope, element) {
                var term;
                var ws;

                if (!scope.socket) {
                    return;
                }

                ws = new WebSocket(scope.socket, 'wetty');
                ws.onopen = function() {
                    lib.init(function() {
                        term = new hterm.Terminal();
                        window.term = term;
                        term.decorate(element[0]);

                        term.setCursorPosition(0, 0);
                        term.setCursorVisible(true);
                        term.prefs_.set('ctrl-c-copy', true);
                        term.prefs_.set('use-default-window-copy', true);

                        term.runCommandClass(Wetty, document.location.hash.substr(1));
                        ws.send(JSON.stringify({
                            rowcol: true,
                            col: term.screenSize.width,
                            row: term.screenSize.height
                        }));
                    });
                };

                scope.$on('$destroy', function() {
                    ws.close();
                });

                ws.onmessage = function(msg) {
                    if (!msg || !msg.data)
                        return;
                    var data = JSON.parse(msg.data);
                    if (term)
                        term.io.writeUTF16(data.data);
                };

                ws.onerror = function(e) {
                    console.log("WebSocket connection error");
                };

                ws.onclose = function() {
                    console.log("WebSocket connection closed");
                };

                function Wetty(argv) {
                    this.argv_ = argv;
                    this.io = null;
                    this.pid_ = -1; // needed by hterm

                    this.run = function() {
                        this.io = this.argv_.io.push();

                        this.io.onVTKeystroke = this.sendString_.bind(this);
                        this.io.sendString = this.sendString_.bind(this);
                        this.io.onTerminalResize = this.onTerminalResize.bind(this);
                    };

                    this.sendString_ = function(str) {
                        ws.send(JSON.stringify({data: str}));
                    };

                    this.onTerminalResize = function(col, row) {
                        if (!ws) {
                            return;
                        }

                        ws.send(JSON.stringify({rowcol: true, col: col, row: row}));
                    };
                }
            }
        };
    }
})();
