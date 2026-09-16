function setScrollBehavior(anchor, type) {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();

        window.location.hash = this.getAttribute('href');
        DoScrollIntoView(document.getElementById(this.getAttribute('href').replace("#", "")), false, type);
    });
}

var onceIsDone = false;
function autoScroll(once, type) {
    var hash = decodeURIComponent(window.location.hash);

    if(hash === ""){ return; }

    var element = document.getElementById(hash.replace("#", ""));
    openParentSummaryIfFound(element, once);
    DoScrollIntoView(element, once, type);
}

var pageTitle = document.title;
function DoScrollIntoView(element, once, type) {
    if (element !== null) {
        if (once === true && onceIsDone === true) {
            return;
        }
        else {
            element.scrollIntoView({ behavior: "smooth", block: type });
            element.focus(); // no browser good support yet : // element.focus({ focusVisible: true });
            onceIsDone = true;
        }
        console.log("Scrolling to : " + element.id);
        document.title = pageTitle + " # " + element.parentElement.textContent.replaceAll("#", "");
    }
}

function openParentSummaryIfFound(element, once) {
    if (element !== null) {
        if (once === true && onceIsDone === true) {
            return;
        }
        let parentElements = [];
        let currentElement = element;

        while (currentElement !== document) {
            parentElements.push(currentElement.parentNode);
            currentElement = currentElement.parentNode;
        }

        for (parent in parentElements) {
            if (parentElements[parent].nodeName === 'DETAILS') {
                parentElements[parent].open = true;
            }
        }

        makeElementMoreVisible(element);
    }
}

function makeElementMoreVisible(element) {
    var newNode = element.parentNode;
    if(element.id === "header" || element.id === "footer"){
        return;
    }

    newNode.animate(
        [
        /*
            { transform: "translate3d(0, 0, 0)"},
            { transform: "translate3d(0, -10px, 0)"},
            { transform: "translate3d(0, 0, 0)"},
            { transform: "translate3d(0, 10px, 0)"},
            { transform: "translate3d(0, 0, 0)"},
        */

            {
                opacity: "0",
                transform: "rotateX(90deg)",
            },
            {
                opacity: "0.80",
                transform: "rotateX(720deg)",
            },
            {
                opacity: "1",
                transform: "rotateX(720deg)"
            }
        ],
        {
            duration: 2000,
            delay: 500,
            easing: 'ease',
            iterations: 1,
        }
    );
}