<a id="header"></a>
<header>
    <link rel="stylesheet" href="/src/includes/header/header.css">

    <nav id="navlinklist">
        <?php /*require_once $_SERVER['DOCUMENT_ROOT'] . '/src/includes/header/search/navlink.php';*/ ?>
    </nav>
    <div id="NavBarHolder">
        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/src/includes/header/search/searchbar.html'; ?>
    </div>
    <div id="NavTopModuleHolder"></div>

    <script src="/src/includes/header/header.js"></script>

        <script src="/src/pages/admin/music/music.js"></script> 
        <script>mergeYouTubeTitles(musicLinks, true, false, false);</script>
        <script src="/src/pages/admin/video/video.js"></script> 
        <script>mergeYouTubeTitles(videoLinks, false, true, false);</script>
        <script src="/src/js/film.js"></script>
        <script>mergeYouTubeTitles(filmLinks, false, false, true);</script>

        <script>getURLParameter();</script>
</header>
<link rel="stylesheet" href="/src/css/scrollPercentage.css">
<div id="scrollPercentage"></div>
<script src="/src/js/scrollPercentage.js"></script>