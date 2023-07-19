document.getElementById('nocn_create_new_form').addEventListener('click', function(){
    document.getElementById('nocn_form_menu_error').style.display = 'none';
    if(document.getElementById('nocn_form').style.display == 'block'){
        document.getElementById('nocn_form').style.display = 'none';
    } else {
        document.getElementById('nocn_form').style.display = 'block';
        document.getElementById('nocn_form').scrollIntoView();
    }
});
document.getElementById('nocn_form').addEventListener('submit', function(e){
    e.preventDefault();
    submitForm("");
});
function submitForm(ext){
    const learner = document.getElementById('learnerName'+ext).value;
    const qual = document.getElementById('qualification'+ext).value;
    const unitNum = document.getElementById('unitNumber'+ext).value;
    const level = document.getElementById('level'+ext).value;
    const tutorAssess = document.getElementById('tutorAssessor'+ext).value;
    const date = document.getElementById('date'+ext).value;
    const feedbackAssess = document.getElementById('feedbackAssessor'+ext).value;
    const criteria = document.getElementById('assessmentCriteria'+ext).value;
    const tutorAssessSign = document.getElementById('tutorAssessorSignature'+ext).checked;
    const tutorAssessSignD = document.getElementById('tutorAssessorSignatureDate'+ext).value;
    const iqaSign = document.getElementById('iqaSignature'+ext).checked;
    const iqaSignD = document.getElementById('iqaSignatureDate'+ext).value;
    const formNum = document.getElementById('form_number'+ext).value;
    const params = `learner=${learner}&qual=${qual}&unitNum=${unitNum}&level=${level}&tutorAssess=${tutorAssess}&date=${date}&feedbackAssess=${feedbackAssess}&criteria=${criteria}&tutorAssessSign=${tutorAssessSign}&tutorAssessSignD=${tutorAssessSignD}&iqaSign=${iqaSign}&iqaSignD=${iqaSignD}&formNum=${formNum}`;
    const xhr = new XMLHttpRequest();
    xhr.open('POST','./classes/inc/nocn_coach.inc.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function(){
        if(this.status == 200){
            let text = JSON.parse(this.responseText);
            const nocnErrorText = document.getElementById('nocn_error_text'+ext);
            nocnErrorText.style.display = 'none';
            if(text['error'] == false){
                location.reload();
            } else if(text['error'] == true){
                const array = [
                    ['learner','Learner Name','learnerName'+ext],
                    ['qual','Qualification','qualification'+ext],
                    ['unitNum','Unit Number','unitNumber'+ext],
                    ['level','Level','level'+ext],
                    ['tutorAssess','Tutor/Assessor','tutorAssessor'+ext],
                    ['date','Date','date'+ext],
                    ['feedbackAssess','Feedback from Assessor to Learner','feedbackAssessor'+ext],
                    ['criteria','Criteria','assessmentCriteria'+ext],
                    ['tutorAssessSign','Tutor/Assessor Signature','tutorAssessorSignature'+ext],
                    ['tutorAssessSignD','Tutor/Assessor Signature Date','tutorAssessorSignatureDate'+ext],
                    ['iqaSign','IQA Signature','iqaSignature'+ext],
                    ['iqaSignD','IQA Signature Date','iqaSignatureDate'+ext],
                    ['formNum','Form Number','form_number'+ext]
                ];
                let errorText = 'Invalid:\n';
                array.forEach(function(item){
                    if(text[item[0]]){
                        errorText += item[1]+':'+text[item[0]]+"\n";
                        document.getElementById(item[2]).style = 'border: 2px solid red';
                    } else {
                        document.getElementById(item[2]).style = '';
                    }
                });
                if(text['errorType']){
                    errorText += text['errorType'] + '\n';
                }
                nocnErrorText.innerText = errorText;
                nocnErrorText.style.display = 'block';
            }
        }
    }
    xhr.send(params);
}
function clickedSign(date){
    if(date != ''){
        const tempDate = new Date(Date.now());
        if(date.includes('_exists')){
            if(document.getElementById(date.replace('Date','')).checked){
                document.getElementById(date).value = tempDate.getFullYear()+"-"+("0"+(tempDate.getMonth()+1)).slice(-2)+"-"+("0"+tempDate.getDate()).slice(-2);
            } else {
                document.getElementById(date).value = '';
                resetMinMax(date);
            }
        } else {
            if(document.getElementById(date).checked){
                document.getElementById(date+"Date").value = tempDate.getFullYear()+"-"+("0"+(tempDate.getMonth()+1)).slice(-2)+"-"+("0"+tempDate.getDate()).slice(-2);
            } else {
                document.getElementById(date+"Date").value = "";
            }
        }
    }
}
function resetMinMax(id){
    let tempDate = new Date(Date.now());
    document.getElementById(id).setAttribute('min', tempDate.getFullYear()+"-"+("0"+(tempDate.getMonth()+1)).slice(-2)+"-"+("0"+tempDate.getDate()).slice(-2));
    document.getElementById(id).setAttribute('max', tempDate.getFullYear()+"-"+("0"+(tempDate.getMonth()+1)).slice(-2)+"-"+("0"+tempDate.getDate()).slice(-2));
}