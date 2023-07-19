let formid = 0;
function selectForm(id){
    document.getElementById('nocn_select_formid_error').style.display = 'none';
    document.getElementById('nocn_error_text').style.display = 'none';
    formid = id;
    const params = `id=${id}`;
    const xhr = new XMLHttpRequest();
    xhr.open('POST','./classes/inc/nocn_learner_form.inc.php',true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.onload = function(){
        if(this.status == 200){
            let text = JSON.parse(this.responseText);
            const errorText = document.getElementById('nocn_select_formid_error');
            if(text['error'] == true){
                errorText.innerText = 'Error Loading NOCN Form';
                errorText.style.display = 'block';
            } else if(text['error'] == false){
                errorText.style.display = 'none';
                if(text['array']){
                    text = text['array'];
                    const formArray = [
                        ['learnername','learnerName'],
                        ['qualification'],
                        ['unitnumber','unitNumber'],
                        ['level'],
                        ['assessorname','tutorAssessor'],
                        ['date'],
                        ['feedbackassessor','feedbackAssessor'],
                        ['commentlearner','learnerComments'],
                        ['learnersignaturedate','learnerSignatureDate'],
                        ['assessorsignaturedate','tutorAssessorSignatureDate'],
                        ['iqasignaturedate','iqaSignatureDate']
                    ];
                    formArray.forEach(function(item){
                        if(text[item[0]]){
                            if(item.length > 1){
                                document.getElementById(item[1]).value = text[item[0]];
                                document.getElementById(item[1]).style = '';
                            } else {
                                document.getElementById(item[0]).value = text[item[0]];
                                document.getElementById(item[0]).style = '';
                            }
                        } else {
                            if(item.length > 1){
                                if(item[1] == 'learnerSignatureDate'){
                                    resetMinMax('learnerSignatureDate')
                                }
                                document.getElementById(item[1]).value = "";
                                document.getElementById(item[1]).style = '';
                            } else {
                                document.getElementById(item[0]).value = "";
                                document.getElementById(item[0]).style = '';
                            }
                        }
                    });
                    if(text['metcriteria']){
                        if(text['metcriteria'] == 0){
                            document.getElementById('assessmentCriteria').value = 'no';
                        } else if(text['metcriteria'] == 1){
                            document.getElementById('assessmentCriteria').value = 'yes';
                        }
                        document.getElementById('assessmentCriteria').style = '';
                    }
                    const checkboxArray = [
                        ['learnersignature', 'learnerSignature'],
                        ['assessorsignature', 'tutorAssessorSignature'],
                        ['iqasignature', 'iqaSignature']
                    ]
                    checkboxArray.forEach(function(item){
                        document.getElementById(item[1]).checked = (text[item[0]]) ? true : false;
                        document.getElementById(item[1]).style = '';
                    });
                    document.getElementById('nocn_form').style.display = 'block';
                    document.getElementById('nocn_form_title').innerText = text['title'];
                }
            }
        }
    }
    xhr.send(params);
}
document.getElementById('nocn_form').addEventListener('submit', function(e){
    e.preventDefault();
    const name = document.getElementById('learnerName').value;
    const comment = document.getElementById('learnerComments').value;
    const signed = document.getElementById('learnerSignature').checked;
    const signedD = document.getElementById('learnerSignatureDate').value;
    const params = `name=${name}&comment=${comment}&signed=${signed}&signedD=${signedD}&formid=${formid}`;
    const xhr = new XMLHttpRequest();
    xhr.open('POST','./classes/inc/nocn_learner.inc.php',true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function(){
        if(this.status == 200){
            let text = JSON.parse(this.responseText);
            const errorText = document.getElementById('nocn_error_text');
            const checkArray = [
                ['name','Learner Name','learnerName'],
                ['comment','Learner Comment','learnerComments'],
                ['signed','Learner Signature','learnerSignature'],
                ['signedD','Learner Signature Date','learnerSignatureDate'],
                ['formid','Form ID']
            ];
            if(text['error'] == true){
                let invalid = 'Invalid:\n';
                checkArray.forEach(function(item){
                    if(text[item[0]]){
                        if(item.length > 2){
                            invalid += item[1]+":"+text[item[0]]+"\n";
                            document.getElementById(item[2]).style = 'border: 2px solid red;';
                        } else {
                            invalid += item[1]+":"+text[item[0]]+"\n";
                        }
                    }
                });
                if(text['errorType']){
                    invalid += text['errorType'] + '\n';
                }
                errorText.innerText = invalid;
                errorText.style.display = 'block';
            } else if(text['error'] == false){
                location.reload();
            }
        }
    }
    xhr.send(params);
});
function clickedSign(date){
    if(date != ''){
        const tempDate = new Date(Date.now());
        if(document.getElementById(date).checked){
            document.getElementById(date+"Date").value = tempDate.getFullYear()+"-"+("0"+(tempDate.getMonth()+1)).slice(-2)+"-"+("0"+tempDate.getDate()).slice(-2);
        } else {
            document.getElementById(date+"Date").value = "";
            resetMinMax(date+"Date");
        }
    }
}
function resetMinMax(id){
    let tempDate = new Date(Date.now());
    document.getElementById(id).setAttribute('min', tempDate.getFullYear()+"-"+("0"+(tempDate.getMonth()+1)).slice(-2)+"-"+("0"+tempDate.getDate()).slice(-2));
    document.getElementById(id).setAttribute('max', tempDate.getFullYear()+"-"+("0"+(tempDate.getMonth()+1)).slice(-2)+"-"+("0"+tempDate.getDate()).slice(-2));
}