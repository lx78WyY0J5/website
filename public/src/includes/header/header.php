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
     <?php
        echo "<script>"; 
        require_once $_SERVER['DOCUMENT_ROOT'] . '/src/pages/admin/music/music.js'; 
        echo "mergeYouTubeTitles(musicLinks, true, false, false);";
        require_once $_SERVER['DOCUMENT_ROOT'] . '/src/pages/admin/video/video.js'; 
        echo "mergeYouTubeTitles(videoLinks, false, true, false);";
        require_once $_SERVER['DOCUMENT_ROOT'] . '/src/js/film.js'; 
        echo "mergeYouTubeTitles(filmLinks, false, false, true);";
        echo "</script>";
     ?>
    <script>getURLParameter();</script>
</header>
<link rel="stylesheet" href="/src/css/scrollPercentage.css">
<div id="scrollPercentage"></div>
<script src="/src/js/scrollPercentage.js"></script>