let header = document.querySelector('header');
let animZone = document.querySelector('.circles');
let avatar = document.querySelector('.avatar-box');

animZone.style.height = header.offsetHeight + 'px';
if(avatar){
    avatar.style.height = header.offsetHeight*0.75 + 'px';
    avatar.style.top = header.offsetHeight*0.125 + 'px';
    avatar.style.right = header.offsetHeight*0.125 + 'px';
}
