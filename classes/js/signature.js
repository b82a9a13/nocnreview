const canvas = document.getElementById('sign_canvas');
const ctx = canvas.getContext('2d');
ctx.lineWidth = 3;
ctx.lineJoin = ctx.lineCap = 'round';
ctx.fillStyle = 'white';
ctx.fillRect(0,0,canvas.width,canvas.height);
let signed = false;
let writingMode = false;
let writing = false;
document.getElementById('nocn_sign_form').addEventListener('submit', function(e){
    e.preventDefault();
    if(signed){
        changeText('sign_error_text','');
        const data = document.getElementById('sign_canvas').toDataURL("image/jpeg");
        const params = `data=${data}`;
        const xhr = new XMLHttpRequest();
        xhr.open('POST','./classes/inc/signature.inc.php',true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function(){
            if(this.status == 200){
                const text = JSON.parse(this.responseText);
                if(text['error']){
                    changeText('sign_error_text','Submit Failed');
                } else {
                    changeText('sign_success_text','Success');
                    location.reload();
                }
            } else{
                changeText('sign_error_text','No connection');
            }
        }
        xhr.send(params);
    } else{
        changeText('sign_error_text','Please create a signature');
    }
});
function changeText(id,text){
    document.getElementById(id).innerText = text;
}
document.getElementById('sign_clear').addEventListener('click', ()=>{
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    signed = false;
});
const getPosition = (e)=>{
    return [(e.clientX - e.target.getBoundingClientRect().x), (e.clientY - e.target.getBoundingClientRect().y)];
};
canvas.addEventListener('pointerdown', (e)=>{
    writingMode = true;
    ctx.beginPath();
    const [x,y] = getPosition(e);
    ctx.moveTo(x,y);
    writing = true;
})
canvas.addEventListener('pointerup', ()=>{
    writingMode = false;
    signed = true;
    writing = false;
})
canvas.addEventListener('pointermove', (e)=>{
    if(writingMode){
        const [x,y] = getPosition(e);
        ctx.lineTo(x,y);
        ctx.stroke();
    }
})
canvas.addEventListener('mouseout', ()=>{
    writingMode = false;
    if(writing){
        signed = true;
        writing = false;
    }
})