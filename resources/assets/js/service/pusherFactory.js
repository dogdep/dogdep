(function() {
    angular
        .module('dt')
        .factory('Pusher', push);

    function push() {
        var pusher = new Pusher('{$TOKEN:PUSHER_KEY}', {
            encrypted: true
        });
        var pulls = pusher.subscribe('pulls');
        var releases = pusher.subscribe('releases');

        return {
            pusher: function() {
                return pusher;
            },
            pulls: function() {
                return pulls;
            },
            releases: function() {
                return releases;
            }
        };
    }
})();
