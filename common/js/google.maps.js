

window.google = window.google || {};
google.maps = google.maps || {};
(function() {
  
  function getScript(src) {
    document.write('<' + 'script src="' + src + '"' +
                   ' type="text/javascript"><' + '/script>');
  }
  
  var modules = google.maps.modules = {};
  google.maps.__gjsload__ = function(name, text) {
    modules[name] = text;
  };
  
  google.maps.Load = function(apiLoad) {
    delete google.maps.Load;
    apiLoad([0.009999999776482582,[[["http://mt0.googleapis.com/vt?lyrs=m@210000000\u0026src=api\u0026hl=zh-CN\u0026","http://mt1.googleapis.com/vt?lyrs=m@210000000\u0026src=api\u0026hl=zh-CN\u0026"],null,null,null,null,"m@210000000"],[["http://khm0.googleapis.com/kh?v=126\u0026hl=zh-CN\u0026","http://khm1.googleapis.com/kh?v=126\u0026hl=zh-CN\u0026"],null,null,null,1,"126"],[["http://mt0.googleapis.com/vt?lyrs=h@210000000\u0026src=api\u0026hl=zh-CN\u0026","http://mt1.googleapis.com/vt?lyrs=h@210000000\u0026src=api\u0026hl=zh-CN\u0026"],null,null,"imgtp=png32\u0026",null,"h@210000000"],[["http://mt0.googleapis.com/vt?lyrs=t@130,r@210000000\u0026src=api\u0026hl=zh-CN\u0026","http://mt1.googleapis.com/vt?lyrs=t@130,r@210000000\u0026src=api\u0026hl=zh-CN\u0026"],null,null,null,null,"t@130,r@210000000"],null,null,[["http://cbk0.googleapis.com/cbk?","http://cbk1.googleapis.com/cbk?"]],[["http://khm0.googleapis.com/kh?v=72\u0026hl=zh-CN\u0026","http://khm1.googleapis.com/kh?v=72\u0026hl=zh-CN\u0026"],null,null,null,null,"72"],[["http://mt0.googleapis.com/mapslt?hl=zh-CN\u0026","http://mt1.googleapis.com/mapslt?hl=zh-CN\u0026"]],[["http://mt0.googleapis.com/mapslt/ft?hl=zh-CN\u0026","http://mt1.googleapis.com/mapslt/ft?hl=zh-CN\u0026"]],[["http://mt0.googleapis.com/vt?hl=zh-CN\u0026","http://mt1.googleapis.com/vt?hl=zh-CN\u0026"]],[["http://mt0.googleapis.com/mapslt/loom?hl=zh-CN\u0026","http://mt1.googleapis.com/mapslt/loom?hl=zh-CN\u0026"]],[["https://mts0.googleapis.com/mapslt?hl=zh-CN\u0026","https://mts1.googleapis.com/mapslt?hl=zh-CN\u0026"]],[["https://mts0.googleapis.com/mapslt/ft?hl=zh-CN\u0026","https://mts1.googleapis.com/mapslt/ft?hl=zh-CN\u0026"]]],["zh-CN","US",null,0,null,null,"http://maps.gstatic.com/mapfiles/","http://csi.gstatic.com","https://maps.googleapis.com","http://maps.googleapis.com"],["http://maps.gstatic.com/intl/zh_cn/mapfiles/api-3/12/5","3.12.5"],[1350400096],1.0,null,null,null,null,1,"",null,null,0,"http://khm.googleapis.com/mz?v=126\u0026",null,"https://earthbuilder.googleapis.com","https://earthbuilder.googleapis.com",null,"http://mt.googleapis.com/vt/icon"], loadScriptTime);
  };
  var loadScriptTime = (new Date).getTime();
  getScript("http://maps.gstatic.com/intl/zh_cn/mapfiles/api-3/12/5/main.js");
})();
