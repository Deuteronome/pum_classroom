const reader = new FileReader();
const avatar = document.querySelector('#avatar-preview');
let fileInput = document.querySelector('#user_avatar');
if(!fileInput) 
{
  fileInput = document.querySelector('#user_update_avatar');
}

reader.onload = e => {
    avatar.src = e.target.result;
  }

fileInput.addEventListener('change', e => {
    const f = e.target.files[0];
    reader.readAsDataURL(f);
  })