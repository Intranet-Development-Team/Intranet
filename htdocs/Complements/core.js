if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)
{
    $('html').attr("data-bs-theme", "dark");
}

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event =>
{
    if (event.matches)
    {
        $('html').attr("data-bs-theme", "dark");
    }
    else
    {
        $('html').attr("data-bs-theme", "light");
    }
});

console.log('%cWARNING', 'font-size:5em;color:red;');
console.log('%cJavaScript console is a feature designed for web development, not for the so-called "hacking". Do not paste any code that you don\'t understand here. Self-XSS attacks can be carried out. Your personal information may be exposed.', 'font-size:2em;');
console.log("%cLearn more on: https://en.wikipedia.org/wiki/Self-XSS", "font-size:1.2em;");

function confirmModal(title, message, successfunction, btncancel = "Cancel", btncontinue = "Continue")
{
    let element = $('<div class="modal fade"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h1 class="modal-title fs-5">' + title + '</h1></div><div class="modal-body">' + message + '</div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">' + btncancel + '</button><button class="btn btn-danger" data-bs-dismiss="modal" onclick="' + successfunction + '">' + btncontinue + '</button></div></div></div></div></div>');

    let modal = new bootstrap.Modal(element);
    modal.show();
}

function alertModal(title, message, afterfunction, btn = "Ok")
{
    let element = $('<div class="modal fade"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h1 class="modal-title fs-5">' + title + '</h1></div><div class="modal-body">' + message + '</div><div class="modal-footer"><button class="btn btn-primary" data-bs-dismiss="modal" onclick="' + afterfunction + '">' + btn + '</button></div></div></div></div></div>');

    let modal = new bootstrap.Modal(element);
    modal.show();
}

function preventMisclick(ele)
{
    ele.css('pointer-events', 'none');
    ele.html('<div class="spinner-border" role="status"style="width:.95em;height:.95em;"></div> Loading');
    setTimeout(function ()
    {
        ele.css('pointer-events', 'auto');
        ele.html("Resubmit");
    }, 10000);
}