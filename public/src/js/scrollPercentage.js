console.log("scrollPercentage.js loaded");

document.addEventListener('scroll', function () {
    var scroll = getScrollPercentage();
    setWidthPercentage(scroll);
});

function getScrollPercentage() {
    var height = document.body.clientHeight;
    var top = document.body.scrollTop;
    var maxHeight = document.body.scrollHeight;

    const scrollPercent = (top / (maxHeight - height)) * 100;

    return scrollPercent;
}

function setWidthPercentage(scrollPercentage) {
    var scroller = document.getElementById("scrollPercentage");
    scroller.style.width = scrollPercentage + '%'
}