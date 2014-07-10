(function () {
  var empty_fn = new Function; 
//  var console  = window.console || { log: empty_fn };
  var jump_url_re = /^https??:\/\/[^\/]+\/url\?/i;
  var add_event_listener = function (element, event_type, handler, use_capture) {
    element = element || {};
    if(element.attachEvent) {
      element.attachEvent('on' + event_type, handler);
      return true;
    }
    if(element.addEventListener) {
      element.addEventListener(event_type, handler, use_capture);
//      console.log(arguments);
      return true;
    }
    return false;
  }
  var fix_url = function () {
    var links = document.getElementsByTagName("a"), link, r, j;
    for(var i = links.length - 1; i >= 0; --i) {
      var link = links[i];
      link.setAttribute("rel", "noreferrer");
      link.rel = "noreferrer";
// link.href.indexOf('/url?') < 0 && ( link.href = '/url?url=' + encodeURIComponent(link.href) ); continue; // test jump url
      r = jump_url_re.exec(link.href);
      if(r) {
        r = link.href.substr(r[0].length).split('&');
        for(j = r.length -1; j >= 0; --j) {
          if(/^url=/.test(r[j])) {
            link.href = decodeURIComponent(r[j].substr(4));
//            console.log(link.href);
            break;
          }
        }
      }
    }
  }
/* The first time I want to remove all event handlers from an HTMLElement by cloning it and replacing it, but unfortunately we can't remove handlers that are attached by onxxxx attribute(e.g. onmousedown), and even worse, once onmousedown has been triggered, we can't stop it, because this event usually is fired on <a> before on <body>. We can stop redirecting, but we can't stop it from calling the corresponding onmousedown handler. So I come up with the solution: set window.rwt to an empty function constantly to override Google's window.rwt(<a> 's onmousedown will call it) for 5 seconds(every 50 ms).
  var f = function (evt) {
    try {
        var clone_link = link.cloneNode(true);
        link.parentNode.replaceChild(clone_link, link);
        link = clone_link;
        link.setAttribute("rel", "noreferrer");
        link.rel = "noreferrer";
        link.removeAttribute("ping");
        link.onmousedown = null;
    } catch (e) { console.log(e); }
  };
*/
//  add_event_listener(window, 'load', function () {
    var monitored = false;
    var count = 100;
    var timer = setInterval(function () {
        var body;
        window.rwt = empty_fn;
        if(! monitored && (body = document.getElementsByTagName("body")[0]) ) {
          add_event_listener(body, 'click', fix_url, false);
          add_event_listener(body, 'mousedown', fix_url, false);
          monitored = true;
        }
        if(--count < 0) clearInterval(timer);
      }, 50);
//  }, false);
})();
