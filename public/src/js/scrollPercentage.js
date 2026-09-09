document.addEventListener('scroll', function () {
    var scroll = getScrollPercentage();
    setWidthPercentage(scroll);
});

function getScrollPercentage() {
    var doc = document.documentElement;
    var body = document.body;
    var top = 'scrollTop';
    var height = 'scrollHeight';

    var percent = (doc[top] || body[top]) / ((doc[height] || body[height]) - doc.clientHeight) * 100;

    return percent;
}

function setWidthPercentage(scrollPercentage) {
    var scroller = document.getElementById("scrollPercentage");
    scroller.style.width = scrollPercentage + '%'
}