// Debugger
var DEBUG = function() {
    var DEBUG_ON = true;

    if (DEBUG_ON) {
        console.trace();
        if (DEBUG.arguments.length > 0) {
            console.log(DEBUG.arguments);
        }
    }
};

var CONFIG={
    email:"#newsletter_email",
    formulario:"#form",
    tempo:5000,
    error_class:"error",
    change_color:"change_color"
};

function formSubmit(eventSubmit){
    DEBUG('In: formSubmit()', eventSubmit);
    var email=$(CONFIG.email), //id -> #newsletter_email
        paragrafo=$("<p>").addClass(CONFIG.change_color);
    // Prevent to send the form
    eventSubmit.preventDefault();

    // 3) Submit the form
    if (validaForm()) {
        DEBUG('In: formSubmit(). Condition: validaForm() == true');
        //eventSubmit.target.submit();
        $("." + CONFIG.change_color).remove();
        $.ajax({ 
              type: eventSubmit.target.method, //method, action -> look in index.html
              url: eventSubmit.target.action, 
              data: $(eventSubmit.target).serialize() 
              }) 
              .done(function(resposta) {
                DEBUG( "Server Response: ", resposta );
                paragrafo.text(resposta);
                email.before(paragrafo);    
        }); 
    }

    // 4) Receive the message and show to the user
    else {
        email.addClass(CONFIG.error_class);
        window.setTimeout(function() {
           email.removeClass(CONFIG.error_class);
        }, CONFIG.tempo);
    }
}

// 2) Form validation
function validaForm() {
    DEBUG('In: validaForm()');

    var email=$(CONFIG.email);
    return ($.trim(email.val()).length != 0);
}

// jQuery init
$(document).ready(function(){
    DEBUG('In: $(document).ready()');

    // 1) Intercept a form submit
    var formulario = $(CONFIG.formulario);
    formulario.submit(formSubmit);
});