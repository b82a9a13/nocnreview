document.getElementById('reset_form').addEventListener('submit', (e) => {
    e.preventDefault();
    const user = document.getElementById('reset_user').value;
    const errorText = document.getElementById('reset_form_error');
    errorText.style.display = 'none';
    errorText.innerText = '';
    errorText.className = 'text-error';
    if(user != ""){
        const params = `id=${user}`;
        const xhr = new XMLHttpRequest();
        xhr.open('POST','./classes/inc/nocn_admin_reset_signature.inc.php',true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function(){
            if(this.status == 200){
                const text = JSON.parse(this.responseText);
                if(text['error'] == false){
                    if(text['result'] == false){
                        errorText.innerText = 'Error: Deletion Failed';
                        errorText.style.display = 'block';
                    } else if(text['result'] == true){
                        errorText.innerHTML = 'Success';
                        errorText.className = 'text-success';
                        errorText.style.display = 'block';
                        let innerHTML = '<option selected disabled value="">Select a user</option>';
                        if(text['list'].length > 0){
                            for(let i = 0; i < text['list'].length; i++){
                                innerHTML += '<option value="'+text['list'][i][0]+'">'+text['list'][i][1]+'</option>';
                            }
                        }
                        document.getElementById('reset_user').innerHTML = innerHTML;
                    }
                } else if(text['error'] == true){
                    errorText.innerText = 'Error: Deletion Failed';
                    errorText.style.display = 'block';
                }
            } else {
                errorText.innerText = 'Error: Connection Error';
                errorText.style.display = 'block';
            }
        }
        xhr.send(params);
    } else {
        errorText.innerText = 'Error: Select a user';
        errorText.style.display = 'block';
    }
});

document.getElementById('incomplete_form').addEventListener('submit', (e)=>{
    e.preventDefault();
    const user = document.getElementById('incomplete_user').value;
    const errorText = document.getElementById('incomplete_form_error');
    errorText.style.display = 'none';
    errorText.innerText = '';
    errorText.className = 'text-error';
    if(user != ""){
        const params = `id=${user}`;
        const xhr = new XMLHttpRequest();
        xhr.open('POST', './classes/inc/nocn_admin_incomplete.inc.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function(){
            if(this.status == 200){
                const text = JSON.parse(this.responseText);
                if(text['error'] == false){
                    if(text['result'] == false){
                        errorText.innerText = 'Error: No Results';
                        errorText.style.display = 'block';
                    } else if(text['result'] == true){
                        errorText.innerHTML = 'Success';
                        errorText.className = 'text-success';
                        errorText.style.display = 'block';
                        const div = document.getElementById('incomplete_div');
                        let innerHTML = '<div style="width: 100%;"class="nocn-border-inner"><h1 class="text-center">Incomplete Forms<span class="nocn-c-pointer" style="float:right;" onclick="close_div(`incomplete`)">X</span></h1>';
                        let formHTML = '<div class="nocn-border">';
                        let btnHTML = '<div class="nocn-border text-center mb-1">';
                        for(let i = 0; i < text['list'].length; i++){
                            btnHTML += '<button class="btn-primary btn mr-1" onclick="display_incomplete_form(`'+i+'`)">'+text['list'][i][21]+'</button>';
                            formHTML += (i == 0) ? '<div style="display: block;" id="incomplete_'+i+'">' : '<div style="display: none;" id="incomplete_'+i+'">';
                            formHTML += '<h1>'+text['list'][i][21]+'</h1>';
                            formHTML += '<img src="./classes/img/nocn.png"><div><b>Learner Name: </b><a href="window.location.href=./../../../user/profile.php?id='+text['list'][i][12]+'">'+text['list'][i][13]+'</a></div><br><div><b>Qualification: </b>'+text['list'][i][19]+'</div><br><div style="display:flex;"><div style="width:50%;" class="mr-1"><b>Unit Number: </b>'+text['list'][i][20]+'</div><div style="width:50%;"><b>Level: </b>'+text['list'][i][16]+'</div></div><br><div style="display: flex;"><div style="width: 50%;" class="mr-1"><b>Tutor/Assessor: </b><a href="window.location.href=./../../../user/profile.php?id='+text['list'][i][0]+'">'+text['list'][i][1]+'</a></div><div style="width: 50%;"><b>Date: </b>'+text['list'][i][6]+'</div></div><br><div><b>Feedback from Assessor to Learner: </b>'+text['list'][i][7]+'</b></div><br>';
                            formHTML += (text['list'][i][4] == null) ? '<div><b>Comments from Learner: </b></div><br>' : '<div><b>Comments from Learner: </b>'+text['list'][i][4]+'</div><br>';
                            formHTML += (text['list'][i][17] == 0) ? '<div><b>Have all assessment criteria for the unit been met?</b> No</div><br>' : '<div><b>Have all assessment criteria for the unit been met?</b> Yes</div><br>';
                            formHTML += (text['list'][i][14] == null) ? '<div style="display: flex;"><div style="width:50%;" class="mr-1"><b>Learner Signature: </b></div>' : '<div style="display: flex;"><div style="width:50%;" class="mr-1"><b>Learner Signature: </b>&#x2713;</div>';
                            formHTML += (text['list'][i][15] == null) ? '<div style="width: 50%;"><b>Date: </b></div></div><br>' : '<div style="width: 50%;"><b>Date: </b>'+text['list'][i][15]+'</div></div><br>';
                            formHTML += (text['list'][i][2] == null) ? '<div style="display: flex;"><div style="width:50%;" class="mr-1"><b>Tutor/Assessor Signature: </b></div>' : '<div style="display: flex;"><div style="width:50%;" class="mr-1"><b>Tutor/Assessor Signature: </b>&#x2713;</div>';
                            formHTML += (text['list'][i][3] == null) ? '<div style="width: 50%;"><b>Date: </b></div></div><br>' : '<div style="width: 50%;"><b>Date: </b>'+text['list'][i][3]+'</div></div><br>';
                            formHTML += (text['list'][i][10] == null) ? '<div style="display: flex;"><div style="width:50%;" class="mr-1"><b>IQA Signature (if sampled): </b></div>' : '<div style="display: flex;"><div style="width:50%;" class="mr-1"><b>IQA Signature (if sampled): </b>&#x2713;</div>';
                            formHTML += (text['list'][i][11] == null) ? '<div style="width: 50%;"><b>Date: </b></div></div><br>' : '<div style="width: 50%;"><b>Date: </b>'+text['list'][i][11]+'</div></div><br>';
                            formHTML += '</div><br>';
                        }
                        btnHTML += '</div>';
                        formHTML += '</div>';
                        innerHTML += btnHTML + formHTML;
                        innerHTML += '</div>';
                        div.innerHTML = innerHTML;
                        div.style.display = 'block';
                    }
                } else if(text['error'] == true){
                    errorText.innerText = 'Error: Loading Failed';
                    errorText.style.display = 'block';
                }
            } else {
                errorText.innerText = 'Error: Connection Error';
                errorText.style.display = 'block';
            }
        }
        xhr.send(params);
    } else {
        errorText.innerText = 'Error: Select a user';
        errorText.style.display = 'block';
    }
});
function display_incomplete_form(id){
    const form = document.getElementById('incomplete_'+id);
    form.style.display = (form.style.display == 'none') ? 'block' : 'none';
}
function close_div(name){
    document.getElementById(`${name}_div`).style.display = `none`;
    document.getElementById(`${name}_form_error`).style.display = `none`;
}

document.getElementById('history_form').addEventListener('submit', (e)=>{
    e.preventDefault();
    document.getElementById('history_content').style.display = 'none';
    const user = document.getElementById('history_user').value;
    const errorText = document.getElementById('history_form_error');
    errorText.style.display = 'none';
    errorText.innerText = '';
    errorText.className = 'text-error';
    if(user != ""){
        const params = `id=${user}`;
        const xhr = new XMLHttpRequest();
        xhr.open('POST', './classes/inc/nocn_admin_history.inc.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function(){
            if(this.status == 200){
                const text = JSON.parse(this.responseText);
                if(text['error'] == false){
                    if(text['result'] == false){
                        errorText.innerText = 'Error: No Results';
                        errorText.style.display = 'block';
                    } else if(text['result'] == true){
                        errorText.innerHTML = 'Success';
                        errorText.className = 'text-success';
                        errorText.style.display = 'block';
                        const div = document.getElementById('history_div');
                        let innerHTML = '';
                        for(let i = 0; i < text['list'].length; i++){
                            innerHTML += '<button class="btn btn-primary mb-1 mr-1" onclick="get_review_history('+text['list'][i][0]+', 9999)">'+text['list'][i][1]+'</button>';
                        }
                        document.getElementById('history_btns').innerHTML = innerHTML;
                        div.style.display = 'block';
                    }
                } else if(text['error'] == true){
                    errorText.innerText = 'Error: Loading Failed';
                    errorText.style.display = 'block';
                }
            } else {
                errorText.innerText = 'Error: Connection Error';
                errorText.style.display = 'block';
            }
        }
        xhr.send(params);
    } else {
        errorText.innerText = 'Error: Select a user';
        errorText.style.display = 'block';
    }
});
let currentID = null;
let currentPos = null;
let currentData = [];
function get_review_history(id, pos){
    const params = `id=${id}`;
    const xhr = new XMLHttpRequest();
    const errorText = document.getElementById('history_div_error');
    errorText.style.display = 'none';
    errorText.innerText = '';
    errorText.className = 'text-error';
    clear_history_form();
    if(currentID == id){
        update_history_form(currentData, currentID, pos);
        currentPos = pos;
    } else {
        xhr.open('POST', './classes/inc/nocn_admin_history_form.inc.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function(){
            if(this.status == 200){
                const text = JSON.parse(this.responseText);
                if(text['error'] == false){
                    if(text['result'] == false){
                        errorText.innerText = 'Error: No Results';
                        errorText.style.display = 'block';
                    } else if(text['result'] == true){
                        errorText.innerHTML = 'Success';
                        errorText.className = 'text-success';
                        errorText.style.display = 'block';
                        currentID = id;
                        currentPos = pos;
                        currentData = text['list'];
                        update_history_form(text['list'], id, pos);
                    }
                } else if(text['error'] == true){
                    errorText.innerText = 'Error: Loading Failed';
                    errorText.style.display = 'block';
                }
            } else {
                errorText.innerText = 'Error: Connection Error';
                errorText.style.display = 'block';
            }
        }
        xhr.send(params);
    }
}
function update_history_form(list, id, pos){
    let btn = '';
    for(let i = 1; i < list.length+1; i++){
        let tempDate = new Date(list[i-1][0]*1000);
        btn += '<button class="btn-primary btn mb-1 mr-1" onclick="get_review_history('+id+','+i+')">'+("0"+tempDate.getDate()).slice(-2)+"/"+("0"+(tempDate.getMonth()+1)).slice(-2)+"/"+tempDate.getFullYear()+', '+("0"+(tempDate.getHours()+1)).slice(-2)+':'+("0"+(tempDate.getMinutes()+1)).slice(-2)+':'+("0"+(tempDate.getSeconds()+1)).slice(-2)+'</button>';
    }
    for(let i = 0; i < list.length && i < pos; i++){
        let tempDate = new Date(list[i][0]*1000);
        document.getElementById('history_content_title').innerText = list[i][7]+' ('+("0"+tempDate.getDate()).slice(-2)+"/"+("0"+(tempDate.getMonth()+1)).slice(-2)+"/"+tempDate.getFullYear()+', '+("0"+(tempDate.getHours()+1)).slice(-2)+':'+("0"+(tempDate.getMinutes()+1)).slice(-2)+':'+("0"+(tempDate.getSeconds()+1)).slice(-2)+')';
        const json = JSON.parse(list[i][4]);
        document.getElementById('history_lname').innerText = (json['learnername']) ? json['learnername'] : document.getElementById('history_lname').innerText;
        document.getElementById('history_lname').href = (json['learnerid']) ? './../../user/profile.php?id='+json['learnerid'] : document.getElementById('history_lname').href;
        document.getElementById('history_qual').innerText = (json['qualification']) ? json['qualification'] : document.getElementById('history_qual').innerText;
        document.getElementById('history_unitnum').innerText = (json['unitnumber']) ? json['unitnumber'] : document.getElementById('history_unitnum').innerText;
        document.getElementById('history_level').innerText = (json['level']) ? json['level'] : document.getElementById('history_level').innerText;
        document.getElementById('history_tutorassessor').innerText = (json['assessorname']) ? json['assessorname'] : document.getElementById('history_tutorassessor').innerText;
        document.getElementById('history_tutorassessor').href = (json['assessorid']) ? './../../user/profile.php?id='+json['assessorid'] : document.getElementById('history_tutorassessor').href;
        tempDate = new Date(json['date']*1000);
        document.getElementById('history_date').innerText = (json['date']) ? ("0"+tempDate.getDate()).slice(-2)+"/"+("0"+(tempDate.getMonth()+1)).slice(-2)+"/"+tempDate.getFullYear() : document.getElementById('history_date').innerText;
        document.getElementById('history_feedatol').innerText = (json['feedbackassessor']) ? json['feedbackassessor'] : document.getElementById('history_feedatol').innerText;
        document.getElementById('history_comlearn').innerText = (json['commentlearner']) ? json['commentlearner'] : document.getElementById('history_comlearn').innerText;
        const metcriteria = (json['metcriteria']) ? 'Yes' : 'No';
        document.getElementById('history_criteria').innerText = (json['metcriteria']) ? metcriteria : document.getElementById('history_criteria').innerText;
        let tempSign = (json['learnersignature']) ? '&#x2713;' : '';
        document.getElementById('history_learnsign').innerHTML = (json['learnersignature']) ? tempSign : document.getElementById('history_learnsign').innerText;
        tempDate = new Date(json['learnersignaturedate'] * 1000);
        document.getElementById('history_learnsign_d').innerText = (json['learnersignaturedate']) ? ("0"+tempDate.getDate()).slice(-2)+"/"+("0"+(tempDate.getMonth()+1)).slice(-2)+"/"+tempDate.getFullYear() : document.getElementById('history_learnsign_d').innerText;
        tempSign = (json['assessorsignature']) ? '&#x2713;' : '';
        document.getElementById('history_tutorasessorsign').innerHTML = (json['assessorsignature']) ? tempSign : document.getElementById('history_tutorasessorsign').innerText;
        tempDate = new Date(json['assessorsignaturedate'] * 1000);
        document.getElementById('history_tutorasessorsign_d').innerText = (json['assessorsignaturedate']) ? ("0"+tempDate.getDate()).slice(-2)+"/"+("0"+(tempDate.getMonth()+1)).slice(-2)+"/"+tempDate.getFullYear() : document.getElementById('history_tutorasessorsign_d').innerText;
        tempSign = (json['iqasignature']) ? '&#x2713;' : '';
        document.getElementById('history_iqasign').innerHTML = (json['iqasignature']) ? tempSign : document.getElementById('history_iqasign').innerHTML;
        tempDate = new Date(json['iqasignaturedate'] * 1000);
        document.getElementById('history_iqasign_d').innerText = (json['iqasignaturedate']) ? ("0"+tempDate.getDate()).slice(-2)+"/"+("0"+(tempDate.getMonth()+1)).slice(-2)+"/"+tempDate.getFullYear() : document.getElementById('history_iqasign_d').innerText;
        document.getElementById('history_src_ip').innerText = (list[i][8]) ? list[i][8] : '';
        document.getElementById('history_src_browser').innerText = (list[i][3]) ? list[i][3] : '';
        document.getElementById('history_src_uid').innerText = (list[i][5]) ? list[i][5] : '';
        document.getElementById('history_src_fullname').innerText = (list[i][9]) ? list[i][9] : '';
    }
    document.getElementById('history_content').style.display = 'block';
    document.getElementById('history_content_btns').innerHTML = btn;
}
function clear_history_form(){
    document.getElementById('history_lname').innerText = "";
    document.getElementById('history_lname').href = "";
    document.getElementById('history_qual').innerText = "";
    document.getElementById('history_unitnum').innerText = "";
    document.getElementById('history_level').innerText = "";
    document.getElementById('history_tutorassessor').innerText = "";
    document.getElementById('history_tutorassessor').href = "";
    document.getElementById('history_date').innerText = "";
    document.getElementById('history_feedatol').innerText = "";
    document.getElementById('history_comlearn').innerText = "";
    document.getElementById('history_criteria').innerText = "";
    document.getElementById('history_learnsign').innerHTML = "";
    document.getElementById('history_learnsign_d').innerText = "";
    document.getElementById('history_tutorasessorsign').innerHTML = "";
    document.getElementById('history_tutorasessorsign_d').innerText = "";
    document.getElementById('history_iqasign').innerHTML = "";
    document.getElementById('history_iqasign_d').innerText = "";
    document.getElementById('history_src_ip').innerText = "";
    document.getElementById('history_src_browser').innerText = "";
    document.getElementById('history_src_uid').innerText = "";
    document.getElementById('history_src_fullname').innerText = "";
}