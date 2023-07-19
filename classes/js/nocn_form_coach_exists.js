function edit_form(id, number){
    document.getElementById('nocn_form_menu_error').style.display = 'none';
    document.getElementById('nocn_error_text_exists').style.display = 'none';
    const params = `id=${id}`;
    const xhr = new XMLHttpRequest();
    xhr.open('POST','./classes/inc/nocn_coach_form.inc.php',true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function(){
        if(this.status == 200){
            let text = JSON.parse(this.responseText);
            const errorTextE = document.getElementById('nocn_form_menu_error');
            errorTextE.style.display = 'none'
            if(text['error'] == true){
                errorTextE.innerText = 'Error Loading NOCN Form';
                errorTextE.style.display = 'block';
            } else if(text['error'] == false){
                errorTextE.style.display = 'none';
                if(text['array']){
                    text = text['array'];
                    const formArray = [
                        ['learnername','learnerName_exists'],
                        ['qualification','qualification_exists'],
                        ['unitnumber','unitNumber_exists'],
                        ['level','level_exists'],
                        ['assessorname','tutorAssessor_exists'],
                        ['date','date_exists'],
                        ['feedbackassessor','feedbackAssessor_exists'],
                        ['commentlearner','learnerComments_exists'],
                        ['learnersignaturedate','learnerSignatureDate_exists'],
                        ['assessorsignaturedate','tutorAssessorSignatureDate_exists'],
                        ['iqasignaturedate','iqaSignatureDate_exists']
                    ];
                    formArray.forEach(function(item){
                        document.getElementById(item[1]).value = (text[item[0]]) ? text[item[0]] : "";
                        if(document.getElementById(item[1]).hasAttribute('min') && item[0] != 'learnersignaturedate'){
                            if(text[item[0]].length > 0){
                                document.getElementById(item[1]).setAttribute('min', '');
                                document.getElementById(item[1]).setAttribute('max', '');
                            } else {
                                resetMinMax(item[1]);
                            }
                        }
                        document.getElementById(item[1]).style = '';
                    });
                    if(text['metcriteria']){
                        if(text['metcriteria'] == 0){
                            document.getElementById('assessmentCriteria_exists').value = 'no';
                        } else if(text['metcriteria'] == 1){
                            document.getElementById('assessmentCriteria_exists').value = 'yes';
                        }
                        document.getElementById('assessmentCriteria_exists').style = '';
                    }
                    const checkboxArray = [
                        ['learnersignature', 'learnerSignature_exists'],
                        ['assessorsignature', 'tutorAssessorSignature_exists'],
                        ['iqasignature', 'iqaSignature_exists']
                    ];
                    checkboxArray.forEach(function(item){
                        document.getElementById(item[1]).checked = (text[item[0]]) ? true : false;
                        document.getElementById(item[1]).style = '';
                    });
                    document.getElementById('form_number_exists').value = number;
                    document.getElementById('nocn_form_exists').style.display = 'block';
                    document.getElementById('nocn_form_title_exists').innerText = text['title'] + " (Edit)";
                }
            }
        }
    }
    xhr.send(params);
}
document.getElementById('nocn_form_exists').addEventListener('submit', function(e){
    e.preventDefault();
    submitForm('_exists');
});