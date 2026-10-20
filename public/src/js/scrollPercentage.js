console.log("scrollPercentage.js loaded");

document.addEventListener('scroll', function () {
    let scroll = getScrollPercentage();
    setWidthPercentage(scroll);
});

function getScrollPercentage() {
    let bodyheight = document.body.clientHeight;

    let docheight = document.documentElement.clientHeight;

    let height;
    if(bodyheight < docheight){
        height = bodyheight;
    }
    else if(docheight < bodyheight){
        height = docheight;
    }

    let bodytop = document.body.scrollTop;

    let doctop = document.documentElement.scrollTop;

    let top;
    if((doctop != 0 || bodytop != 0) && doctop != bodytop){
        if(bodytop != 0){
            top = bodytop;
        }
        else if(doctop != 0){
            top = doctop;
        }
    }else if((doctop != 0 && bodytop != 0) && bodytop === doctop){
        top = doctop;
    }

    let bodyMaxHeight = document.body.scrollHeight;

    let docMaxHeight = document.documentElement.scrollHeight;

    let maxHeight;
    if(bodyMaxHeight === docMaxHeight){
        maxHeight = bodyMaxHeight;
    }
    else{
        if(bodyMaxHeight < docMaxHeight){
            maxHeight = bodyMaxHeight;
        }
        else if(docMaxHeight < bodyMaxHeight){
            maxHeight = docMaxHeight;
        }
    }

    let scrollPercent = (top / (maxHeight - height)) * 100;

    if(Number.isNaN(scrollPercent)) { scrollPercent = 0; }

    return scrollPercent;
}

function setWidthPercentage(scrollPercentage) {
    let scroller = document.getElementById("scrollPercentage");
    scroller.style.width = scrollPercentage + '%'
}