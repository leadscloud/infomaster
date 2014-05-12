<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-44689298-1', 'shibangsoft.com');
/*
  var _gaq = _gaq || [];
  _gaq.push(['_setCustomVar', 1,'user','<?php echo $_USER["name"]?>',1]);
  _gaq.push(['_trackPageview']);
*/
ga('send', 'pageview', {
  'dimension2': '<?php echo $_USER["name"]?>'
});

  var dimensionValue = '<?php echo $_USER["name"]?>';
  ga('set', 'dimension1', dimensionValue);

  ga('send', 'pageview');

  

</script>