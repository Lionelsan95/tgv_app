$(document).ready(function(){
    $("#action").on('change', function(){
        $("#alert").remove();
        const   oldSolde = parseFloat($("#old_solde").val()),
                action = parseFloat($(this).children("option:selected").attr('montant')),
                val = parseInt($(this).children("option:selected").attr('value'));
        let     solde =  oldSolde + action;

        if(solde < 0){
            $("#editUserButton").attr('disabled','disabled')
            $(this).parent('div')
                .append('<div id="alert" class="sufee-alert alert with-close alert-danger alert-dismissible fade show"><span class="badge badge-pill badge-danger">Alert</span>Le solde sera négatif!<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }else{
            $("#editUserButton").removeAttr('disabled');
        }

        if(val < 0 || solde < 0){
            solde = oldSolde;
        }

        $("#solde").val(solde);

    });
    $("#editUserButton").on('click', function(e){
        e.preventDefault();
        $("#editUser").submit();
    })

    function checkpass(password)
    {
        return password.trim().length > 5 ? true:false;
    }

    function ValidateEmail(inputText)
    {
        var mailformat = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
        if(inputText.match(mailformat)) {
            return true;
        }
        else {
            return false;
        }
    }

    function verify(idButton, idPassword, formId, idPassword2)
    {
        const passwordNotSecure = 'Your password should have at leat 6 characters!',
            passwordDifferent = 'Your password are different!',
            invalidEmail = 'Your address mail is not valid';
        $("#"+idButton).on('click', function(e){
                e.preventDefault();
                if(checkpass($("#"+idPassword).val()))
                {
                    if(idPassword2 === null)
                        $('form[name="' + name_form + '"]').submit();
                    else if($("#"+idPassword).val() == $("#"+idPassword2).val()) {
                        $("#"+formId).submit();
                    }
                    else
                        $("#"+idPassword2).parent('div')
                            .append('<div id="pwd_alert2" class="sufee-alert alert with-close alert-danger alert-dismissible fade show"><span class="badge badge-pill badge-danger">Alert</span>'+passwordDifferent+'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
                }
                else
                {
                    $("#"+idPassword).parent('div')
                        .append('<div id="pwd_alert" class="sufee-alert alert with-close alert-danger alert-dismissible fade show"><span class="badge badge-pill badge-danger">Alert</span>'+passwordNotSecure+'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
                }
            });
    }
    verify("create", "user_password_first", "subscribe", "user_password_second");


    $("#createUser").on('click', function(e){
        e.preventDefault();
        const firstName = $("#firstName").val(),
            lastName = $("#lastName").val(),
            email = $("#email").val(),
            orga = $("#organisation").val();

        if(ValidateEmail(email)){

            if(lastName.trim() !== '' && firstName.trim() !== '' && orga.trim() !== '')
            {

            }else{
                console.log('Please fill all that field');
            }
        }else{
            $("#email").parent('div')
                .append('<div id="pwd_alert2" class="sufee-alert alert with-close alert-danger alert-dismissible fade show"><span class="badge badge-pill badge-danger">Alert</span>'+passwordDifferent+'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }

    });
});