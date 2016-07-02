require('animate.css');
require('./favicon.ico');
require('./style.scss');

const id = document.getElementById.bind(document);
const EMAIL_FORM    = id('email-form');
const EMAIL_INPUT   = id('email');
const EMAIL_SUCCESS = id('email-success');
const EMAIL_ERROR   = id('email-error');
const CHANNELS_LIST = id('channels-list');

EMAIL_FORM.addEventListener('submit', event => {
    event.preventDefault();

    fetch('/get-invite', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: EMAIL_INPUT.value })
    }).then(res => res.json()).then(result => {
        if(result.error) {
            EMAIL_SUCCESS.style.display = 'none';
            EMAIL_ERROR.innerHTML = result.error;
            EMAIL_ERROR.style.display = 'block';
        } else {
            EMAIL_ERROR.style.display = 'none';
            EMAIL_SUCCESS.style.display = 'block';
            EMAIL_INPUT.value = '';
        }
    });
}, true);

fetch('/team-info').then(res => res.json()).then(info => {
    for(let channel of info.channels) {
        let li = document.createElement('li');
        let txt = document.createTextNode(channel.name);
        li.appendChild(txt);
        li.setAttribute('title', `${channel.membersCount} membres. ${channel.purpose}`);

        CHANNELS_LIST.appendChild(li);
    }

    CHANNELS_LIST.style.display = 'block';
});