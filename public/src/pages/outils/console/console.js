var input = `Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean non nisi id purus volutpat malesuada. Pellentesque vitae leo vel eros iaculis finibus. Fusce tincidunt metus eget dictum feugiat. Donec sit amet dolor condimentum, bibendum lorem vel, iaculis odio. Quisque ut lacinia velit. Etiam nec nulla nec purus elementum convallis vitae eu mauris. Suspendisse non malesuada elit. Nulla nibh nisi, faucibus a odio ut, eleifend fringilla magna. Curabitur quis mi ac magna iaculis ultrices. Sed a felis rhoncus, lacinia nibh ut, condimentum est. Duis finibus tincidunt nisl, vel dapibus enim blandit ac. Donec magna urna, eleifend at dolor eget, lacinia tincidunt odio. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque eget metus tristique, dapibus quam et, molestie velit. Proin finibus neque sed purus suscipit, ac sagittis risus malesuada. Aliquam at placerat tortor.

Phasellus cursus tincidunt enim, id feugiat nunc dictum nec. Nam porttitor pharetra fermentum. Donec tincidunt orci leo, vestibulum ornare neque vestibulum tempus. Aenean tempor dapibus purus quis ornare. Aenean leo ligula, rutrum ac ligula sit amet, maximus facilisis dui. In at risus mattis, aliquet justo at, efficitur arcu. Suspendisse eu interdum justo, vitae viverra purus. Duis quam sapien, efficitur in mauris non, accumsan consectetur quam. Quisque non auctor eros, vitae sodales metus.

Nulla porttitor elementum egestas. Phasellus blandit mollis congue. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Mauris luctus sodales risus eu rhoncus. Nam consectetur tellus vel accumsan eleifend. Duis vestibulum massa at eros venenatis pulvinar. Ut sagittis massa massa, sit amet porttitor augue pharetra id. Integer leo mauris, interdum sit amet accumsan eu, elementum sit amet lectus.

Donec sollicitudin ut metus sit amet lacinia. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Donec bibendum diam imperdiet nisl rhoncus dictum. Cras malesuada non leo quis ultrices. Cras at pellentesque diam. Morbi lobortis orci sit amet iaculis consectetur. Curabitur luctus vestibulum sem in egestas. Sed a augue quis ex maximus lobortis. Sed luctus lacinia lobortis. Nulla at ipsum hendrerit, congue nisi quis, interdum erat. Etiam ut ex vulputate quam pellentesque congue. Donec velit sapien, scelerisque vel consequat ut, euismod id ligula.

Pellentesque tellus nisl, gravida venenatis consectetur sed, lacinia vitae dolor. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Morbi a sodales tellus, a laoreet leo. Proin sed iaculis metus, non suscipit libero. Cras ac eros at turpis ultricies mattis. Maecenas elementum laoreet dolor a porttitor. Sed in augue ac magna gravida fermentum. Vestibulum tempus dui in eros eleifend ultrices. Nullam fringilla erat massa, ac efficitur ipsum vehicula quis. Nulla at ante eget velit aliquam tristique eget non augue. Vivamus laoreet eleifend iaculis.

In nec neque libero. Aliquam non elementum dolor. Phasellus dictum pellentesque dolor at viverra. Sed eget dapibus nunc. Nullam a euismod turpis. Pellentesque et urna nec orci pulvinar pharetra quis eget purus. Suspendisse potenti. Suspendisse quis ante a tellus porttitor consequat. Donec ac nisl nec elit gravida tempus. Aliquam eu tempor metus, ut semper dui.

Proin elit quam, volutpat at ultricies et, dignissim non orci. Aliquam erat volutpat. Ut sit amet eros velit. Etiam fringilla diam eget sem semper tempus. Ut dolor quam, tincidunt suscipit tortor vitae, feugiat molestie ipsum. Integer lobortis pharetra ex eu placerat. Mauris nec ullamcorper odio, ullamcorper congue purus. In accumsan quam vitae eleifend efficitur. Mauris luctus nibh sapien, at sodales arcu lacinia quis. Donec maximus in velit vel pellentesque. Suspendisse placerat, nunc id consectetur congue, nibh dui commodo eros, maximus euismod purus lorem id justo.

Etiam posuere massa pellentesque faucibus commodo. Donec nec posuere libero. Nulla nec tempor odio. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Integer tempus mi risus, in dictum ipsum efficitur eu. Donec eleifend neque vel ligula bibendum mollis. Aenean molestie, arcu placerat imperdiet euismod, metus felis cursus ligula, non tincidunt metus turpis nec purus. Curabitur lacinia venenatis commodo.

Pellentesque eu condimentum nunc. Ut non nulla aliquet, condimentum ipsum sit amet, laoreet metus. Mauris et dictum felis. Fusce nec iaculis diam. Vestibulum in molestie justo. Nulla facilisi. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Vestibulum lacinia iaculis dictum. Praesent varius libero purus, vel vestibulum orci blandit eget. Praesent metus turpis, mollis sed eros vel, faucibus finibus erat. Aenean sed eros lorem. Praesent nec ex luctus, rutrum eros sit amet, viverra leo. Vivamus maximus, nulla quis tincidunt consequat, magna tortor lacinia metus, sit amet lacinia ante enim eget metus.

Etiam ac quam nibh. Aliquam commodo suscipit augue, et venenatis ligula. Nullam bibendum in tortor sit amet condimentum. Integer sed facilisis quam, vitae consequat ipsum. Nunc non ipsum dui. Vestibulum blandit turpis cursus, elementum tortor non, pharetra turpis. Nullam ultricies venenatis odio quis aliquam. Quisque erat nisi, porttitor faucibus sapien a, semper posuere orci. Vivamus in mi sed mi elementum vestibulum. Nam sollicitudin congue pulvinar. Maecenas vel ex ante. Aenean rhoncus diam vel dui interdum, vitae convallis quam lobortis. Sed quis nibh et eros fermentum ultrices. Maecenas nec ullamcorper leo.

\n\n\n\n\n
xoxoxo
- the admin`;

addText(input);

async function addText(text) {
    console.log("Loading console text adder");
    var console_text = document.getElementById("console-body-text");
    for (char in text) {
        console_text.innerHTML += text[char];

        if (text[char] == " ") {
            await sleep(getRandomInt(150));
        }
        else if (text[char] == ".") {
            await breakLine();
            await sleep(getRandomInt(500));
        }
        else if (text[char] == `\n`)
        {
            await breakLine();
        }
        else {
            await sleep(getRandomInt(10));
        }
    }
} 

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

function getRandomInt(max) {
    return Math.floor(Math.random() * max);
}

async function breakLine() {
    var console_text = document.getElementById("console-body-text");
    console_text.innerHTML += "<br>";
}