
<?php require_once __DIR__ . '/app_helper.php'; renderBackButton(); ?>
<script>
(function(){
    var manifest=document.createElement('link');manifest.rel='manifest';manifest.href=new URL('manifest.json',document.baseURI).href;document.head.appendChild(manifest);
    var theme=document.createElement('meta');theme.name='theme-color';theme.content='#0f766e';document.head.appendChild(theme);
    if('serviceWorker' in navigator && (location.protocol==='https:' || location.hostname==='localhost')){
        window.addEventListener('load',function(){navigator.serviceWorker.register(new URL('sw.js',document.baseURI).href).catch(function(error){console.warn('PWA worker tidak dapat dipasang:',error);});});
    }
})();
</script>
