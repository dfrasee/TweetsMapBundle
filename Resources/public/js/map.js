    
var map = {
     current_lat: 0,
     current_lng: 0,
     current_map: null,
     _setup:function(){
         $('#search').click(map.search);
     },

     search: function(e){
        e.preventDefault();
        var q = $('#search_key').val();
        if($.trim(q).length) {
            $.ajax({
                url: $(this).attr('href'),
                data: {q:q},
                dataType: 'json',
                beforeSend: function( xhr ) {

                     $.blockUI({ css: { 
            border: 'none', 
            padding: '15px', 
            backgroundColor: '#000', 
            '-webkit-border-radius': '10px', 
            '-moz-border-radius': '10px', 
            opacity: .5, 
            color: '#fff' 
        } }); 
 
                }
            })
            .done(function( data ) {
               map.initialize(data.lat, data.lng,12);
               map.show_tweets(data);
                $.unblockUI(); 
            });
        }else{
            $.blockUI({message: 'Please endter city name',timeout: 2000} ); 
        }
     },

     initialize: function(latitude,longitude,zoom) {
        document.getElementById('map-canvas').innerHTML = '';
        var mapOptions = {
          center: new google.maps.LatLng(latitude, longitude),
          zoom: zoom,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        map.current_map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);     
     },
     
    // draw tweets data to the map         
     show_tweets: function (data) {
        var mapLabel = new MapLabel({
          text: 'TWEET ABOUT '+data.city.toUpperCase(),
          position: new google.maps.LatLng(data.lat, data.lng),
          map: map.current_map,
          fontSize: 20,
          fontWeight: 'blod',
          fontColor: '#066896',
          align: 'center',
          whiteSpace: 'nowrap',
          top: '50px'
        });

        if(map.current_map){        
            for (var i = 0; i < data.tweets.length; i++) {
              var tweet = data.tweets[i];
              var infowindow = new google.maps.InfoWindow();
              var marker = new google.maps.Marker({
                  position: new google.maps.LatLng(tweet.lat, tweet.lng),
                  map: map.current_map,
                  icon: {url: tweet.avatar, size:new google.maps.Size(40, 40)},
                  shape: {coords:[20,20,20],type: 'circle'},
                  title: addslashes(tweet.title),
                  zIndex: (9+i)
              });
              
              google.maps.event.addListener(marker, 'click', (function(marker, i) {
                    return function() {
                      infowindow.setContent('Tweet: '+ marker.title);
                      infowindow.open(map.current_map, marker,  marker.title);
                    }
              })(marker, i));
            }
        }
    }

};

$(document).ready(function(){
    $(map._setup);
});

function addslashes(str){
  return (str + '')
    .replace(/[\\"']/g, '\\$&')
    .replace(/\u0000/g, '\\0');
}
    